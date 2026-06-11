# MyEduConnect Database Notes

## Schema Summary
- Database: `myeduconnect`
- Main tables created in `db/init.sql`:
  - `users`
  - `grades`
  - `feedback`

## `users` Table Structure
- `id` INT AUTO_INCREMENT PRIMARY KEY
- `username` VARCHAR(50) NOT NULL UNIQUE
- `password` VARCHAR(255) NOT NULL
- `email` VARCHAR(100) NOT NULL
- `role` VARCHAR(20) NOT NULL
- `bio` TEXT DEFAULT NULL

## Password Storage
- Passwords are stored as plaintext in the seed data.
- Login in `src/index.php` compares `username` and `password` directly in SQL.
- No hashing is used in the current flow (no bcrypt, SHA1, or MD5 in the login path).

## Example Seeded Users
- `admin_mmu` / `SuperSecu...2026!`
- `student01` / `stud...pass`

## Security Note
- Password handling is insecure because credentials are stored in plaintext and checked with a raw SQL query.
- This exposes user passwords directly if the database is read or dumped, and it also makes the login query vulnerable to SQL injection.
