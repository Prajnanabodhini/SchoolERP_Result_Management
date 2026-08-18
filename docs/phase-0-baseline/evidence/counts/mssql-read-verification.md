# MSSQL read verification

Captured: 2026-08-18T10:03:55+05:30  
Environment: local Phase 0 workstation  
Database: `Shirgaon SchoolERP`  
Authentication: Windows Integrated Authentication  
Application connection: `sqlsrv_olderp`

Two SELECT-only methods succeeded outside the restricted execution sandbox:

1. `sqlcmd` with trusted Windows authentication.
2. Laravel bootstrap using the configured `sqlsrv_olderp` connection.

| Observation | Value |
|---|---:|
| SQL Server product version | 17.0.1125.2 |
| Edition | Enterprise Developer Edition (64-bit) |
| `SubStudentMst` rows | 8,134 |
| `FeeMstStudent` rows | 1,871 |
| `StandardMst` rows | 163 |
| `DivisionMst` rows | 238 |

The initial restricted-sandbox attempt failed because Windows credentials were unavailable to that process. That failure is superseded by the successful trusted-context reads above. No row content, hostname, credential, connection string, or PII is recorded. No MSSQL write was attempted.
