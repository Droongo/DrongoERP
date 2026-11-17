# Contributing to Drongo ERP

## Team & access

| Team | Repo permission | Typical members |
|------|----------------|-----------------|
| `repo-admins` | Admin | Platform leads |
| `backend-leads` | Write | Backend seniors |
| `frontend-leads` | Write | Frontend seniors |
| `platform-team` | Write | Core packages, CI, tooling |
| `<module>-module` | Triage/Write | Owners of that business module |
| `tech-writers` | Read | Documentation |

Ask a `repo-admins` member to add you to the relevant GitHub team.

## Creating a pull request

1. **Branch naming:** `<module>/<type>/<short-description>`
   - Example: `accounting/feat/invoice-validation`
   - Example: `core/fix/event-bus-null-coalesce`
2. **Keep PRs small.** One module per PR whenever possible.
3. **Code owners are automatic.** GitHub assigns reviewers based on `CODEOWNERS`. If you touch `/modules/accounting/**`, the accounting-module team is requested for review.
4. **Required checks before merge:**
   - `validate-boundaries` — passes
   - `test` — green for changed modules
   - `lint` — no CS violations
   - At least **2 approvals**, including one from the module's owner team if their path is touched.
   - All conversations resolved.

## Creating a new module

See `docs/ARCHITECTURE.md` section "Adding a new module", then run:

```bash
php tools/scaffold-module.php accounting   # (template script you can build)
```

## Inter-module coordination (Sales ↔ Inventory ↔ Reporting)

When your module needs to work with another module:

| Situation | What to do | File to touch |
|-----------|-----------|---------------|
| You need another module to perform an action | Call its public service via `Kernel::getService()` or constructor-injected interface | Your module's `src/` only |
| You need to tell others something happened | Emit a `DomainEvent` via `Kernel::emit()` | Your module's `src/` only |
| You need to react to another module | Subscribe to its event in `Module::boot()` | Your module's `src/` only |
| Another module needs to change | Open a **separate PR** in that module's folder | That module's folder (owned by their team) |

### Rules (enforced by CI + CODEOWNERS)

1. **Declare dependencies.** If your module references `Drongo\Module\<Other>\`, add `<Other>` to `extra.drongo.module.dependsOn` in your `composer.json`.
2. **No `Internal\` access.** Never reference another module's `Drongo\Module\<Other>\Internal\` namespace.
3. **Only edit your own module.** A PR for `modules/sales/` must not contain changes under `modules/inventory/` or any other module.
4. **Depend on interfaces, not concretions.** Put shared contracts in `packages/core/Contracts/`.
5. **Own your schema.** Use your `dbSchema` table prefix. Never join another module's tables directly.

### Pull request requirements for interconnected modules

If your PR touches a module that other modules depend on (e.g. you change `modules/inventory/`):

- The dependent module's team is auto-requested as reviewer via `CODEOWNERS`.
- CI runs `validate-module-boundaries` — cycles and undeclared dependencies block the merge.
- If you change Inventory's public interface (`InventoryServiceInterface` in `packages/core`), the Platform team must approve, and all consumers (Sales, etc.) must be verified compatible.

## Releases

- Modules are versioned independently in their `composer.json`.
- The root repo is tagged `drongo-erp/v<major>.<minor>.<patch>` for coordinated releases.
- Breaking changes to `packages/core` require a major bump and a platform-team RFC.
