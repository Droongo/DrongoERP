# Drongo ERP — Architecture

## 1. Monorepo layout

```
drongo-erp/
├── apps/             # Deployable applications (api, web, admin)
│   └── api/
├── modules/          # 50+ business modules (accounting, inventory, hr, …)
│   └── accounting/
│       ├── composer.json      # Module manifest + ownership metadata
│       ├── src/               # Public API + Internal\
│       ├── tests/
│       └── config/
├── packages/         # Shared libraries (core, db, auth, …)
│   └── core/
├── .github/          # CI workflows, CODEOWNERS, rulesets
├── tools/            # Boundary validators, module scaffolding
├── composer.json     # Root — uses composer/merge-plugin
└── CONTRIBUTING.md
```

## 2. Module contract

Every `modules/<name>` must contain:

| File | Purpose |
|------|---------|
| `composer.json` | Name `drongo/module-<name>`, PSR-4 autoload, `extra.drongo.module` metadata |
| `src/<ModuleName>Module.php` | `register(Kernel)` and `boot(Kernel)` — the public entry point |
| `src/Internal\` | **Private**. Other modules must never import this namespace. |
| `tests/` | PHPUnit tests |
| `config/routes.php` | Route definitions consumed by the host app |

### Metadata (`extra.drongo.module`)

```json
{
  "name": "accounting",
  "displayName": "Accounting",
  "ownerTeam": "@drongo-org/accounting-module",
  "dependsOn": [],
  "dbSchema": "acct_",
  "namespacePrefix": "Drongo\\Module\\Accounting"
}
```

- `dependsOn`: explicit list of other modules this module requires. Enforced in CI.
- `ownerTeam`: GitHub team used in `CODEOWNERS` — they are auto-assigned as reviewers.
- `dbSchema`: database table prefix, keeps module data isolated.

## 3. Communication rules (mandatory)

1. **No direct coupling.** Module A must never instantiate `Module\B\Internal\*` classes.
2. **Talk through the Kernel.** Publish/subscrib to events via `Kernel::emit()` / `Kernel::on()`, or fetch shared services via `Kernel::getService()`.
3. **Declare dependencies.** If module A needs module B, add B to `extra.drongo.module.dependsOn` in A's `composer.json`.
4. **No cycles.** The dependency graph must remain a DAG. CI runs `tools/validate-module-boundaries.php` on every PR.

## 4. Database strategy

- Each module owns tables prefixed with its `dbSchema` (e.g., `acct_ledger_entries`, `inv_stock_items`).
- Cross-module reads happen through the owning module's public service, never via direct SQL joins across schemas.
- Migrations live inside each module: `modules/<name>/migrations/`.

## 5. Adding a new module

1. Copy `modules/accounting/` to `modules/<new-name>/`.
2. Update `composer.json` name, namespace, `ownerTeam`, `dependsOn`.
3. Add the module path to the root `composer.json` `merge-plugin.include` list.
4. Register the module in `apps/api/src/bootstrap.php` (or your app's module registry).
5. Add `/modules/<new-name>/` to `.github/CODEOWNERS`.
6. Open a PR; the platform team reviews, then CI enforces boundaries forever after.