# License Management System

## System Architecture

**Organizational Hierarchy:**
`City → Department → Division → Employee`

**Access Control:**
- **Admin** — Full system access, multi-city management
- **Manager** — City-scoped access, limited to assigned city

---

## Workflow

### 1. Initial Setup (Admin)
```
Cities → Departments → Divisions → Employees → Vendors → Users
```
- Assign manager to city (for city-scoped access)
- Assign city to departments
- Assign department to divisions
- Assign division to employees
- Configure manager permissions (`can_create_license`)

### 2. License Creation
| Creator | Permission | Result |
|---------|-----------|--------|
| Admin | N/A | Approved |
| Manager | `can_create_license = true` | Approved |
| Manager | `can_create_license = false` | Pending |

### 3. Approval Process (Admin)
- Review: `/admin/licenses/pending`
- **Approve** → License activated
- **Reject** → License denied (reason logged)

### 4. License Assignment
Assign approved licenses → Track status → Monitor renewals

---

## Core Features
- **RBAC** — Role-based permissions (Admin/Manager)
- **City Scoping** — Data isolation by city
- **Approval Workflow** — Configurable license creation rights
- **Audit Trail** — Track creator, approver, timestamps
- **Status Management** — Pending/Approved/Rejected states
