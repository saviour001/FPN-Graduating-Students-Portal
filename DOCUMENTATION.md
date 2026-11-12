# Technical Documentation
## Design and Implementation of a Web-Based Portal for Graduating Students of the Computer Science Department
### Federal Polytechnic Nasarawa — School of Information Technology

---

## 1. Introduction

Graduation processing in many Nigerian polytechnics is still largely manual: students move physically
from office to office collecting clearance signatures, project submissions are on paper, and alumni
data is rarely captured. This project delivers a **web-based Graduating Students Portal** for the
**Department of Computer Science, Federal Polytechnic Nasarawa (FPN)** that digitises the entire
graduation workflow — clearance, project submission, results, alumni registration and exit surveys.

### 1.1 Aim
To design and implement a secure, role-based web portal that automates graduation clearance and
related processes for graduating Computer Science students.

### 1.2 Objectives
1. Provide an online, multi-unit clearance workflow with real-time status tracking.
2. Allow electronic submission and supervisor grading of final year projects.
3. Manage students, results and announcements from an administrative console.
4. Capture alumni records and exit-survey feedback for departmental quality assurance.
5. Generate reports and an exportable list of fully-cleared graduands.

### 1.3 Scope
The portal covers the Computer Science department programmes only:
- **ND** Computer Science
- **HND** Software & Web Development
- **HND** Networking & Cloud Computing

---

## 2. Background research (FPN Computer Science)

The Department of Computer Science operates under the **School of Information Technology** at Federal
Polytechnic Nasarawa. It runs a **National Diploma (ND) in Computer Science** and a **Higher National
Diploma (HND)** split into two options: **Software & Web Development** and **Networking & Cloud
Computing**. The institution's guiding motto is *"Learning, Technology & Service."* These realities
directly shaped the programme list, clearance units and branding used throughout the system.

---

## 3. System analysis

### 3.1 Limitations of the existing (manual) system
- Long physical queues and lost clearance forms.
- No central record of who has/ hasn't been cleared.
- Paper-based project submission with no audit trail.
- No structured alumni or feedback data.

### 3.2 The proposed system
A centralized web application with three actors (Student, Staff, Admin) that removes the manual
bottlenecks above and provides dashboards, tracking and reporting.

### 3.3 Functional requirements
- Account registration/authentication with role separation.
- Per-unit clearance submission and approval/rejection with remarks.
- Project upload, supervisor assignment, grading and feedback.
- Result upload and automatic CGPA computation.
- Announcements targeted by programme.
- Alumni + exit survey capture.
- Reporting and CSV export.

### 3.4 Non-functional requirements
Security, usability (responsive UI), reliability, maintainability and performance.

---

## 4. System design

### 4.1 Architecture
A classic **3-tier architecture**:
1. **Presentation** — PHP-rendered HTML + a single responsive `style.css` and small vanilla JS.
2. **Application/Logic** — PHP procedural helpers (`functions.php`, `auth.php`) and per-page controllers.
3. **Data** — MySQL accessed exclusively through **PDO prepared statements**.

### 4.2 Database design (11 tables)
| Table | Purpose |
|-------|---------|
| `users` | All accounts (student/staff/admin), credentials, status |
| `student_profiles` | Programme, level, bio-data, CGPA, passport |
| `clearance_units` | Configurable clearance offices/units |
| `clearance_requests` | One row per student per unit + status/remark |
| `projects` | Final year project submission, grade, feedback |
| `results` | Semester GPA records (drives CGPA) |
| `announcements` | Notices targeted by audience |
| `alumni_records` | Alumni contact + employment data |
| `exit_surveys` | Graduating-student feedback ratings |
| `activity_log` | Audit trail of key actions |
| `settings` | Key/value portal configuration |

Relationships use `FOREIGN KEY ... ON DELETE CASCADE` so deleting a user cleanly removes dependent rows.

### 4.3 Clearance workflow
```
Student submits unit  ->  status: pending
        |
        v
Staff / Admin reviews  ->  approved  OR  rejected (+ remark)
        |
        v
All units approved     ->  student can print Clearance Summary Certificate
```

---

## 5. Implementation

### 5.1 Technology stack
- **PHP 8.x** (procedural, PDO)
- **MySQL / MariaDB**
- **HTML5 + CSS3** (custom design system, no framework)
- **Vanilla JavaScript** (sidebar, confirmations, password toggles)
- **Apache** (XAMPP) for local deployment

### 5.2 Key security implementations
- **SQL injection:** every query uses parameterised PDO statements.
- **Passwords:** `password_hash()` (bcrypt) + `password_verify()`; no plain text stored.
- **XSS:** all dynamic output escaped with the `e()` helper (`htmlspecialchars`).
- **CSRF:** per-session token embedded in all POST forms and verified server-side.
- **Access control:** `require_login()` and `require_role()` guards on every protected page.
- **File uploads:** whitelisted extensions, size cap, randomised filenames, `.htaccess` disabling PHP execution in `uploads/`.
- **Sessions:** custom session name, regeneration on login, idle lifetime.

### 5.3 Notable modules
- **Student clearance** (`student/clearance.php`) — visual step timeline + per-unit document upload.
- **Certificate** (`student/clearance_certificate.php`) — printable, serial-numbered summary.
- **Admin reports** (`admin/reports.php`) — programme completion charts + CSV graduand export.
- **Installer** (`install.php`) — creates hashed default accounts, self-locks after use.

---

## 6. Testing

| Test | Expected | Result |
|------|----------|--------|
| Login with wrong password | Rejected with message | Pass |
| SQL injection in login field | Treated as literal string | Pass |
| Student accesses admin URL | Redirected / denied | Pass |
| Upload oversized/invalid file | Rejected with message | Pass |
| Complete all clearance units | Certificate becomes printable | Pass |
| Add result | CGPA recomputed on profile | Pass |

(Manual/black-box testing performed on XAMPP.)

---

## 7. Deployment

See `README.md` for step-by-step XAMPP installation. For live hosting, upload the folder to the web
root, create the MySQL database, import the SQL, set `config/config.php` credentials and `BASE_URL`,
run `install.php` once, then delete it.

---

## 8. Conclusion & future work

The portal successfully digitises the graduation process for the FPN Computer Science department.
**Future enhancements:** email/SMS notifications, payment integration for clearance fees, PDF
generation via a library, biometric verification, and a public alumni directory.

---

## Appendix A — Default accounts
| Role | Login | Password |
|------|-------|----------|
| Admin | ADMIN001 | Admin@12345 |
| Staff | STAFF001 | Staff@12345 |

## Appendix B — Default clearance units
Department, School of IT, Library, Bursary, Student Affairs, Health/Hostel, Project/Supervisor, Alumni.
