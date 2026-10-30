# PHP Login Session 2.0

A simple PHP and MySQL login system built on PHP sessions. It includes registration, login, logout, CSRF protection, and a dashboard for managing user accounts.

## Features

- Register, log in, and log out
- Passwords are hashed with `password_hash()`. Older MD5 passwords are upgraded the next time that user logs in.
- CSRF tokens on all forms
- Dashboard sessions expire after 30 minutes of inactivity
- Dashboard to add, edit, and delete user accounts

## Requirements

- PHP 8.0 or newer, with the `PDO` and `pdo_mysql` extensions
- MySQL 8.0 or newer. The included SQL dump uses the `utf8mb4_0900_ai_ci` collation, which MariaDB does not support.
- A web server such as Apache or Nginx
- An internet connection in the browser, because Bootstrap, jQuery, and Font Awesome load from CDNs

## Installation

1. Clone the repository into your web server's root folder, or into a subfolder of it:

   ```bash
   git clone https://github.com/DwightMckenzie/php_login-session-2.0.git
   ```

2. Create the database and import the schema:

   ```bash
   mysql -u root -p -e "CREATE DATABASE session_db CHARACTER SET utf8mb4;"
   mysql -u root -p session_db < session_db.sql
   ```

   The SQL dump includes sample user accounts. To start with an empty user table, delete the `INSERT INTO users` statement from `session_db.sql` before importing.

3. Create a database user with limited privileges:

   ```sql
   CREATE USER 'session_user'@'localhost' IDENTIFIED BY 'choose-a-strong-password';
   GRANT SELECT, INSERT, UPDATE, DELETE ON session_db.* TO 'session_user'@'localhost';
   ```

4. Create the configuration file at `/etc/php-secured-session/db.php`. This location is outside the web root, so the file is not publicly accessible.

   ```php
   <?php
   return [
       'host' => 'localhost',
       'name' => 'session_db',
       'user' => 'session_user',
       'pass' => 'choose-a-strong-password',
   ];
   ```

   Make sure the web server's user can read the file and other users cannot. For example, set the owner to the web server user and use `chmod 640`.

5. Open the app in your browser at `http://localhost/php_login-session-2.0/`. You will be redirected to the login page.

6. Click **Register** to create your first account.

## Usage

- **Register:** `register.php`. Usernames must be 3 to 64 characters: letters, numbers, dots, dashes, or underscores. Passwords must be 8 to 72 characters.
- **Log in:** `login.php`
- **Dashboard:** lists all accounts. Use the buttons to add, edit, or delete a user. You cannot delete the account you are logged in with.
- **Log out:** `logout.php`

## Project Structure

```
php_login-session-2.0/
├── assets/          CSS and JavaScript
├── classes/         Database connection (Database.php) and User class
├── helpers/         Session, authentication, CSRF, and escaping helpers
├── includes/        Shared header, navbar, and footer
├── dashboard.php    Account list with add, edit, and delete (login required)
├── index.php        Redirects to the login page
├── login.php
├── logout.php
├── register.php
└── session_db.sql   Database schema and sample data
```

## Security Notes

This project is a learning starter. Before using it on a public site:

- Serve the site over HTTPS. Session cookies are marked `secure` only when HTTPS is detected.
- Remove the sample data from `session_db.sql`, and never commit real passwords or password hashes.
- Consider adding rate limiting to the login form.
- Anyone who can reach the site can register an account. Restrict registration if that is not wanted.
