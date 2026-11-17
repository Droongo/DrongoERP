# Module Interconnects — Scenarios & Guidelines

This document explains how Drongo modules (e.g. **Sales**, **Inventory**, **Reporting**)
work together **without** team members needing to access or modify another module's code.

## Core principle

> A team member should only ever touch files inside **their own module** (`modules/<name>/`).
> Cross-module communication happens through **declared dependencies**, **public service
> interfaces**, and **domain events** — never by reaching into another module's `src/Internal\`.

---

## The three supported patterns

| Pattern | When to use | Example | Coupling |
|---------|-------------|---------|----------|
| **Public service call** | Module A needs Module B to do something synchronously | Sales reserves stock in Inventory | Declared `dependsOn` + interface |
| **Domain event (pub/sub)** | Module A needs to tell others "something happened" without knowing who listens | Sales places order → Reporting + Inventory react | Event bus via `Kernel` |
| **Read-only query via Kernel** | Module A needs data owned by Module B | Reporting reads sales totals | `Kernel::getService()` + declared dep |

---

## Scenario 1: Sales places an order → reserve stock in Inventory

**Who touches what:**
- **Sales developer** works only in `modules/sales/`.
- **Inventory developer** works only in `modules/inventory/`.

**Flow:**
1. Sales calls `InventoryServiceInterface::reserveStock()` (interface lives in `packages/core/Contracts/`).
2. The concrete implementation (`Drongo\Module\Inventory\StockService`) is wired by the **app layer** (`apps/api/src/bootstrap.php`), not by Sales.
3. Sales publishes `OrderPlaced` event via `Kernel::emit()`.
4. Sales **never** imports `Drongo\Module\Inventory\*` classes.
5. Inventory **never** imports `Drongo\Module\Sales\*` classes.

**composer.json (`modules/sales`):**
```json
"extra": {
  "drongo": {
    "module": {
      "name": "sales",
      "dependsOn": ["inventory"],
      ...
    }
  }
}
```

**Rule enforced by CI:** `tools/validate-module-boundaries.php` fails the PR if Sales references
`Drongo\Module\Inventory\` without `inventory` in `dependsOn`, or if it reaches into
`Drongo\Module\Inventory\Internal\`.

---

## Scenario 2: Stock changes → Reporting updates its read model

**Who touches what:**
- **Inventory developer** works only in `modules/inventory/`.
- **Reporting developer** works only in `modules/reporting/`.

**Flow:**
1. Inventory emits `StockAdjusted` event via `Kernel::emit()`.
2. Reporting subscribes in `ReportingModule::boot()`:
   ```php
   $kernel->on(StockAdjusted::class, [new InventoryEventListener(), 'handle']);
   ```
3. Reporting writes to **its own** tables (`rpt_*`). It never writes to `inv_*`.
4. Reporting declares `dependsOn: ["sales", "inventory"]` because it imports their event
   classes — but it only **listens**, never calls their services directly.

---

## Scenario 3: Reporting needs sales + inventory data

**Flow:**
1. Reporting subscribes to both `OrderPlaced` (Sales) and `StockAdjusted` (Inventory).
2. It builds a denormalized read model in `rpt_daily_sales`, `rpt_stock_movements`, etc.
3. If a report needs live data it doesn't have, Reporting requests it through a public
   service exposed via `Kernel::getService()` — never via direct SQL across module schemas.

---

## Golden rules for team members

### 1. Only edit your own module
A PR for Sales must **not** contain changes under `modules/inventory/` or
`modules/reporting/`. If Inventory needs a change, that is a **separate PR** owned by the
Inventory team. `CODEOWNERS` auto-assigns the right reviewers.

### 2. Declare every dependency
If your module uses anything from another module, add it to
`extra.drongo.module.dependsOn` in your `composer.json`. CI enforces this.

### 3. Depend on interfaces, not concretions
Put shared contracts in `packages/core/Contracts/`. Your module type-hints the interface;
the app layer injects the real implementation.

### 4. Use events for cross-module notifications
Prefer `Kernel::emit()` / `Kernel::on()` over direct service calls when the receiver
should not know about the sender. This keeps the graph acyclic.

### 5. Never touch another module's `Internal\`
`src/Internal\` is private by convention. CI fails if any module references another
module's `Internal\` namespace.

### 6. Each module owns its database schema
Use the `dbSchema` prefix (e.g. `sal_`, `inv_`, `rpt_`). Do not write SQL that joins
tables owned by another module. Cross-module reads go through public services/events.

---

## Quick decision tree

```
Do you need another module to DO something?
├─ Yes, synchronously  → Public service call + declare dependsOn
├─ Yes, to notify others → Emit a DomainEvent via Kernel
└─ No, just data         → Subscribe to their event OR query via Kernel::getService()

Are you changing another module's files?
├─ Yes → STOP. Open a PR in that module's folder instead.
└─ No  → Proceed.
```

---

## Example directory ownership

```
modules/sales/              @drongo-org/sales-module   (team A)
modules/inventory/          @drongo-org/inventory-module (team B)
modules/reporting/          @drongo-org/reporting-module (team C)
packages/core/              @drongo-org/platform-team     (shared contracts)
apps/api/                   @drongo-org/backend-leads     (wiring only)
```

Team A (Sales) never needs access to `modules/inventory/` or `modules/reporting/`.
Team B (Inventory) never needs access to `modules/sales/`.
Team C (Reporting) only reads events; it writes to `rpt_*` tables it owns.
