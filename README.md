# MyEduConnect Portal - Vulnerable Environment

## 🛠️ Prerequisites

Before deploying the platform, ensure you have the following installed on your host system:
- **Docker Desktop** (with the WSL 2 backend enabled if you are running on Windows)
- **Git** (optional)

---

## 🚀 Deployment Instructions

Follow these steps to deploy and run the MyEduConnect portal locally:

### 1. Get the Source Code
- **Option A (Simplest):** Download the repository as a **ZIP file** from the GitHub web interface, extract the contents on your local machine.

- **Option B (Git CLI):** Clone the repository directly using Git:
  ```bash
  git clone <your-private-github-repo-link>
  cd "foldername"
  
### 2. Build and Start the Containers
Run the following command in your terminal in vscode.

docker compose up --build -d

### 3. Access the Portal
Once the containers are running, navigate to your web browser and open:
👉 http://localhost

---
🔧 Troubleshooting: Port 80 Conflicts
If you run docker compose up and receive an error stating that Port 80 is already allocated or in use:
It means another local service (such as Skype, local Apache, IIS, or XAMPP) is currently occupying Port 80 on your system.
The Fix: Open docker-compose.yml in a text editor, find the ports mapping under the web service, and change "80:80" to "8080:80".
Re-run docker compose up -d and access the portal in your browser at:
👉 http://localhost:8080

---
🔑 Default Seed Credentials
Use these pre-configured user credentials to log in and navigate the portal:
Role	Username / ID	Password

Username: student01	
Password: studentpass	

Admin	Username: admin_mmu	
Password: SuperSecureAdmin2026!	

---
📁 Project Directory Layout
/uploads/ - Target directory for student assignments and profile picture uploads (automatically created inside the container with write permissions).
Dockerfile - Configures the Apache-PHP environment and installs standard MySQLi database extensions [1].
docker-compose.yml - Orchestrates the multi-container environment (web frontend and MySQL backend) [3].
schema.sql - Database initialization script that automatically populates tables and seed data upon first startup [3].
db_connect.php - Secure database connection logic.
index.php - Portal login page (SQLi target).
dashboard.php - Student Workspace (Stored XSS & Unrestricted File Upload target).
profile.php - Student Directory (IDOR target).
courses.php - Course Search (SQLi & XSS target).
payment.php - Mock Checkout Page (Parameter Tampering target).
transcript.php - Frontend Transcript Viewer (Loads API data dynamically).
api_grades.php - Backend Transcript REST API (BOLA / IDOR target).
admin.php - Administrative Control Panel (Broken Access Control target).
feedback.php - Feedback submission form.
logout.php - Destroys active user sessions.
