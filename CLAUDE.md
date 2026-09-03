# Claude Code Instructions

Read `PRD.md` before making architectural changes. `docs/architecture.md`
describes what already exists; `docs/decisions/` records why.

## Priorities

1. Preserve domain rules.
2. Keep Laravel conventions.
3. Avoid premature abstractions.
4. Add/adjust tests for behavioral changes.
5. Do not implement out-of-scope integrations unless explicitly requested.
6. PostgreSQL is the primary database.
7. UI is pt-BR; code is English.
8. Appointment is the central domain entity.
9. The local database is the canonical source of calendar data.
10. Never make Mundy a hard dependency.

## Before starting a milestone

- inspect current repository state;
- propose a short implementation plan;
- identify migrations or breaking changes;
- implement in small coherent steps;
- run formatter and tests;
- report changed files and remaining work.

## Project specifics

- **Current state:** Milestone 0 (bootstrap) is done. Milestone 1 (domain
  models: `Client`, `ServiceCategory`, `Service`, `Appointment`) has not
  started.
- **Timezone:** persist in UTC, display in `America/Sao_Paulo`. Convert at the
  edges through `App\Support\DisplayTimezone`. Never persist the weekday
  (RF-021).
- **Naming:** the business term "Atendido" maps to the `Client` model. Keep
  interface strings in `lang/pt_BR.json` / `lang/pt_BR/`, never hardcoded in
  Blade.
- **Directories:** create a directory only when a real class justifies it. The
  tree in the PRD is a target, not a scaffold to pre-generate.
- **Auth:** no public registration and no account self-deletion — see
  `docs/decisions/0002-authentication-scope.md`. Deactivate with
  `users.active`.
- **Tests run against PostgreSQL** (`agenda_contabil_test`), not SQLite.

## Commands

```sh
php artisan test        # PHPUnit
vendor/bin/pint         # format
vendor/bin/pint --test  # check formatting (what CI runs)
npm run dev             # Vite dev server
composer dev            # server + queue + logs + vite
```

## Do not

- Commit on the user's behalf.
- Add Filament, Redis, Sanctum, FullCalendar or a permissions package before
  the milestone that needs it.
- Create `calendar_connections` / `external_event_links` before a real
  provider is implemented.
- Store the weekday, or any calendar provider's data, as a source of truth.
