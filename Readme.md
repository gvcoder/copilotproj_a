## Simple PHP Blog (Copilot Demo)

This repository is a small PHP-based blog application with an admin panel, built with SQLite and minimal dependencies. It was scaffolded using GitHub Copilot and includes basic security and administration features for demonstration and learning purposes.

## Key Features
- Public blog listing and individual post pages
- Admin panel to create, edit, delete posts
- SQLite database with initialization script
- Basic security: prepared statements, CSRF tokens, XSS escaping, security headers
- Flash messages and simple logging to `storage/logs/`

## File Structure (important files)
- `public/` — Web root
	- `index.php` — Blog home page
	- `post.php` — View single post
	- `admin/` — Admin panel (login, logout, security-check)
	- `admin/posts/` — Create/Edit/Delete/List post pages
- `scripts/init_db.php` — Create SQLite DB and seed sample posts
- `src/` — Application helpers
	- `auth.php`, `db.php`, `flash.php`, `headers.php`, `security.php`
- `storage/logs/` — Security and event logs

## Prerequisites
- PHP 7.4 or newer
- SQLite3 and the PHP PDO SQLite extension (`pdo_sqlite`)
- Composer is not required

On Debian/Ubuntu you can install required packages with:

```bash
sudo apt update
sudo apt install -y php php-sqlite3 php-xml php-mbstring
```

## Setup (new environment)
1. Clone the repository and change into the project directory.
2. Ensure PHP and the `pdo_sqlite` extension are installed (see prerequisites).
3. Initialize the database:

```bash
php scripts/init_db.php
```

This creates the SQLite database file and inserts sample posts.

## Run Locally (development)
Start the built-in PHP web server from the project root, serving the `public/` directory:

```bash
php -S localhost:8000 -t public
```

Open http://localhost:8000 in your browser.

Admin interface: http://localhost:8000/admin/login.php

Default demo credentials (change these in production):

- Username: `admin`
- Password: `password123`

## Optional: Docker (quick run)
Create a simple `Dockerfile` using the official PHP Apache image, or run:

```bash
docker run --rm -p 8000:80 -v "$PWD/public":/var/www/html -v "$PWD/storage":/var/www/storage php:8.1-apache
```

Adjust volumes and permissions as needed.

## Security Notes / Production Recommendations
- Do not run the built-in PHP server in production — use Nginx/Apache with PHP-FPM.
- Protect the database file and the `storage/` directory with proper filesystem permissions.
- Use HTTPS (TLS) and set secure cookie flags for sessions.
- Replace demo admin credentials and consider an external authentication provider.
- Disable `display_errors` in `php.ini` and enable appropriate logging.

## Troubleshooting
- If pages fail with DB errors, confirm the DB file exists after running `php scripts/init_db.php` and that the web server user can read/write it.
- Check `storage/logs/` for security or runtime logs.

---

If you want, I can run `php scripts/init_db.php` now and/or start the local PHP server for you. Would you like me to do that?