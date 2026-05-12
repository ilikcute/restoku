# System Workflows

This document outlines the core workflows and data processing pipelines within Restoku.

## 1. Transaction & Order Processing

When a cashier processes a transaction in the POS:

```mermaid
sequenceDiagram
    participant UI as Vue Frontend
    participant API as Laravel Controller
    participant FR as Form Request
    participant SVC as OrderService
    participant REPO as OrderRepository
    participant DB as Database

    UI->>API: POST /api/v1/orders
    API->>FR: Validate input
    FR-->>API: Validated data
    API->>SVC: createOrder(data)
    SVC->>SVC: Calculate subtotal, tax, service charge
    SVC->>REPO: create(orderData)
    REPO->>DB: INSERT into orders
    SVC->>REPO: createItems(itemData)
    REPO->>DB: INSERT into order_items
    SVC->>REPO: updateStock(items)
    REPO->>DB: UPDATE products (stock)
    SVC-->>API: Order Object
    API-->>UI: 201 Created (Success)
```

## 2. DPKAD External Sync

Synchronization with the Regional Financial and Asset Management Office (DPKAD) database:

```mermaid
flowchart TD
    Start[Trigger Sync] --> GetLocal[Fetch Pending Local Transactions]
    GetLocal --> CheckConn{Test DPKAD Connection}
    CheckConn -- Fail --> Error[Log Connection Error]
    CheckConn -- Success --> Loop[Iterate Transactions]
    Loop --> Map[Map to DPKAD Schema]
    Map --> Push[INSERT into DPKAD DB]
    Push --> Mark[Mark as Synced Locally]
    Mark --> Next{Has More?}
    Next -- Yes --> Loop
    Next -- No --> Finish[End Sync]
```

## 3. Reporting & Exporting

How PDF and Excel reports are generated:

- **PDF**: Uses `DomPDF` with custom Blade templates (`resources/views/exports/reports/`).
- **Excel**: Uses `PhpSpreadsheet` via `ReportExportService`.

| Workflow | Service | Template/Format |
|----------|---------|-----------------|
| Sales Summary | `ReportExportService` | `summary.pdf` |
| Tax Report | `ReportExportService` | `tax_report.xlsx` |
| Shift Recap | `ShiftService` | `shift_report.pdf` |

## 4. Multi-Tenant Onboarding

The process of registering a new business (Tenant):

1. **User Registration**: `AuthController` receives request.
2. **Atomic Transaction**: `AuthService` wraps everything in a `DB::transaction()`.
3. **Tenant Creation**: `AuthRepository` creates a `Tenant` record.
4. **User Creation**: `AuthRepository` creates a `User` linked to the `Tenant`.
5. **Role Assignment**: User is assigned the `Admin` role via Spatie Permissions.
6. **Initialization**: (Optional) Default categories or settings are seeded.
