# URESIMS — University Research and Extension Services Integration Management System

> **Extension Program and Project Management Module**
> Capstone Project — Bachelor of Science in Information Systems, CHMSU-Talisay

---

## 📖 Overview

URESIMS is a web-based management system designed for **Carlos Hilado Memorial State University (CHMSU)** to streamline the management of extension programs, projects, activities, and beneficiaries. This module focuses on the **Extension Services** arm, providing tools for tracking programs from proposal through completion.

---

## ⚙️ Tech Stack

| Layer        | Technology                                     |
| ------------ | ---------------------------------------------- |
| **Backend**  | Laravel 12, PHP 8.2                            |
| **Database** | MySQL 8.4                                      |
| **Frontend** | Tailwind CSS (CDN), Alpine.js 3.14 (CDN)       |
| **Server**   | Laragon (Apache/Nginx + MySQL)                 |
| **Theme**    | CHMSU institutional green (`#1b5e20`)          |

---

## 🚀 Installation & Setup

### Prerequisites

- [Laragon](https://laragon.org/) (or any PHP 8.2+ / MySQL 8+ / Composer environment)
- Composer

### Steps

```bash
# 1. Clone or copy the project into your web root
cd C:\laragon\www

# 2. Install PHP dependencies
composer install

# 3. Copy environment file and generate app key
cp .env.example .env
php artisan key:generate
```

### Configure `.env`

```dotenv
APP_NAME=URESIMS
APP_URL=http://uresims.test

DB_DATABASE=uresims
DB_USERNAME=root
DB_PASSWORD=
```

### Database Setup

```bash
# Create the database (via MySQL CLI or phpMyAdmin)
mysql -u root -e "CREATE DATABASE uresims"

# Run migrations and seed test data
php artisan migrate --seed
```

### Start the Development Server

```bash
php artisan serve --port=8001
```

Visit **http://127.0.0.1:8001** in your browser.

---

## 🔐 Default Login Credentials

| Role             | Email                | Password   |
| ---------------- | -------------------- | ---------- |
| **Admin**        | admin@chmsu.edu.ph   | `password` |
| **Extension Staff** | staff@chmsu.edu.ph | `password` |

---

## 📦 Features

### Core CRUD Modules

- **Extension Programs** — Full CEFP (Community Extension Function Program) form with proponent info, cooperating entities, funding breakdown, duration, team members, and narrative sections (rationale, objectives, methodology).
- **Extension Projects** — Can be standalone or linked under a program. Tracks title, description, persons responsible, budget, dates, and status.
- **Extension Activities** — Linked to projects. Tracks title, description, location, facilitator, dates, participants, and status.
- **Extension Beneficiaries** — Linked to projects. Tracks name, gender, age, contact information, and address.

### Key Functionalities

| Feature                        | Description                                                                                        |
| ------------------------------ | -------------------------------------------------------------------------------------------------- |
| **Dashboard**                  | Overview stats (total programs, projects, activities, beneficiaries), status breakdowns, recent items |
| **Role-Based Access**          | Admin and Extension Staff roles with middleware protection                                          |
| **Inline Project Creation**    | Add/edit/remove projects directly within the Program create/edit form using Alpine.js dynamic rows   |
| **Dynamic Team Members**       | Add/remove program members with name and responsibility fields                                      |
| **Confirmation Dialogs**       | Alpine.js modal on all create, update, and delete actions (green for save, red for delete)          |
| **Filtering & Search**         | Filter by status, campus, and free-text search on index pages                                      |
| **Auto-Calculated Funding**    | Program funding total auto-sums GAA + STF + Collaborator contributions                            |
| **Status Workflow**            | Three-stage status: `Proposal` → `Ongoing` → `Completed`                                          |
| **Read-Only Program Field**    | Beneficiary edit shows the parent program (if any) as a non-editable reference                     |

---

## 🗄️ Database Schema

### Custom Tables (9 migrations)

| Table                            | Description                                               |
| -------------------------------- | --------------------------------------------------------- |
| `users` (+ role column)         | Admin and Extension Staff accounts                        |
| `campuses`                       | CHMSU campus locations (Talisay, Binalbagan, Alijis, Fortune Towne) |
| `extension_programs`             | Full CEFP program records with all form fields            |
| `extension_program_members`      | Program team members (FK → programs)                      |
| `extension_projects`             | Projects, standalone or under a program (nullable FK → programs) |
| `extension_activities`           | Activities under projects (FK → projects)                 |
| `extension_beneficiaries`        | Beneficiary records (FK → projects)                       |
| `extension_budget_items`         | Polymorphic budget line items for programs or projects     |

### Entity Relationships

```
Campus
 ├── has many Programs
 └── has many Projects

Program
 ├── has many Members
 ├── has many Projects
 └── has many Activities (through Projects)

Project (standalone or under a Program)
 ├── has many Activities
 ├── has many Beneficiaries
 └── has many Budget Items

Activity
 └── belongs to Project
```

---

## 📂 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/LoginController.php
│   │   ├── DashboardController.php
│   │   ├── ExtensionProgramController.php
│   │   ├── ExtensionProjectController.php
│   │   ├── ExtensionActivityController.php
│   │   └── ExtensionBeneficiaryController.php
│   ├── Middleware/
│   │   └── RoleMiddleware.php
│   └── Requests/
│       ├── StoreExtensionProgramRequest.php
│       ├── UpdateExtensionProgramRequest.php
│       ├── StoreExtensionProjectRequest.php
│       ├── UpdateExtensionProjectRequest.php
│       ├── StoreExtensionActivityRequest.php
│       └── UpdateExtensionActivityRequest.php
├── Models/
│   ├── User.php
│   ├── Campus.php
│   ├── ExtensionProgram.php
│   ├── ExtensionProgramMember.php
│   ├── ExtensionProject.php
│   ├── ExtensionActivity.php
│   ├── ExtensionBeneficiary.php
│   └── ExtensionBudgetItem.php

database/
├── migrations/          # 12 migration files
└── seeders/
    └── DatabaseSeeder.php

resources/views/
├── layouts/app.blade.php          # Main layout (sidebar, topbar, modal)
├── auth/login.blade.php           # Login page
├── dashboard/index.blade.php      # Dashboard
└── extension/
    ├── programs/                  # index, create, edit, show
    ├── projects/                  # index, create, edit, show
    ├── activities/                # index, create, edit
    └── beneficiaries/             # index, create, edit

routes/web.php                     # All application routes
```

---

## 🛣️ Routes

| Method   | URI                                | Action            |
| -------- | ---------------------------------- | ----------------- |
| GET      | `/`                                | Redirect to login |
| GET/POST | `/login`                           | Authentication    |
| POST     | `/logout`                          | Logout            |
| GET      | `/dashboard`                       | Dashboard         |
| Resource | `/extension/programs`              | Programs CRUD     |
| Resource | `/extension/projects`              | Projects CRUD     |
| Resource | `/extension/activities`            | Activities CRUD (no show) |
| Resource | `/extension/beneficiaries`         | Beneficiaries CRUD (no show) |

---

## 🧪 Seeded Test Data

The `DatabaseSeeder` populates the system with:

- **4 Campuses** — Talisay, Binalbagan, Alijis, Fortune Towne
- **2 Users** — 1 admin, 1 extension staff
- **1 Program** — "Community Empowerment Through Sustainable Livelihood Development"
- **3 Projects** — 2 under the program, 1 standalone
- **4 Activities** — Distributed across projects
- **6 Beneficiaries** — Across different projects
- **5 Budget Items** — Linked to the program and projects

To reset and reseed:

```bash
php artisan migrate:fresh --seed
```

---

## ⚡ Performance Optimization

For faster page loads during development, run:

```bash
php artisan optimize        # Caches config, routes, events, views
php artisan view:cache      # Pre-compiles all Blade templates
```

To clear caches after making changes:

```bash
php artisan optimize:clear
```

---

## 📝 Notes

- This module covers the **Extension Services** portion of URESIMS only.
- The system uses **Tailwind CSS CDN** and **Alpine.js CDN** — no build step (npm) is required.
- All confirmation dialogs use a global Alpine.js modal component defined in the layout.
- The `php artisan serve` dev server is single-threaded; for better performance, use Laragon's built-in Apache or Nginx.

---

## 👤 Author

**BSIS 3rd Year Student**
Carlos Hilado Memorial State University — Talisay Campus
AY 2025–2026

---

*Built with ❤️ using Laravel, Tailwind CSS, and Alpine.js*
