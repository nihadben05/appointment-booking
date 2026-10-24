# Appointment Booking System

A simple PHP and MySQL web app where clients book appointment slots and a manager approves or rejects them.

## Features

**Clients**
- Register and log in
- Book an available time slot
- View and cancel their appointments
- Edit their name and email

**Manager**
- Dashboard with appointment statistics
- Add time slots
- Approve or reject booking requests
- Browse the client list and client details

## Requirements

- PHP 7.4 or newer with the `mysqli` extension
- MySQL or MariaDB (InnoDB)
- Apache (XAMPP or WAMP works well)

## Setup

1. Copy the `appointment_booking` folder into your web root (for XAMPP: `htdocs`).
2. Import `database.sql` with phpMyAdmin, or run:
   ```
   mysql -u root -p < database.sql
   ```
3. Check the database settings in `includes/db.php` (host, user, password, database name).
4. Open `http://localhost/appointment_booking/`.

## Creating the manager account

Registration only creates clients. Generate a password hash:

```
php -r "echo password_hash('YourPassword', PASSWORD_DEFAULT);"
```

Then insert the manager in phpMyAdmin:

```sql
INSERT INTO appointment_db.users (role, name, email, password)
VALUES ('manager', 'Manager', 'manager@example.com', 'PASTE_HASH_HERE');
```

## Project structure

```
appointment_booking/
├── index.php
├── login.php
├── register.php
├── logout.php
├── database.sql
├── assets/style.css
├── includes/        shared code (database, auth, helpers, layout)
├── client/          client dashboard
└── manager/         manager dashboard, requests, clients
```

## Security

- Prepared statements for every query
- Output escaping and CSRF protection on all state-changing forms
- Role-based access for client and manager pages
- Passwords stored with `password_hash`

Change the default database credentials before deploying to a real server.
