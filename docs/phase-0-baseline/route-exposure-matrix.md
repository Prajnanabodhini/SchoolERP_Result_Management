# Route exposure matrix

**Current-main reassessment:** Re-generated in memory from `php artisan route:list --json` on 2026-08-18 at SHA `1780866c61c0b7cb0e7a9652735ccdc312d671ad`. The 171 normalized route rows exactly match the saved TSV evidence after excluding its metadata header: 159 include `auth`, and 12 do not. No route was invoked.

Generated from `php artisan route:list --json` at repository SHA `1780866c61c0b7cb0e7a9652735ccdc312d671ad`. Full evidence: `evidence/route-list/route-list.tsv` (171 routes: 159 authenticated, 12 without `auth`). Classification reflects middleware and HTTP method, not intended menu visibility.

| Route / group | Method | Middleware | Classification | Side-effect handling |
|---|---|---|---|---|
| `/`, login, forgot/reset password, `/up` | GET/POST | Public/web as listed | Expected public/authentication/health | Login/reset POSTs were not invoked |
| `/teacher-bulk-allocation/exam-details` | GET | `web` | Public data endpoint | Read-only route not invoked |
| `/test-page` | GET | `web` | Public development/test page | Not invoked |
| `storage/{path}` | GET, PUT | none shown | Public file read/write exposure candidate | PUT was not invoked |
| `/erp-student-sync/{year}` | GET | `web,auth` | Authenticated state-changing GET | Not invoked |
| `/erp-sync/students/{year}` | GET | `web,auth` | Authenticated state-changing GET | Not invoked |
| `/marks-entry/edit` | GET | `web,auth` | Authenticated alternate edit | Read not invoked; unsafe update path noted |
| `/marks-entry/update` | POST | `web,auth` | Authenticated state-changing alternate edit | Not invoked |
| Administrator prefix (12 routes) | mixed | `web,auth` | Admin-intended, but no route role middleware | Not invoked |
| `students/get-divisions` | GET | authenticated in route inventory | Stale candidate: controller method absent | Not invoked |
| `exam-master-subjects/{standard}` | GET | authenticated in route inventory | Stale candidate: controller method absent | Not invoked |
| Public registration | — | — | Absent | Register view/controller artifacts do not expose a route |

## Interpretation

- `PermissionHelper` and role permissions influence menu/UI visibility, but do not replace server-side route middleware.
- “Admin-intended” is an inference from prefix/controller/menu configuration. The observed middleware contract is only `web,auth`.
- GET ERP sync endpoints are classified as state-changing by controller behavior despite their HTTP verb.
- The framework-generated storage PUT route is a high-priority exposure candidate because no middleware appears in the generated route metadata.

No state-changing route was called for this inventory.
