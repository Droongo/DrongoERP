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

## Releases

- Modules are versioned independently in their `composer.json`.
- The root repo is tagged `drongo-erp/v<major>.<minor>.<patch>` for coordinated releases.
- Breaking changes to `packages/core` require a major bump and a platform-team RFC.