# MyEduConnect Portal - Vulnerable Environment

## 🛠️ Prerequisites

Before deploying the platform, ensure you have the following installed on your host system:
- **Docker Desktop** (with the WSL 2 backend enabled if you are running on Windows)
- **Git** (optional)

---

## 🚀 Deployment Instructions

### 1. Get the Source Code
- **Option A (Simplest):** Download the repository as a **ZIP file** from the GitHub web interface and extract it.
- **Option B (Git CLI):** Clone the repository:
  ```bash
  git clone <your-private-github-repo-link>
  ```

### 2. Build and Start the Containers
The Docker setup lives inside the `EticalHackingAss/` project folder. Change into it first, then bring up the stack:
```bash
cd EticalHackingAss/EticalHackingAss
docker compose up --build -d
```

### 3. Access the Portal
Once the containers are running, open your browser at:
👉 http://localhost

---

## 🔧 Troubleshooting: Port 80 Conflicts
If `docker compose up` reports that **Port 80 is already allocated / in use**, another local service
(Skype, Apache, IIS, XAMPP, etc.) is occupying Port 80 on your system.

**The fix:** open `EticalHackingAss/docker-compose.yml`, find the `ports` mapping under the
`web_portal` service, and change `"80:80"` to `"8080:80"`. Re-run `docker compose up -d` and browse to:
👉 http://localhost:8080

---

## 🔑 Default Seed Credentials

| Role    | Username    | Password                |
|---------|-------------|-------------------------|
| Student | `student01` | `studentpass`           |
| Admin   | `admin_mmu` | `SuperSecureAdmin2026!` |

---

## 📁 Project Directory Layout

```
EticalHackingAss/                # Docker project root — run "docker compose" from here
├── Dockerfile                   # Apache + PHP 7.4 image (EOL — outdated-package weakness), installs MySQLi
├── docker-compose.yml           # Orchestrates web_portal (PHP) + db_server (MySQL 5.7) containers
├── db/
│   └── init.sql                 # DB init script: creates tables and seed data on first startup
└── src/                         # Web root, mounted into the container at /var/www/html
    ├── index.php                # Login page — SQL Injection target
    ├── dashboard.php            # Student workspace — Stored XSS & Unrestricted File Upload target
    ├── profile.php              # Student directory — IDOR target
    ├── courses.php              # Course search — SQL Injection & XSS target
    ├── payment.php              # Mock checkout — Parameter Tampering target
    ├── transcript.php           # Frontend transcript viewer (loads API data dynamically)
    ├── api_grades.php           # Backend transcript REST API — BOLA / IDOR target
    ├── admin.php                # Administrative control panel — Broken Access Control target
    ├── db_connect.php           # DB connection logic (connects as root with an empty password — credential weakness)
    ├── feedback.php             # Feedback submission form
    └── logout.php               # Destroys active user sessions
```

> `uploads/` is created automatically inside the `web_portal` container (with write permissions)
> for student assignment and profile-picture uploads.
