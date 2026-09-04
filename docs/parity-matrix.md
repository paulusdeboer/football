# Functional parity matrix

| Area | Target behavior | Verification |
| --- | --- | --- |
| Login | Login with `name` and password; redirect to dashboard | PHPUnit feature test + browser smoke test |
| Player list | Sortable list, session-persisted filter and optional soft-deleted players | Feature test |
| Player CRUD | Multi-create, edit, delete and restore with current rating/position rules | Feature tests |
| Game creation | 10–12 players, date, snapshots and deterministic team assignment | Unit + feature tests |
| Game result | Store both scores, recalculate ratings and optionally send requests | Feature + mail tests |
| Rating flow | Public form, one submission per player/game and confirmation page | Feature + browser test |
| Rating overview | Historical games, rating players and values grouped as today | Feature test |
| Dashboard | Existing placeholder chart data | Inertia response test |
| Styling | Bootstrap/Sass layout and current navigation/table/form appearance | Browser smoke test |
| Data | Existing tables, IDs, relations and historical records retained | Snapshot parity command |

Known inconsistencies are characterized first. No behavior is silently changed during the parity release.
