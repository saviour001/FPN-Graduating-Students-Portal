# FPN Graduating Students Portal — Complete User Guide

**Federal Polytechnic Nasarawa · School of Information Technology · Department of Computer Science**

*Learning, Technology & Service*

This single document explains three things:

1. **How to install and use the app** (for every kind of user)
2. **How the app was built** (architecture, technology, and design decisions)
3. **Likely supervisor / panel questions — with model answers**

---

## PART 1 — HOW TO USE THE APP

### 1.1 What the portal does

The portal is a web application that manages everything a final-year (graduating) Computer Science student must complete before graduation:

- Student registration and profile (bio-data, passport, programme).
- **Clearance** across departmental units (Department, SIT, Library, Bursary, Safety/Health, Hostel, Project, Alumni).
- **Final year project** submission, supervisor assignment, grading and feedback.
- **Results / CGPA** records per session and semester.
- **Announcements** from the department.
- **Alumni record** capture and **exit survey** feedback.
- Administrative **reports** and a graduand (cleared students) export.

The department offers:
- **ND** — Computer Science
- **HND** — Software & Web Development
- **HND** — Networking & Cloud Computing

### 1.2 Installing (one click)

1. Install **XAMPP** (PHP 8.0+ and MySQL/MariaDB).
2. Copy the whole `fpn-portal` folder into `C:\xampp\htdocs\`.
3. Start **Apache** and **MySQL** in the XAMPP Control Panel.
4. Open **`http://localhost/fpn-portal/install.php`**.
   It automatically creates the database, all tables, seed data, and the default Admin & Staff accounts. No manual phpMyAdmin import is needed.
5. Click **Go to login**, sign in, then **delete `install.php`** for security.

### 1.3 Default accounts

| Role | Login ID | Password |
|------|----------|----------|
| Admin (HOD) | `ADMIN001` | `Admin@12345` |
| Staff (Coordinator) | `STAFF001` | `Staff@12345` |

> Change these passwords immediately after first login (**Change Password** in the sidebar).
> Students create their own accounts through **Register**.

You can sign in with a **matric number, staff ID, or email** — the login box accepts any of them.

### 1.4 Student walkthrough

1. **Register** — go to the home page → *Register*, enter full name, email, matric number, programme, level, and a password.
2. **Complete your profile** — add phone, date of birth, gender, address, and upload a passport photograph.
3. **Clearance** — for each unit, upload the required document and submit. Track each unit's status (Pending / Approved / Rejected). A rejected item shows the officer's remark so you can re-submit.
4. **Final Project** — enter the title and abstract, upload the project document. Watch for supervisor feedback and status changes.
5. **Results / CGPA** — view the semester GPA records uploaded by the department and your computed CGPA.
6. **Alumni Form & Exit Survey** — fill these in as part of graduation.
7. **Clearance Certificate** — once every unit is approved, a printable certificate becomes available.
8. **Announcements** — read department notices.

### 1.5 Staff walkthrough (Project & Clearance Officers)

1. **Clearance Queue** — review student submissions, then Approve or Reject with a remark.
2. **Project Review** — read abstracts, download documents, and record feedback.
3. **Change Password** — update your credentials.

### 1.6 Admin walkthrough (HOD / Department)

1. **Dashboard** — key figures: total students, clearance progress, projects, and recent registrations.
2. **Students** — browse and search all registered students by programme/level.
3. **Clearance** — approve/reject any unit for any student, filter by status.
4. **Projects** — assign supervisors, set grades, write feedback, change status.
5. **Results** — add or delete semester GPA records; CGPA is recomputed automatically.
6. **Announcements** — publish or remove notices for students.
7. **Alumni & Surveys** — view captured alumni records and exit-survey responses.
8. **Reports** — summary statistics and a **CSV export of graduands** (fully cleared students).
9. **Staff Accounts** — create and manage staff logins.
10. **Settings** — portal name, current session, graduation year, and open/close clearance.
11. **Activity Log** — an audit trail of important actions.

### 1.7 Troubleshooting

| Symptom | Cause / Fix |
|--------|-------------|
| `install.php` shows a database error | MySQL not started, or `root` has a password. Start MySQL, or set `DB_PASS` in `config/config.php`. |
| "Not Found" on a page | A file is missing from `htdocs\fpn-portal`. Re-extract the full folder. |
| Can't log in | Use the exact default credentials, or run `install.php` once to (re)create accounts. |
| Uploads fail | Ensure the `uploads/` folder exists and is writable; check the file type/size (max 10 MB). |
| Session expires quickly | Sessions time out after 1 hour of inactivity by design; sign in again. |

---

## PART 2 — HOW THE APP WAS BUILT

### 2.1 Technology stack

- **Language:** PHP 8 (procedural, no framework — deliberately, so the code is easy to read and defend).
- **Database:** MySQL / MariaDB via **PDO** with prepared statements.
- **Front end:** Hand-written semantic HTML, a single modern CSS file (custom properties, responsive grid, collapsible sidebar), and a small vanilla-JS file (menu toggle, confirm dialogs, auto-hiding alerts).
- **Server:** Apache (bundled with XAMPP).
- **No external libraries or CDNs** — the whole app runs offline, which is ideal for a campus/lab defence.

### 2.2 Folder structure

```
fpn-portal/
├── config/        config.php (settings/constants), database.php (PDO connection)
├── includes/      functions.php, auth.php, header.php, footer.php, sidebars
├── assets/        css/style.css, js/main.js, img/logo.jpg
├── database/      fpn_portal.sql (schema + seed)
├── uploads/       passports/, projects/, clearance/ (protected)
├── student/       student-facing pages
├── staff/         staff review pages
├── admin/         admin management pages
├── index.php      public landing page
├── login.php  register.php  logout.php
├── install.php    one-click installer
└── README.md  DOCUMENTATION.md  USER_GUIDE.md
```

### 2.3 Architecture pattern

The app follows a lightweight **front-controller-per-page** pattern with shared includes:

- Every protected page requires `includes/auth.php`, calls `require_login()` / `require_role()`, sets a few layout variables, then includes `header.php` (which renders the sidebar and top bar) and `footer.php`.
- **Separation of concerns:** configuration (`config/`), reusable logic and helpers (`includes/`), presentation (`assets/` + header/footer), and per-feature controllers (`student/`, `staff/`, `admin/`).
- **Role-based access control (RBAC):** three roles — `student`, `staff`, `admin`. `require_role()` blocks unauthorized access and redirects users to their own dashboard.

### 2.4 Database design (11 tables)

`users`, `student_profiles`, `clearance_units`, `clearance_requests`, `projects`, `results`, `announcements`, `alumni_records`, `exit_surveys`, `activity_log`, `settings`.

- Normalised to 3NF: a `users` table holds identity/role; `student_profiles` holds student-specific bio-data (1-to-1 with users).
- `clearance_requests` links a student (`user_id`) to a `clearance_unit` with a status — a classic many-to-many resolved via a junction-style table.
- Foreign keys enforce referential integrity; seed data provides the 8 clearance units and default settings.

### 2.5 Security measures (important for the defence)

1. **Password hashing** with PHP `password_hash()` (bcrypt) and `password_verify()`; automatic re-hash when the cost changes. Plain passwords are never stored.
2. **SQL injection prevention** — every query uses **PDO prepared statements** with bound parameters (native prepares, emulation disabled).
3. **CSRF protection** — every form carries a CSRF token verified on submit (HTTP 419 on mismatch).
4. **Output escaping** — all dynamic output passes through `e()` (htmlspecialchars) to prevent XSS.
5. **Session hardening** — custom session name, HTTP-only cookies, idle timeout, and session-ID regeneration on login.
6. **File-upload validation** — extension and size checks, randomised stored filenames, and an `.htaccess` that blocks PHP execution inside `uploads/`.
7. **Access control** — role guards on every protected page.
8. **Activity logging** — an audit trail of sensitive actions.
9. **Least exposure** — `.htaccess` denies web access to `.sql`/`.md` files and disables directory listing.

### 2.6 Key helper functions

- `e()` — safe HTML output. `db()` — PDO singleton. `redirect()`, `set_flash()/get_flash()` — navigation and one-time messages.
- `csrf_field()/verify_csrf()` — CSRF. `input()/old()` — request handling. `handle_upload()` — validated uploads.
- `attempt_login()/require_role()` — authentication and authorization. `log_activity()`, `setting()`, `status_badge()`.

---

## PART 3 — LIKELY SUPERVISOR / PANEL QUESTIONS (WITH ANSWERS)

**Q1. What problem does this project solve?**
Manual graduation clearance is slow, paper-based, and error-prone. The portal digitises registration, clearance, project submission, results and reporting into one auditable system, reducing time and lost documents.

**Q2. Why PHP and MySQL rather than a framework?**
PHP + MySQL runs on the polytechnic's existing infrastructure (XAMPP) with zero licensing cost, and plain PHP keeps the code transparent and defensible. A framework would add abstraction that isn't necessary for the scope and would be harder to explain line-by-line.

**Q3. What software methodology did you use?**
An iterative/incremental model: requirements → design (ERD, UI) → implementation module-by-module → testing → deployment, refining each module before moving on.

**Q4. How is data kept secure?**
Bcrypt password hashing, PDO prepared statements against SQL injection, CSRF tokens on every form, output escaping against XSS, hardened sessions, validated uploads, and role-based access control. (See Part 2.5.)

**Q5. How do you prevent SQL injection?**
Every database call uses parameterised prepared statements; user input is bound as parameters, never concatenated into SQL.

**Q6. How are passwords stored?**
Never in plain text — they are hashed with `password_hash()` (bcrypt) and checked with `password_verify()`. The system also re-hashes automatically if the algorithm/cost is upgraded.

**Q7. What are the user roles and how do they differ?**
Student (register, clearance, project, results, forms), Staff (review clearance and projects), Admin/HOD (full management, reports, settings, staff accounts). Access is enforced by `require_role()`.

**Q8. Explain your database design / why is it normalised?**
Eleven related tables in 3NF. Identity is separated from student bio-data; clearance is a junction between students and units; foreign keys enforce integrity and avoid duplication and update anomalies.

**Q9. How does the clearance workflow work?**
A student uploads a document per unit → status is Pending → a staff/admin officer Approves or Rejects with a remark → when all units are Approved the student can print a clearance certificate.

**Q10. How is CGPA calculated?**
Admins enter each semester's GPA; the system averages the GPA records and stores the result as the student's CGPA on their profile, updating it whenever results change.

**Q11. How do you handle file uploads safely?**
Type and size are validated, the file is stored under a randomised name, and `.htaccess` prevents any PHP from executing inside the uploads directory.

**Q12. Can it handle many users / is it scalable?**
Yes for departmental scale. Indexed keys keep queries fast; the app is stateless per request (state lives in the DB and session), so it can move to a stronger server or managed MySQL if needed.

**Q13. How would you deploy this to the real school server?**
Copy the folder to the server's web root, create the database (or run `install.php`), set production credentials in `config.php`, switch `APP_ENV` to production (debug off), enable HTTPS, and remove `install.php`.

**Q14. What did you test?**
Registration and login (with matric/staff-id/email), role guards, clearance approve/reject, project review, result entry and CGPA recompute, announcements, reports/CSV export, and validation/error paths (bad input, wrong credentials, expired sessions).

**Q15. What are the limitations and future work?**
Current limits: email notifications and payment integration are out of scope. Future work: email/SMS alerts, online clearance-fee payment, analytics dashboards, and a REST API/mobile app.

**Q16. What makes your project original/valuable?**
It is tailored to Federal Polytechnic Nasarawa's actual Computer Science programmes and clearance units, is fully offline-capable, secure by design, and ready to deploy on the school's existing XAMPP setup.

**Q17. A clearance form normally needs signatures and a stamp — how can a student get a valid one without coming to school?**
The portal issues an **electronic clearance certificate**. It solves the signature problem three ways, based on how real institutions (e.g. NYSC, JAMB and several polytechnics) now issue verifiable documents online: (1) **each office signs electronically** — when an officer approves a unit, their name and approval date are printed against that unit, which is that office's signature; (2) the **HOD's authorised e-signature image and the department's official seal** are uploaded once by the admin and stamped on every certificate; and (3) every certificate carries a **unique certificate number and a QR code / verification link**. Because anyone can scan the code and confirm the details against the live database, a hand-written signature is not required and a forged copy cannot pass verification.

**Q18. What stops someone from editing the downloaded PDF?**
The certificate is only a printout of what the database says. The real proof is the online verification page (`verify.php`): it reads the student's record live and re-checks that every unit is still approved. If someone changes a name or matric number on the PDF, the certificate number will either not match those details or not exist, so the forgery is exposed. The certificate itself states that it is computer-generated and valid without a hand-written signature.

---

## PART 4 — CLEARANCE CERTIFICATE & ONLINE VERIFICATION

### For the student
1. Complete clearance — upload the required document for every unit and wait for each to be **Approved**.
2. When the progress bar reaches **100%**, open **Clearance** and click **Download Clearance Certificate**.
3. The official A4 certificate opens in a new tab showing your passport, bio-data, every cleared unit with the approving officer and date, the HOD signature and seal, and a QR/verification block.
4. Click **Download / Print (Save as PDF)** and choose *Save as PDF* (or print). You never have to visit the school to collect a signed form.

### For the admin (one-time setup)
1. Go to **Admin → Settings → Clearance certificate — signatories & official seal**.
2. Enter the HOD name/title and the second signatory name/title.
3. Upload the **HOD e-signature**, the optional **second signature**, and the **official seal/stamp** (clear PNG/JPG; transparent-background PNG looks best). These are stored in `uploads/signatures/`.
4. Save. From then on every certificate is automatically stamped. If no images are uploaded, the certificate still prints validly using the officers' typed names as electronic signatures.

### How verification works
- Each certificate has a permanent number in the format `FPN-CS-<year>-<studentID>-<random>` and a link/QR to `verify.php`.
- Any third party (employer, another school, NYSC) can open the link or scan the QR, enter the number, and instantly see whether it is **genuine and fully cleared** — read live from the database, so tampered copies fail.

---

*End of guide.*
