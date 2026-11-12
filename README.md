# FPN Graduating Students Portal

**Design and Implementation of a Web-Based Portal for Graduating Students of the Computer Science Department**

Federal Polytechnic Nasarawa &middot; School of Information Technology &middot; Department of Computer Science

Built with **PHP (PDO) + MySQL** and a hand-crafted, responsive UI. No frameworks required.

---

## 1. What the system does

A complete graduation-management portal with three roles:

| Role | Capabilities |
|------|--------------|
| **Student** | Register, complete profile, upload passport, run online **clearance** (per unit), submit **final year project**, view **results/CGPA**, complete **alumni** form + **exit survey**, read announcements, print a **clearance summary certificate**. |
| **Staff** (project coordinator / reviewer) | Review clearance queue, review & grade projects, change password. |
| **Admin** (HOD) | Dashboard analytics, manage students (suspend/activate/reset password), review clearance, review/grade projects & assign supervisors, upload results, post announcements, view alumni & survey insights, **reports + CSV export of graduands**, manage staff accounts, configure clearance units & portal settings, view activity log. |

### Programmes (from current FPN research)
- **ND** — Computer Science
- **HND** — Software & Web Development
- **HND** — Networking & Cloud Computing

---

## 2. Quick start (XAMPP / Windows) — ONE CLICK, NO MANUAL IMPORT

1. **Install XAMPP** (PHP 8.0+ and MySQL/MariaDB).
2. Copy the whole `fpn-portal` folder into `C:\xampp\htdocs\`.
3. Start **Apache** and **MySQL** from the XAMPP Control Panel.
4. Open **`http://localhost/fpn-portal/install.php`** in your browser.
   That single page does **everything automatically**:
   - creates the `fpn_portal` database, all tables and seed data (no phpMyAdmin import needed),
   - creates the default Admin & Staff accounts with securely hashed passwords.
5. Click **Go to login**, sign in, then **delete `install.php`**.

> You do **not** need to import `database/fpn_portal.sql` manually — the installer runs it for you.
> (The `.sql` file is still included in `database/` in case you ever want to import it by hand.)
>
> If MySQL isn't on the default XAMPP settings, edit `config/config.php` (`DB_USER`, `DB_PASS`) before running the installer.

### Default logins (created by install.php)
| Role | Login ID | Password |
|------|----------|----------|
| Admin (HOD) | `ADMIN001` | `Admin@12345` |
| Staff | `STAFF001` | `Staff@12345` |

Students create their own accounts from the **Register** page.

> If your MySQL uses a password, edit `config/config.php` (`DB_USER`, `DB_PASS`).

---

## 3. Folder structure

```
fpn-portal/
├── index.php               # Public landing page
├── login.php / logout.php / register.php
├── install.php             # One-time account setup (delete after use)
├── config/                 # config.php (constants) + database.php (PDO)
├── includes/               # functions, auth, header, footer, sidebars
├── assets/css|js|img/       # style.css, main.js, logo.jpg
├── database/fpn_portal.sql # Full schema + seed data
├── uploads/                # passports, projects, clearance (writable)
├── student/                # student module pages
├── staff/                  # staff module pages
└── admin/                  # admin module pages
```

---

## 4. Security features

- **PDO prepared statements** everywhere (SQL-injection safe).
- **Password hashing** with `password_hash()` / `password_verify()` (bcrypt).
- **CSRF tokens** on every POST form.
- **Role-based access control** (`require_role()`), session hardening.
- **Output escaping** via `e()` (XSS safe).
- **Upload validation** (extension + size) and `.htaccess` blocking PHP execution in `uploads/`.
- Forced password change for admin/staff temporary passwords.

See `DOCUMENTATION.md` for the full technical write-up (suited for the project report).
