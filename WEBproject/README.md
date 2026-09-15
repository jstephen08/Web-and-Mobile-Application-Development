# NSBM EventHub — Backend Setup (PHP + MySQL / XAMPP)

## 1. Place the project
Extract/copy this whole `WEBproject` folder into:
```
C:\xampp\htdocs\WEBproject
```
So the path `C:\xampp\htdocs\WEBproject\backend\api\events.php` exists.

## 2. Start services
Open the XAMPP Control Panel and start **Apache** and **MySQL**.

## 3. Create the database
1. Go to `http://localhost/phpmyadmin`
2. Click the **SQL** tab (or use "Import")
3. Open `backend/schema.sql` from this folder, paste its contents in, and click **Go**.

This creates the `nsbm_eventhub` database with `users`, `events`, `announcements`, and `registrations` tables, plus some sample events/announcements.

## 4. Fix the seed passwords (one-time step)
The passwords in `schema.sql` are placeholder hashes. To generate real working ones:
1. Visit `http://localhost/WEBproject/backend/hash_seed.php` in your browser.
2. It prints `UPDATE ...` SQL statements — copy them into phpMyAdmin's SQL tab and run them.
3. Delete `hash_seed.php` afterward (or just leave it — it's harmless, but tidy to remove).

Default logins after this step:
| Role    | Email                        | Password    |
|---------|------------------------------|-------------|
| Admin   | admin@nsbm.ac.lk             | admin123    |
| Student | kamal@student.nsbm.ac.lk     | student123  |
| Student | nimali@student.nsbm.ac.lk    | student123  |

## 5. Open the site
Go to:
```
http://localhost/WEBproject/webproject.html
```
**Not** `file:///...` — PHP only executes through Apache via `localhost`.

## What changed from the static demo
- `webproject.js` no longer stores events/announcements/registrations in JavaScript arrays — it fetches everything from `backend/api/*.php` over `fetch()`, using PHP sessions for login state.
- The old "Login As" dropdown and the `admin123` prompt-based role switch are gone. Role (student vs admin) now comes from the `users` table, checked with `password_verify()`.
- Students can self-register via the new "Create an account" link on the login modal (`register.php`). Admin accounts aren't self-serve — create them directly in the `users` table (or reuse the seeded one).

## API summary
| Endpoint                        | Method | Auth        | Purpose                              |
|----------------------------------|--------|-------------|---------------------------------------|
| `backend/api/register.php`      | POST   | —           | Create a student account, logs in    |
| `backend/api/login.php`         | POST   | —           | Log in, starts a session             |
| `backend/api/logout.php`        | POST   | —           | Destroys the session                 |
| `backend/api/session.php`       | GET    | —           | Who's currently logged in?           |
| `backend/api/events.php`        | GET    | —           | List events (filter by category/search) |
| `backend/api/events.php`        | POST   | Admin       | Create an event                      |
| `backend/api/events.php?id=`    | DELETE | Admin       | Delete an event                      |
| `backend/api/announcements.php` | GET    | —           | List announcements                   |
| `backend/api/announcements.php` | POST   | Admin       | Post an announcement                 |
| `backend/api/registrations.php` | GET    | Login req.  | Admin: full list. Student: own event ids |
| `backend/api/registrations.php` | POST   | Login req.  | Toggle register/unregister for an event |

## Troubleshooting
- **"Database connection failed"** → check `backend/includes/db.php` credentials match your MySQL setup (default XAMPP is user `root`, no password).
- **Blank page / 500 error** → check `C:\xampp\php\logs\php_error_log` or enable `display_errors` in `php.ini` temporarily.
- **Login always fails** → make sure you ran the `hash_seed.php` UPDATE statements (step 4) — the hashes shipped in `schema.sql` are placeholders.
