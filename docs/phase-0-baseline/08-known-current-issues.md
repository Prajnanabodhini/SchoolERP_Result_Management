# Known current issues deliberately left unchanged

These are observed baseline characteristics, not fixes performed by Phase 0.

| Area | Observation | Risk / effect |
|---|---|---|
| Credential handling | Login directly compares submitted and stored password values; admin create/update assigns password directly. Change/reset paths hash. Dump inspection found 38 non-empty password-field values and no recognized adaptive hashes. | Credential exposure and inconsistent authentication behavior |
| Sensitive repository artifacts | Committed dumps contain user and ERP/student data | Repository access can expose sensitive records; remediation requires controlled security response |
| Active status | Login path does not check `is_active` | Inactive users may authenticate; inference requires live confirmation |
| Authorization | Administrator-intended routes have generic `auth` middleware; permissions primarily govern menus | Direct-route access may exceed intended roles |
| Alternate marks edit | Update path lacks primary path's ownership, lock, range validation, and transaction safeguards | Unauthorized or invalid mark changes may be possible |
| Public routes | Teacher bulk exam-details and test page are public; Laravel storage GET/PUT route also appears without middleware | Data disclosure or state-change exposure requires urgent review |
| State-changing GET | Two ERP student-sync endpoints use GET and require only authentication | CSRF/idempotency and accidental mutation risk |
| Subject IDs | Historical mapping IDs and canonical subject IDs coexist | Wrong joins/displays and migration complexity |
| Dashboard subject display | Allocation relation interprets canonical subject ID as a Standard Wise mapping ID | Incorrect subject label is possible |
| Result logic | Analytics uses a 35% interpretation while official generation uses subject rules and a D band beginning at 33% | Conflicting pass/fail reporting |
| Data ownership | Live MSSQL lookups coexist with local mirror/report paths | Staleness and inconsistent outputs |
| Session guard | Context guard logs out only if both year and section are missing | Partial context can proceed |
| Stale routes | `students/get-divisions` and `exam-master-subjects/{standard}` target absent controller methods | Runtime errors if invoked |
| Migrations | Two teacher-class-allocation creation migrations coexist | Fresh migration/restore drift risk |
| Import/migration history | `exam_master_subjects` exists with 63 rows, while its create migration is reported pending | Running migrations against the imported baseline could conflict; do not use migrations to reconstruct it |
| Audit coverage | Marks audit structures exist; user/auth and status transition coverage appears incomplete | Limited accountability |
| Environment boundary | Remote testing deployment is a separate disconnected system; prior local IIS probing was out of scope | Remote parity must be collected on that system, not inferred locally |

No issue above was exploited, normalized, refactored, or repaired during Phase 0.
