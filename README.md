# URESIMS — University Research and Extension Services Integration Management System

> **Extension Program and Project Management Module**
> Capstone Project — Bachelor of Science in Information Systems, CHMSU-Talisay

---

## 📖 Overview

URESIMS is a web-based management system designed for **Carlos Hilado Memorial State University (CHMSU)** to streamline the management of extension programs, projects, activities, and beneficiaries. This module focuses on the **Extension Services** arm, providing tools for tracking programs from proposal through completion, with a built-in **4-phase workflow engine**, **document management**, and **admin user management**.

---

## ⚙️ Tech Stack

| Layer                | Technology                                |
| -------------------- | ----------------------------------------- |
| **Backend**    | Laravel 12, PHP 8.2                       |
| **Database**   | MySQL 8.4                                 |
| **Frontend**   | Tailwind CSS 4, Alpine.js 3.15 (via Vite) |
| **Build Tool** | Vite 7                                    |
| **Server**     | Laragon (Apache/Nginx + MySQL)            |
| **Theme**      | CHMSU institutional green (`#1b5e20`)   |

---

## 🚀 Installation & Setup

### Prerequisites

- [Laragon](https://laragon.org/) (or any PHP 8.2+ / MySQL 8+ / Composer environment)
- Composer
- Node.js & npm

### Steps

```bash
# 1. Clone or copy the project into your web root
cd C:\laragon\www

# 2. Install PHP dependencies
composer install

# 3. Install frontend dependencies
npm install

# 4. Copy environment file and generate app key
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

# Create the storage symlink (required for document uploads)
php artisan storage:link
```

### Start the Development Server

```bash
# Option A: Start all services concurrently (server + queue + logs + Vite)
composer dev

# Option B: Start individually
php artisan serve --port=8001
npm run dev                        # Vite dev server (separate terminal)
```

Visit **http://127.0.0.1:8001** in your browser.

### Build for Production

```bash
npm run build
```

---

## 🔐 Default Login Credentials

| Role                      | Email              | Password     |
| ------------------------- | ------------------ | ------------ |
| **Admin**           | admin@chmsu.edu.ph | `password` |
| **Extension Staff** | staff@chmsu.edu.ph | `password` |

---

## 📦 Features

### Core CRUD Modules

- **Extension Programs** — Full CEFP (Community Extension Function Program) form with proponent info, cooperating entities, funding breakdown, duration, team members, and narrative sections (rationale, objectives, methodology).
- **Extension Projects** — Can be standalone or linked under a program. Tracks title, description, persons responsible, budget, dates, and status.
- **Extension Activities** — Linked to projects. Tracks title, description, location, facilitator, dates, participants, and status.
- **Extension Beneficiaries** — Linked to projects. Tracks type, sector, and male/female/total count breakdowns.

### Workflow & Document Management

- **4-Phase Workflow** — Programs, projects, and activities follow a formal status lifecycle: `Draft` → `Proposal` → `Ongoing` → `Completed`.
- **Requirements Checking** — Each phase enforces required fields and documents before advancement (e.g., proposal requires a "Proposal Document", completion requires a "Terminal/Completion Report").
- **Phase-Aware Document Uploads** — Upload supporting documents tied to specific workflow phases with format and file-size validation.
- **Admin Bypass** — Admins can skip workflow phases with a mandatory reason (min 10 characters), logged for audit.
- **Transition Logging** — All status transitions are recorded in an audit log with timestamps, actor, and bypass reason (if any).
- **Program Completion Guard** — A program cannot advance to `Completed` until all its child projects are `Completed`.

### Admin & User Management

- **User CRUD** — Admin-only interface to create, edit, and manage user accounts.
- **Activate / Deactivate Users** — Toggle user accounts on or off; deactivated users are auto-logged out via middleware.
- **Role-Based Access Control** — `admin` and `extension_staff` roles with middleware-protected routes.

### Key Functionalities

| Feature                           | Description                                                                                                                                |
| --------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| **Dashboard**               | Overview stats (total programs, projects, activities, beneficiaries with gender breakdown), status breakdowns, overdue items, recent items |
| **Role-Based Access**       | Admin and Extension Staff roles with `RoleMiddleware` and `EnsureUserIsActive` middleware                                              |
| **Inline Project Creation** | Add/edit/remove projects directly within the Program create/edit form using Alpine.js dynamic rows                                         |
| **Dynamic Team Members**    | Add/remove program members with name and responsibility fields                                                                             |
| **Confirmation Dialogs**    | Alpine.js modal on all create, update, and delete actions (green for save, red for delete)                                                 |
| **Filtering & Search**      | Filter by status, campus, and free-text search on index pages                                                                              |
| **Auto-Calculated Funding** | Program funding total auto-sums GAA + STF + Collaborator contributions                                                                     |
| **Sidebar Workflow Counts** | Sidebar dynamically displays per-phase counts for programs and projects via a view composer                                                |
| **Overdue Tracking**        | Dashboard highlights activities and projects past their target date that are not yet completed                                             |

---

## 🗄️ Database Schema

### Tables (9 migrations)

| Table                         | Description                                                                                       |
| ----------------------------- | ------------------------------------------------------------------------------------------------- |
| `users`                     | User accounts with `role` (admin / extension_staff), `is_active` flag, and optional campus FK |
| `campuses`                  | CHMSU campus locations (Talisay, Binalbagan, Alijis, Fortune Towne)                               |
| `extension_programs`        | Full CEFP program records (~25 columns) with all form fields                                      |
| `extension_program_members` | Program team members (FK → programs)                                                             |
| `extension_projects`        | Projects, standalone or under a program (nullable FK → programs)                                 |
| `extension_activities`      | Activities under projects (FK → projects)                                                        |
| `extension_beneficiaries`   | Beneficiary records with type, sector, male/female/total counts (FK → projects)                  |
| `extension_budget_items`    | Budget line items with location, description, and amount (FK → projects)                         |
| `status_documents`          | Polymorphic documents attached to workflow phases (programs, projects, or activities)             |
| `status_transition_logs`    | Polymorphic audit log for all status transitions (programs, projects, or activities)              |

### Entity Relationships

```
Campus (1) ──┬──< ExtensionProgram (1) ──┬──< ExtensionProgramMember
             │                           ├──< ExtensionProject (1) ──┬──< ExtensionActivity
             │                           │                           ├──< ExtensionBeneficiary
             │                           │                           ├──< ExtensionBudgetItem
             │                           │                           ├──< StatusDocument (morph)
             │                           │                           └──< StatusTransitionLog (morph)
             │                           ├──< StatusDocument (morph)
             │                           └──< StatusTransitionLog (morph)
             └──< ExtensionProject (standalone)
           
User ──> Campus (nullable)
User ──< uploaded StatusDocuments
User ──< performed StatusTransitionLogs

ExtensionProject (1) ──< EvaluationForm (1) ──< EvaluationCriteria
                                             └──< EvaluationResponse (1) ──< EvaluationAnswer
```

---

## 📂 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/LoginController.php         # Login / logout
│   │   ├── Controller.php                   # Base controller
│   │   ├── DashboardController.php          # Dashboard stats & overview
│   │   ├── ExtensionProgramController.php   # Programs CRUD
│   │   ├── ExtensionProjectController.php   # Projects CRUD
│   │   ├── ExtensionActivityController.php  # Activities CRUD
│   │   ├── ExtensionBeneficiaryController.php # Beneficiaries CRUD
│   │   ├── UserController.php               # Admin user management
│   │   ├── WorkflowController.php           # Workflow advance/bypass/documents
│   │   ├── ProposalWizardController.php     # Multi-step proposal submission wizard
│   │   ├── EvaluationFormController.php     # Evaluation form CRUD (admin)
│   │   └── PublicEvaluationController.php   # Public evaluation form responses
│   ├── Middleware/
│   │   ├── EnsureUserIsActive.php           # Auto-logout deactivated users
│   │   └── RoleMiddleware.php               # Role-based route protection
│   └── Requests/
│       ├── StoreExtensionProgramRequest.php
│       ├── UpdateExtensionProgramRequest.php
│       ├── StoreExtensionProjectRequest.php
│       ├── UpdateExtensionProjectRequest.php
│       ├── StoreExtensionActivityRequest.php
│       └── UpdateExtensionActivityRequest.php
├── Models/
│   ├── User.php                    # User with roles & is_active
│   ├── Campus.php                  # Campus locations
│   ├── ExtensionProgram.php        # CEFP program model
│   ├── ExtensionProgramMember.php  # Program team members
│   ├── ExtensionProject.php        # Projects (standalone or under program)
│   ├── ExtensionActivity.php       # Activities under projects
│   ├── ExtensionBeneficiary.php    # Beneficiary records
│   ├── ExtensionBudgetItem.php     # Budget line items
│   ├── StatusDocument.php          # Polymorphic workflow documents
│   ├── StatusTransitionLog.php     # Polymorphic transition audit log
│   ├── EvaluationForm.php          # Evaluation forms for projects
│   ├── EvaluationCriteria.php      # Criteria within evaluation forms
│   ├── EvaluationResponse.php      # Public evaluation form responses
│   └── EvaluationAnswer.php        # Individual criterion answers
├── Providers/
│   └── AppServiceProvider.php      # View composer for sidebar counts
└── Services/
    └── WorkflowService.php         # 4-phase workflow engine

database/
├── migrations/                     # 10 migration files
└── seeders/
    └── DatabaseSeeder.php          # All seed data in one seeder

resources/views/
├── layouts/app.blade.php           # Main layout (sidebar, topbar, modal)
├── auth/login.blade.php            # Login page
├── dashboard/index.blade.php       # Dashboard
├── admin/
│   └── users/                      # index, create, edit
└── extension/
    ├── programs/                   # index, create, edit, show
    ├── projects/                   # index, create, edit, show
    ├── activities/                 # index, create, edit
    └── beneficiaries/              # index, create, edit

routes/web.php                      # All application routes
```

---

## 🛣️ Routes

| Method   | URI                                       | Action                                |
| -------- | ----------------------------------------- | ------------------------------------- |
| GET      | `/`                                     | Redirect to login                     |
| GET/POST | `/login`                                | Authentication                        |
| POST     | `/logout`                               | Logout                                |
| GET      | `/dashboard`                            | Dashboard                             |
| Resource | `/extension/programs`                   | Programs CRUD                         |
| Resource | `/extension/projects`                   | Projects CRUD                         |
| Resource | `/extension/activities`                 | Activities CRUD (no show)             |
| Resource | `/extension/beneficiaries`              | Beneficiaries CRUD (no show)          |
| POST     | `/workflow/{type}/{id}/advance`         | Advance workflow phase                |
| POST     | `/workflow/{type}/{id}/bypass`          | Admin bypass workflow phase           |
| POST     | `/workflow/{type}/{id}/upload-document` | Upload phase document                 |
| DELETE   | `/workflow/document/{document}`         | Delete a document                     |
| Resource | `/admin/users`                          | User management (admin only, no show) |
| PATCH    | `/admin/users/{user}/toggle-active`     | Toggle user active status             |
| Resource | `/extension/projects/{project}/evaluation-forms` | Evaluation form CRUD (admin) |
| GET      | `/evaluate/{form:uuid}`                 | Public evaluation form                |
| POST     | `/evaluate/{form:uuid}`                 | Submit public evaluation response     |
| GET/POST | `/proposal/{type}/...`                  | Proposal wizard (multi-step)          |

---

## 🧪 Seeded Test Data

The `DatabaseSeeder` populates the system with:

- **4 Campuses** — Talisay, Binalbagan, Alijis, Fortune Towne
- **2 Users** — 1 admin (`admin@chmsu.edu.ph`), 1 extension staff (`staff@chmsu.edu.ph`)
- **1 Program** — "Community Empowerment Through Sustainable Livelihood Development" (Ongoing, Talisay)
- **4 Program Members** — Team members for the program
- **3 Projects** — 2 under the program, 1 standalone (Alijis)
- **4 Activities** — Distributed across projects with varied statuses
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
- The frontend is built with **Tailwind CSS 4** and **Alpine.js 3.15** via **Vite** — run `npm run dev` for development or `npm run build` for production assets.
- All confirmation dialogs use a global Alpine.js modal component defined in the layout.
- The `php artisan serve` dev server is single-threaded; for better performance, use Laragon's built-in Apache or Nginx.
- All models use **hard deletes** with cascading foreign keys (no soft deletes).
- Document uploads are stored on the `public` disk — ensure `php artisan storage:link` has been run.
- The `composer dev` script starts the Laravel server, queue worker, Pail log viewer, and Vite dev server concurrently.

---

## 👤 Author

**BSIS 3rd Year Student**
Carlos Hilado Memorial State University — Talisay Campus
AY 2025–2026

---

*Built with ❤️ using Laravel, Tailwind CSS, and Alpine.js*
