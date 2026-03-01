# Extension Program Management System — Workflow Specification

> **Version:** 4.0 (Simplified)  
> **Date:** February 26, 2026  
> **System:** URESIMS — University Research & Extension Services Information Management System  
> **Framework:** Laravel 12 (PHP 8.2+)

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Entity-Relationship Model](#2-entity-relationship-model)
3. [Hierarchy & Structural Integrity Rules](#3-hierarchy--structural-integrity-rules)
4. [Workflow State Machine](#4-workflow-state-machine)
5. [Phase Definitions & Requirements Matrix](#5-phase-definitions--requirements-matrix)
6. [Proposal Wizard](#6-proposal-wizard)
7. [Document Management System](#7-document-management-system)
8. [Beneficiary / Participant Management](#8-beneficiary--participant-management)
9. [Budget & Member Management](#9-budget--member-management)
10. [Role-Based Access Control](#10-role-based-access-control)
11. [Business Rules Summary](#11-business-rules-summary)
12. [Database Schema Reference](#12-database-schema-reference)
13. [API / Route Reference](#13-api--route-reference)

---

## 1. System Overview

The Extension Program Management System tracks the complete lifecycle of university extension programs, from initial draft through formal proposal submission, administrative approval, active implementation, and final completion.

### 1.1 Core Entities

| Entity | Description | Can Exist Independently? |
|--------|-------------|:------------------------:|
| **Program** | Top-level grouping of related extension projects | Yes |
| **Project** | A specific undertaking with defined scope and deliverables | Yes (standalone) or under a Program |
| **Activity** | A discrete task or event within a project | No — must belong to a Project |
| **Beneficiary** | Individuals or groups served by the extension work | Owned by a Project |
| **Document** | Uploaded files (proposals, reports, evidence) | Attached to any entity via polymorphic relation |
| **Budget Item** | Line-item budget entries for a project | Owned by a Project |
| **Program Member** | Team members assigned to a program | Owned by a Program |

### 1.2 Design Principles

- **Hierarchy enforcement**: A program cannot exist without at least one project once submitted; a project cannot exist without at least one activity once submitted.
- **Bottom-up advancement**: Activities must be advanced before their project; projects before their program. Users must advance child entities first, then the parent.
- **Admin approval gate**: Advancing from Proposal → Ongoing requires an administrator. Extension staff cannot self-approve their own proposals.
- **Immediate file listing**: Uploaded files appear instantly; the document type/label can be edited afterward (with guards against breaking required-doc constraints).
- **Full audit trail**: Every status transition is logged with actor, timestamp, and optional notes.
- **Simple document management**: Upload, edit labels, or delete and re-upload. No complex versioning.
- **Direct beneficiary ownership**: Each beneficiary belongs to exactly one project.

---

## 2. Entity-Relationship Model

```
┌──────────────────┐
│     Campus       │
└──────┬───────────┘
       │ 1:N
       ▼
┌──────────────────┐       1:N        ┌─────────────────────────┐
│ ExtensionProgram │─────────────────▶│   ExtensionProject      │
│                  │                  │  (extension_program_id   │
│  • status        │                  │   nullable → standalone) │
│  • draft_data    │                  │                          │
│  • campus_id     │                  │  • status                │
│  • created_by    │                  │  • draft_data            │
└──────┬───────────┘                  └──────┬──────────────────┘
       │ 1:N                                 │ 1:N     1:N     1:N
       ▼                                     ▼         ▼       ▼
┌──────────────────┐              ┌──────────┐ ┌──────────┐ ┌──────────┐
│ ProgramMember    │              │ Activity │ │Beneficiary│ │BudgetItem│
│  • name          │              │ • status │ │ • type   │ │ • item   │
│  • responsibility│              │          │ │ • sector │ │ • amount │
└──────────────────┘              └──────────┘ └──────────┘ └──────────┘

╔══════════════════════════════════════════════════════╗
║  Polymorphic Relations (attached to any entity):     ║
║  • StatusDocument       (uploaded files)              ║
║  • StatusTransitionLog  (audit trail)                 ║
╚══════════════════════════════════════════════════════╝
```

---

## 3. Hierarchy & Structural Integrity Rules

### 3.1 Containment Rules

| Rule | Enforcement |
|------|-------------|
| A **Program** must have **≥1 Project** | Checked when advancing from `proposal` phase |
| A **Project** must have **≥1 Activity** | Checked when advancing from `proposal` phase |
| A **Project** must have **≥1 Beneficiary** | Checked when advancing from `proposal` phase |
| An **Activity** must always belong to a **Project** | Enforced via `extension_project_id NOT NULL` |
| A **Project** may be **standalone** (no program) | `extension_program_id` is nullable |

### 3.2 Deletion Constraints

| Rule | Enforcement |
|------|-------------|
| Cannot delete an entity in `ongoing` or `completed` status | `WorkflowService::canDelete()` |
| Cannot delete the **last project** of a submitted program | Sibling count check in `canDelete()` |
| Cannot delete the **last activity** of a submitted project | Sibling count check in `canDelete()` |
| Deleting a program **cascades** to its projects, activities, documents | Database `ON DELETE CASCADE` |

### 3.3 Bottom-Up Advancement

Entities must be advanced **children-first**. A parent cannot enter a phase unless all its children meet the structural requirements for that target phase.

**Practical workflow order:**
1. Advance all Activities to the desired phase first.
2. Then advance their parent Project.
3. Then advance the parent Program.

```
To ENTER "ongoing" phase:
  Program requires ──► All projects at ≥ "ongoing"
  Project requires ──► All activities at ≥ "ongoing"

To ENTER "completed" phase:
  Program requires ──► All projects at "completed"
  Project requires ──► All activities at "completed"
```

> **Note:** The check is performed against the **target phase** (the phase being entered), not the phase being left. For example, when a project is currently at `proposal` and requests advancement, the system checks whether all its activities are `≥ ongoing` (the target phase rule), not whether the proposal-phase rules are met.

---

## 4. Workflow State Machine

All three entity types (Program, Project, Activity) share the same four-phase lifecycle:

```
  ┌─────────┐    submit     ┌───────────┐   admin      ┌──────────┐    complete    ┌───────────┐
  │  DRAFT  │──────────────▶│ PROPOSAL  │──approves───▶│ ONGOING  │──────────────▶│ COMPLETED │
  │  (0)    │               │   (1)     │              │   (2)    │               │    (3)    │
  └─────────┘               └───────────┘              └──────────┘               └───────────┘
       │                          │                          │                           │
       │  ◄── Editable ──►       │  ◄── Review ──►         │  ◄── Active ──►          │  ◄── Final ──►
       │  Free-form data entry   │  Must meet all           │  Implementation          │  Locked, reports
       │  Upload drafts          │  requirements.           │  phase; upload            │  and documentation
       │                         │  Awaits admin            │  monitoring docs          │  submitted
       │                         │  approval to advance.    │                           │
       └─────────────────────────┴──────────────────────────┴───────────────────────────┘
                                        ▲
                                        │  Admin can BYPASS
                                        │  forward or backward
                                        │  (with logged reason)
                                        ▼
```

### 4.1 Transition Rules

| From → To | Trigger | Who Can Do It | Requirements |
|-----------|---------|---------------|--------------|
| `draft` → `proposal` | User submits | Admin or Owner | Current-phase fields + target-phase fields & docs met |
| `proposal` → `ongoing` | Admin approves | **Admin only** | Current + target fields & docs + structural rules |
| `ongoing` → `completed` | User/admin advances | Admin or Owner | Current + target fields & docs + all children completed |
| Any → Any (forward) | Admin bypass | **Admin only** | Bypass reason logged (min 10 chars); warnings for skipped requirements |
| Any → Any (backward) | Admin bypass | **Admin only** | Bypass reason logged (min 10 chars); optional child cascade |

**Validation on advancement (`canAdvance`):**
1. **Target-phase requirements** — all required fields and documents for the phase being *entered* must be fulfilled.
2. **Structural rules** — hierarchy constraints for the *target* phase must be satisfied.
3. **Role gate** — `proposal → ongoing` requires `admin` role.

### 4.2 Admin Bypass (Forward)

Administrators can skip intermediate phases with a mandatory justification. The bypass:
- Validates via `WorkflowService::validateBypass()`.
- Returns non-blocking **warnings** for all skipped requirements and structural rules.
- Logs the transition in `status_transition_logs` with `is_bypass = true`, `bypass_reason`, and warnings in `notes`.
- Optionally **cascades** the status to child entities (`cascade_children = true`) to maintain structural consistency.

### 4.3 Admin Bypass (Backward)

Administrators can also move entities **backward** to an earlier phase:
- Requires a documented reason (min 10 chars).
- Warns about child entities that remain at a higher status.
- Optionally cascades the backward move to children.
- The entity will need to re-advance through all skipped phases afterward.

### 4.4 Top-Down Cascade

When a parent entity is bypassed (forward or backward), the admin can opt to **cascade** the status change to all child entities:
- Program → cascades to all Projects → cascades to all Activities.
- Each cascaded child gets its own `StatusTransitionLog` entry with `bypass_reason = "Cascaded from parent ..."`.
- This prevents structural inconsistency (e.g., a completed program with draft projects).

---

## 5. Phase Definitions & Requirements Matrix

> **Important:** When advancing from Phase A → Phase B, the system validates requirements for the **target phase** (Phase B). All required fields and documents for Phase B must be met before entry is allowed.

### 5.1 Program Requirements

| Phase | Required Fields | Required Documents | Structural Rules |
|-------|----------------|-------------------|-----------------|
| **Draft** | Title, Campus | — | — |
| **Proposal** | Title, Proponent Name, Campus, Rationale, General Objective | Proposal Document | Must have ≥1 project |
| **Ongoing** | Target Start Date, Program Leader | — | All projects must be ≥ "Ongoing" |
| **Completed** | Target End Date | Terminal/Completion Report | All projects must be "Completed" |

**Non-blocking warnings** (shown but do not prevent advancement):
- No beneficiaries across any projects (at proposal)
- No program members assigned (at proposal)

### 5.2 Project Requirements

| Phase | Required Fields | Required Documents | Structural Rules |
|-------|----------------|-------------------|-----------------|
| **Draft** | Title, Campus | — | — |
| **Proposal** | Title, Description, Campus | Project Proposal Document | Must have ≥1 activity, ≥1 beneficiary |
| **Ongoing** | Target Start Date, Person(s) Responsible | — | All activities must be ≥ "Ongoing" |
| **Completed** | Target End Date | Completion Report | All activities must be "Completed" |

**Non-blocking warnings:**
- No budget items added (at proposal)

### 5.3 Activity Requirements

| Phase | Required Fields | Required Documents | Structural Rules |
|-------|----------------|-------------------|-----------------|
| **Draft** | Title | — | Must belong to a project |
| **Proposal** | Title, Description | — | Must belong to a project |
| **Ongoing** | Target Date, Person(s) Responsible | — | Must belong to a project |
| **Completed** | Completion Date | — | Must belong to a project |

---

## 6. Proposal Wizard

The system provides a multi-step **Proposal Submission Wizard** for programs and projects, guiding users through the `draft → proposal` transition.

### 6.1 Wizard Steps

| Step | Route | Description |
|------|-------|-------------|
| 1. Start | `GET /proposal/{type}/start` | Choose entity type, enter title and campus |
| 2. Upload | `GET /proposal/{type}/upload` | Upload required and supporting documents |
| 3. Details | `GET /proposal/{type}/details` | Fill in all proposal-phase fields (rationale, objectives, etc.) |
| 4. Projects | `GET /proposal/{type}/projects` | *(Program only)* Add or link projects under the program |
| 5. Confirmation | `GET /proposal/{type}/confirmation` | Review all entered data before submitting |
| 6. Submit | `POST /proposal/{type}/submit` | Validate and transition from `draft` → `proposal` |
| 7. Next Steps | `GET /proposal/{type}/next-steps` | Post-submission guidance |

### 6.2 Draft Persistence

- Users can **save a draft** at any wizard step via `POST /proposal/{type}/save-draft`.
- Saved drafts are persisted to the database (the entity is created with `status = 'draft'` and progress stored in `draft_data` JSON column).
- Users can **resume a draft** via `GET /proposal/{type}/continue/{id}`.
- Users can **delete a draft** via `DELETE /proposal/{type}/draft/{id}`.
- Users can **cancel** the wizard via `POST /proposal/{type}/cancel`.

### 6.3 Wizard Scope

The wizard handles only the `draft → proposal` transition. After submission, advancement to `ongoing` requires separate admin approval via the workflow advance/bypass endpoints.

---

## 7. Document Management System

### 7.1 Upload & Immediate Listing

Files are uploaded and **immediately** listed in the system. The user can:
1. Upload a file with a label.
2. **Edit the document type/label afterward** via `PATCH /workflow/document/{id}/type` (subject to label-rename guards).
3. View the file, replace it (creating a new version), or soft-delete it.

### 7.2 Document Updates

- To update a document, users can delete the existing one and upload a replacement.
- Document labels and types can be edited after upload via `PATCH /workflow/document/{id}/type`.

### 7.4 Document Deletion Guards

- **Ownership check**: Only the uploader or an admin can delete a document.
- **Phase check**: Documents on `completed` entities can only be deleted by administrators.
- **Requirement check**: Cannot delete a document that is the sole satisfier of a required-doc constraint.
- Enforced by `WorkflowService::canModifyDocument()`.

### 7.5 File Format Validation

| Phase | Allowed Formats | Max Size |
|-------|----------------|----------|
| Draft | PDF, DOC, DOCX, RTF, XLS, XLSX, JPG, JPEG, PNG, PPTX | 20 MB |
| Proposal | PDF, DOC, DOCX | 10 MB |
| Ongoing | PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG | 20 MB |
| Completed | PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, MP4, PPTX | 50 MB |

### 7.6 Document Type Taxonomy

Documents are categorized by an editable `document_type` field. Predefined types:

| Key | Display Name |
|-----|-------------|
| `proposal` | Proposal Document |
| `cover_letter` | Cover Letter |
| `proponent_bio` | Proponent Profile / Bio |
| `moa_mou` | MOA / MOU |
| `endorsement` | Endorsement Letter |
| `budget` | Budget Breakdown |
| `workplan` | Work Plan / Timeline |
| `completion` | Completion / Terminal Report |
| `monitoring` | Monitoring Report |
| `evaluation` | Evaluation Report |
| `attendance` | Attendance Sheet |
| `photo_doc` | Photo Documentation |
| `certificate` | Certificate |
| `data_set` | Data Set |
| `supporting` | Supporting Document |
| `other` | Other |

### 7.8 Suggested Labels per Phase

| Phase | Suggested Document Labels |
|-------|--------------------------|
| Draft | Draft Document, Supporting Document, Reference Material |
| Proposal | Proposal Document, Cover Letter, Proponent Bio, MOA/MOU, Endorsement Letter, Budget Breakdown, Work Plan, Data Set |
| Ongoing | Monitoring Report, Progress Report, Attendance Sheet, Photo Documentation, Financial Report |
| Completed | Terminal/Completion Report, Evaluation Report, Certificate, Photo Documentation, Financial Liquidation, Post-Activity Report |

### 7.9 Soft Deletion

Deleted documents are soft-deleted (`deleted_at` timestamp) to preserve the audit trail. The actual file remains on disk for compliance/audit purposes.

---

## 8. Beneficiary / Participant Management

### 8.1 Ownership Model

Each beneficiary record has a **primary owner** — the project it was created under (`extension_project_id`).

### 8.2 Counting & Reporting

| Field | Description |
|-------|-------------|
| `male_count` | Number of male participants |
| `female_count` | Number of female participants |
| `total_count` | Auto-computed: male + female |
| `type` | `individual`, `organization`, or `community` |
| `sector` | Farmer, Student, Youth, Women, Senior, Indigenous, PWD, Government, Private, Community, Other |

### 8.3 Dynamic Updates

- Beneficiaries can be added, edited, or removed at any phase.
- The number of beneficiaries per project is not fixed — it varies dynamically.

---

## 9. Budget & Member Management

### 9.1 Budget Items (`ExtensionBudgetItem`)

Budget items are line-item entries associated with a **Project** (`extension_project_id`).

| Field | Description |
|-------|-------------|
| `item` | Description of the budget line item |
| `total_budget` | Amount allocated |

- Budget items can be added, edited, or removed at any phase.
- The system warns (non-blocking) if a project has **no budget items** when advancing from proposal.
- Total budget spent is available via the `total_budget_spent` accessor on `ExtensionProject`.

### 9.2 Program Members (`ExtensionProgramMember`)

Program members represent team members assigned to a **Program** (`extension_program_id`).

| Field | Description |
|-------|-------------|
| `name` | Member's full name |
| `responsibility` | Role or responsibility within the program |

- Members can be added, edited, or removed at any phase.
- The system warns (non-blocking) if a program has **no members** when advancing from proposal.

---

## 10. Role-Based Access Control

### 10.1 Roles

| Role | Description |
|------|-------------|
| `admin` | Full system access. Can bypass workflow, manage users, manage all records. |
| `extension_staff` | Can create and manage their own programs, projects, activities. |

### 10.2 Ownership Definition

**"Own records"** means records where `created_by = auth()->id()`. Ownership is determined by the `created_by` foreign key on the entity, **not** by campus membership or program member status.

### 10.3 Permission Matrix

| Action | Admin | Extension Staff |
|--------|:-----:|:---------------:|
| Create program/project/activity | ✅ | ✅ |
| Edit own records | ✅ | ✅ |
| Edit others' records | ✅ | ❌ |
| Delete own records (draft/proposal only) | ✅ | ✅ |
| Delete others' records | ✅ | ❌ |
| Advance draft → proposal (own) | ✅ | ✅ |
| **Advance proposal → ongoing** | **✅** | **❌ (requires admin)** |
| Advance ongoing → completed (own) | ✅ | ✅ |
| Bypass workflow (forward or backward) | ✅ | ❌ |
| Cascade bypass to children | ✅ | ❌ |
| Upload documents (all phases) | ✅ | ✅ |
| Edit document type/label | ✅ | ✅ (own uploads) |
| Delete documents on completed entities | ✅ | ❌ |
| Manage users | ✅ | ❌ |

---

## 11. Business Rules Summary

### 11.1 Structural Integrity

| # | Rule | Enforcement Point |
|---|------|------------------|
| S1 | A program cannot advance past `proposal` without at least 1 project | `WorkflowService::checkStructuralRules()` |
| S2 | A project cannot advance past `proposal` without at least 1 activity | `WorkflowService::checkStructuralRules()` |
| S3 | A project cannot advance past `proposal` without at least 1 beneficiary | `WorkflowService::checkStructuralRules()` |
| S4 | An activity must always belong to a project | Database FK + model validation |
| S5 | Cannot delete the last project of a submitted program | `WorkflowService::canDelete()` |
| S6 | Cannot delete the last activity of a submitted project | `WorkflowService::canDelete()` |
| S7 | Cannot delete entities in `ongoing` or `completed` status | `WorkflowService::canDelete()` |

### 11.2 Workflow Integrity

| # | Rule | Enforcement Point |
|---|------|------------------|
| W1 | Status can only move forward one step at a time (draft→proposal→ongoing→completed) | `WorkflowService::validateStatusChange()` via `canAdvance()` |
| W2 | Skipping phases requires admin bypass with documented reason (min 10 chars) | `WorkflowController::bypass()` + `WorkflowService::validateBypass()` |
| W3 | Moving backward requires admin bypass with documented reason | `WorkflowController::bypass()` + `WorkflowService::validateBypass()` |
| W4 | All child entities must reach required status before parent can advance | `WorkflowService::checkStructuralRules()` (checked against target phase) |
| W5 | Advancing from Proposal → Ongoing requires admin role | `WorkflowService::canAdvance()` role gate |
| W6 | Target-phase requirements must be met before advancing | `WorkflowService::canAdvance()` |
| W7 | Every transition is logged with actor, from/to status, timestamp | `StatusTransitionLog` model |
| W8 | Admin bypass optionally cascades status to child entities | `WorkflowService::cascadeStatusToChildren()` |

### 11.3 Document Integrity

| # | Rule | Enforcement Point |
|---|------|------------------|
| D1 | Files are validated against phase-specific format and size limits | `WorkflowController::uploadDocument()` |
| D2 | Uploaded files are immediately listed (label editable afterward) | Controller + view layer |
| D3 | Deleted documents are soft-deleted for audit trail | `SoftDeletes` trait on `StatusDocument` |
| D4 | Document type/category can be changed after upload | `WorkflowController::updateDocumentType()` |
| D5 | Required-doc matching uses label OR document_type display name | `WorkflowService::getRequirementsStatus()` |
| D6 | Documents on completed entities can only be deleted by admins | `WorkflowService::canModifyDocument()` |
| D7 | Cannot delete a document that is the sole satisfier of a required-doc constraint | `WorkflowService::canModifyDocument()` |

### 11.4 Beneficiary Integrity

| # | Rule | Enforcement Point |
|---|------|------------------|
| B1 | Each beneficiary belongs to exactly one project | `extension_project_id` FK |
| B2 | Count of beneficiaries is not fixed — varies per project | No max constraint |
| B3 | Beneficiary data (type, sector, counts) can be updated dynamically | `ExtensionBeneficiaryController` |

---

## 12. Database Schema Reference

### 12.1 Core Tables

#### `extension_programs`
| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | Auto-increment |
| ic_no | VARCHAR(50) | Nullable |
| title | VARCHAR(255) | Required |
| proponent_name | VARCHAR(255) | Nullable (required at proposal) |
| status | ENUM | `draft`, `proposal`, `ongoing`, `completed` |
| draft_data | JSON | Nullable — stores wizard scratch data |
| campus_id | BIGINT FK | References `campuses` |
| created_by | BIGINT FK | References `users` |
| *(+ 20 more fields)* | | Funding, dates, objectives, etc. |

#### `extension_projects`
| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | Auto-increment |
| extension_program_id | BIGINT FK | Nullable (standalone if null) |
| title | VARCHAR(255) | Required |
| status | ENUM | `draft`, `proposal`, `ongoing`, `completed` |
| draft_data | JSON | Nullable |
| campus_id | BIGINT FK | References `campuses` |
| created_by | BIGINT FK | References `users` |

#### `extension_activities`
| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | Auto-increment |
| extension_project_id | BIGINT FK | Required — always under a project |
| title | VARCHAR(255) | Required |
| status | ENUM | `draft`, `proposal`, `ongoing`, `completed` |
| created_by | BIGINT FK | References `users` |
| *(campus inherited from parent project)* | | |

#### `extension_beneficiaries`
| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | Auto-increment |
| extension_project_id | BIGINT FK | Primary owning project |
| name | VARCHAR(255) | Required |
| type | ENUM | `individual`, `organization`, `community` |
| sector | VARCHAR(50) | Nullable |
| male_count | UNSIGNED INT | Default 0 |
| female_count | UNSIGNED INT | Default 0 |
| total_count | UNSIGNED INT | Auto-computed |

### 12.2 Document & Audit Tables

#### `status_documents`
| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | |
| documentable_type | VARCHAR | Polymorphic (Program/Project/Activity) |
| documentable_id | BIGINT | |
| phase | VARCHAR | Phase when uploaded |
| label | VARCHAR(255) | User-facing label |
| document_type | VARCHAR(100) | Editable category key |
| file_name | VARCHAR | Hashed storage name |
| file_path | VARCHAR | Relative path on disk |
| original_name | VARCHAR | Original upload name |
| mime_type | VARCHAR | MIME type |
| file_size | BIGINT | Bytes |
| uploaded_by | BIGINT FK | References `users` |
| deleted_at | TIMESTAMP | Soft-delete |

#### `status_transition_logs`
| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT PK | |
| transitionable_type | VARCHAR | Polymorphic |
| transitionable_id | BIGINT | |
| from_status | VARCHAR | |
| to_status | VARCHAR | |
| transitioned_by | BIGINT FK | References `users` |
| is_bypass | BOOLEAN | Default false |
| bypass_reason | TEXT | Required if bypass |
| notes | TEXT | Optional |

---

## 13. API / Route Reference

All routes require authentication (`auth` middleware).

### 13.1 Workflow Transitions

| Method | URI | Action | Description |
|--------|-----|--------|-------------|
| POST | `/workflow/{type}/{id}/advance` | `WorkflowController@advance` | Advance to next phase (validates both current + target requirements; proposal→ongoing requires admin) |
| POST | `/workflow/{type}/{id}/bypass` | `WorkflowController@bypass` | Admin bypass forward or backward (requires `bypass_reason` min 10 chars, optional `cascade_children` boolean) |

*`{type}` = `program`, `project`, or `activity`*

**Bypass request body:**
```json
{
  "target_phase": "ongoing",
  "bypass_reason": "Approved by the Dean after external review.",
  "cascade_children": true,
  "notes": "Optional additional notes."
}
```

### 13.2 Document Management

| Method | URI | Action | Description |
|--------|-----|--------|-------------|
| POST | `/workflow/{type}/{id}/upload-document` | `@uploadDocument` | Upload new document |
| PATCH | `/workflow/document/{document}/type` | `@updateDocumentType` | Edit label/type |
| DELETE | `/workflow/document/{document}` | `@deleteDocument` | Soft-delete document (guarded by phase + requirement checks) |

### 13.3 Proposal Wizard

| Method | URI | Action | Description |
|--------|-----|--------|-------------|
| GET | `/proposal/{type}/start` | `ProposalWizardController@start` | Step 1: Start wizard |
| POST | `/proposal/{type}/start` | `@saveStart` | Save step 1 data |
| GET | `/proposal/{type}/upload` | `@upload` | Step 2: Upload documents |
| POST | `/proposal/{type}/upload` | `@saveUpload` | Save uploaded files |
| GET | `/proposal/{type}/details` | `@details` | Step 3: Enter metadata |
| POST | `/proposal/{type}/details` | `@saveDetails` | Save metadata |
| GET | `/proposal/{type}/projects` | `@projects` | Step 4: Add projects (program only) |
| POST | `/proposal/{type}/projects` | `@saveProjects` | Save project assignments |
| GET | `/proposal/{type}/confirmation` | `@confirmation` | Review before submission |
| POST | `/proposal/{type}/submit` | `@submit` | Submit (draft → proposal) |
| GET | `/proposal/{type}/next-steps` | `@nextSteps` | Post-submission guidance |
| POST | `/proposal/{type}/save-draft` | `@saveDraft` | Persist draft from any step |
| GET | `/proposal/{type}/continue/{id}` | `@continueDraft` | Resume a saved draft |
| DELETE | `/proposal/{type}/draft/{id}` | `@deleteDraft` | Delete a saved draft |
| POST | `/proposal/{type}/cancel` | `@cancel` | Cancel the wizard |

*`{type}` = `program` or `project`*

### 13.4 CRUD Resources

| Resource | Route Prefix | Controller |
|----------|-------------|------------|
| Programs | `extension/programs` | `ExtensionProgramController` |
| Projects | `extension/projects` | `ExtensionProjectController` |
| Activities | `extension/activities` | `ExtensionActivityController` |
| Beneficiaries | `extension/beneficiaries` | `ExtensionBeneficiaryController` |

---

## Appendix A: Glossary

| Term | Definition |
|------|-----------|
| **Phase / Status** | One of four lifecycle stages: Draft, Proposal, Ongoing, Completed |
| **Advancement** | Moving a record forward to the next phase after meeting all requirements (current + target phase) |
| **Admin Approval Gate** | The `proposal → ongoing` transition requires an administrator; extension staff cannot self-approve |
| **Bypass (Forward)** | Admin override that skips intermediate phases with a documented reason; warnings logged for skipped requirements |
| **Bypass (Backward)** | Admin override that moves a record to an earlier phase with a documented reason |
| **Top-Down Cascade** | Optionally propagating a parent's bypass to all child entities for structural consistency |
| **Structural Rule** | A constraint based on the hierarchy (e.g., program must have projects); checked against the **target** phase |
| **Document Label Guard** | Prevents deleting a document that is the sole satisfier of a required-doc constraint |
| **Soft Delete** | Marking a record as deleted without physically removing it from the database |
| **Ownership** | Determined by `created_by` field — the user who created the record |
| **Proposal Wizard** | Multi-step guided flow for transitioning from draft to proposal status |

---

*End of Specification*
