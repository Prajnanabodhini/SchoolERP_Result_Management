# Current local-to-MSSQL mapping

**Current-main reassessment (2026-08-18):** Source review at SHA `1780866c61c0b7cb0e7a9652735ccdc312d671ad` confirms this translation chain remains current. The existing SELECT-only Windows Integrated Authentication evidence remains valid; no MSSQL write was issued.

This documents the active translation chain observed in source. It is distinct from the frozen target design.

## Verified connection status — 2026-08-18

Windows Integrated Authentication to `Shirgaon SchoolERP` succeeded through both `sqlcmd` and Laravel's configured `sqlsrv_olderp` connection. SELECT-only reads verified `SubStudentMst` (8,134 rows), `FeeMstStudent` (1,871), `StandardMst` (163), and `DivisionMst` (238). This supersedes the previous connection-unavailable statement. It verifies database/table accessibility, not every local-to-MSSQL mapping combination.

## Academic context and student lookup

1. `SelectionController` queries MSSQL `SubStudentMst` through connection `sqlsrv_olderp` for distinct `yearid` and `sectionid`.
2. Section display names are produced by a hard-coded mapping.
3. Selected values are stored in session as `yearid`, `sectionid`, `year_name`, and `section_name`.
4. A local `standard_year_mappings` row translates the selected local `academic_year_id` and local `standard_id` to an MSSQL/old ERP standard ID.
5. A local division record supplies the division name. MSSQL `DivisionMst` is matched using year, translated standard ID, and upper-case trimmed division name.
6. MSSQL `SubStudentMst` is then queried using the resolved year/section/standard/division context and joined to MSSQL `FeeMstStudent` for active student information.

Primary evidence: `app/Http/Controllers/SelectionController.php`, `app/Helpers/StudentHelper.php`, models `SubStudentMst`, `StandardMst`, `DivisionMst`, and `FeeMstStudent`.

| Concept | Local identifier/source | MSSQL identifier/source | Current bridge |
|---|---|---|---|
| Academic year | `academic_years.id` in local administrative paths | `SubStudentMst.yearid` | `standard_year_mappings.academic_year_id`; selection also stores raw selected year in session |
| Section | local `sections` exists | `SubStudentMst.sectionid` | Current selection is MSSQL-derived; display name mapping is hard-coded |
| Standard | `standards.id` | old/ERP standard ID and `StandardMst` | `standard_year_mappings.old_standard_id` by academic year + local standard |
| Division | `divisions.id`/name | `DivisionMst.divisionid` | `division_year_mappings` exists; helper also resolves by year + old standard + normalized name |
| Student | local mirror tables exist | `SubStudentMst` plus `FeeMstStudent` | Principal helper reads live MSSQL after the context translation |

## Mixed current paths

- Result-sheet paths also perform direct MSSQL student lookup.
- Result-register/report-card code includes local mirrored student/fee joins.
- `erp_student_master` and two authenticated GET sync controllers remain capable of local replication.

Live MSSQL table access is verified, but this mapping sequence remains primarily a source-code contract because no specific anonymized end-to-end `ERP-MAP-001` context has yet been selected. No MSSQL or MariaDB write was performed.

## Frozen future target

MSSQL will be authoritative for academic and student masters using source IDs; MariaDB will hold Result Management data. Phase 0 did not implement that change.
