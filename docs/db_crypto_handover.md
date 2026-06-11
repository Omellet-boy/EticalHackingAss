# MyEduConnect DB and Crypto Hardening Handover (AbazarAdam)

## Summary
- Moved the active web app to `EticalHackingAss/src/` and kept Docker Compose mounting that folder as `/var/www/html`.
- Hardened password storage with bcrypt hashes in `db/init.sql` using `password_hash(..., PASSWORD_BCRYPT)`.
- Updated login in `src/index.php` to verify passwords with `password_verify()`.
- Switched the app database connection in `src/db_connect.php` to the dedicated `myedu_app` MySQL account.
- Removed runtime schema mutation from `src/dashboard.php` so the app works with least-privilege DB access.
- Cleaned database error handling so failures show controlled messages instead of raw stack traces.

## Database Changes
- `users.password` now stores bcrypt hashes only.
- Seeded demo users remain:
  - `student01` / `studentpass`
  - `admin_mmu` / `SuperSecureAdmin2026!`
- `myedu_app` is created in `db/init.sql` with `SELECT`, `INSERT`, `UPDATE`, `DELETE` on `myeduconnect.*` only.

## Verification Checklist
- [x] `docker compose up --build -d` starts the stack from `EticalHackingAss/EticalHackingAss`
- [x] `http://localhost` serves the MyEduConnect portal
- [x] Login works for `student01` / `studentpass`
- [x] Login works for `admin_mmu` / `SuperSecureAdmin2026!`
- [x] `users.password` contains bcrypt hashes starting with `$2y$`
- [x] The PHP app connects using `myedu_app`, not `root`

## Notes
- The legacy `version 1/` folder is retained for reference, but the active Dockerized application uses `src/`.
- No routes or URLs were changed.
