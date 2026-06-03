<style>
body {
    background: #eef4fb;
}

.people-page {
    padding: 22px;
    min-height: 100vh;
    font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

/* HEADER */
.page-title-card {
    background: #ffffff;
    border-radius: 8px;
    padding: 18px 22px;
    margin-bottom: 16px;
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.12);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.page-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    background: #e8f1ff;
    color: #2f76dd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.people-title {
    font-size: 21px;
    font-weight: 700;
    color: #111827;
}

.people-subtitle {
    font-size: 13px;
    color: #6b7280;
}

.btn-add-member {
    background: #2f76dd;
    color: #ffffff;
    border-radius: 7px;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 5px 12px rgba(47, 118, 221, 0.25);
}

.btn-add-member:hover {
    color: #ffffff;
    background: #2563c7;
}

/* PANEL */
.table-panel {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.12);
    overflow: hidden;
}

.table-panel-header {
    padding: 18px 20px;
    background: #ffffff;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.policy-title {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
}

.policy-subtitle {
    font-size: 12px;
    color: #6b7280;
}

.toolbar-actions {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.search-box {
    width: 280px;
    height: 32px;
    border: 1px solid #d6e1ef;
    border-radius: 5px;
    display: flex;
    align-items: center;
    padding: 0 10px;
    background: #ffffff;
}

.search-box i {
    font-size: 12px;
    color: #53657d;
    margin-right: 7px;
}

.search-box input {
    border: none;
    outline: none;
    width: 100%;
    font-size: 12px;
    color: #111827;
    background: transparent;
}

.search-box input::placeholder {
    color: #a8b5c7;
}

.tool-btn {
    height: 32px;
    border: 1px solid #d6e1ef;
    background: #ffffff;
    color: #374151;
    border-radius: 5px;
    padding: 0 11px;
    font-size: 12px;
    font-weight: 600;
}

/* TABS */
.table-panel-tabs {
    padding: 0 20px 12px 20px;
    border-bottom: 1px solid #edf0f4;
}

.people-tabs {
    display: inline-flex;
    background: #f3f6fa;
    border-radius: 7px;
    padding: 4px;
    gap: 3px;
}

.people-tab {
    border: none;
    background: transparent;
    color: #6b7280;
    font-size: 12px;
    font-weight: 600;
    padding: 7px 25px;
    border-radius: 5px;
    transition: all 0.2s ease;
}

.people-tab span {
    margin-left: 5px;
    font-size: 11px;
}

.people-tab.active {
    background: #ffffff;
    color: #111827;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.08);
}

/* TABLE */
.table-panel-body {
    background: #ffffff;
}

.people-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}

.people-table thead th {
    background: #eaf0f7;
    color: #53657d;
    font-size: 12px;
    font-weight: 700;
    padding: 12px 16px;
    border-bottom: 1px solid #e3eaf3;
    white-space: nowrap;
    text-transform: uppercase;
}

.people-table tbody td {
    padding: 15px 16px;
    font-size: 13px;
    color: #53657d;
    vertical-align: middle;
    border-bottom: 1px solid #edf0f4;
    white-space: nowrap;
}

.people-table tbody tr:hover {
    background: #f9fbff;
}

/* STATUS */
.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}

.status-pending {
    color: #92400e;
    background: #fef3c7;
}

.status-approved {
    color: #166534;
    background: #dcfce7;
}

.status-rejected {
    color: #991b1b;
    background: #fee2e2;
}

.status-progress {
    color: #075985;
    background: #e0f2fe;
}

.status-completed {
    color: #166534;
    background: #dcfce7;
}

/* ACTION BUTTONS */
.action-group {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
}

.action-group form {
    margin: 0;
}

.btn-action {
    width: 33px;
    height: 31px;
    border-radius: 5px;
    border: none;
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    text-decoration: none;
    cursor: pointer;
}

.btn-action:hover {
    color: #ffffff;
}

.btn-action-view {
    background: #1da7f2;
}

.btn-action-view:hover {
    background: #0d8ed3;
}

.btn-action-approve {
    background: #22c55e;
}

.btn-action-approve:hover {
    background: #16a34a;
}

.btn-action-reject {
    background: #ef4444;
}

.btn-action-reject:hover {
    background: #dc2626;
}

.btn-action-delete {
    background: #111827;
}

.btn-action-delete:hover {
    background: #000000;
}

/* EMPTY STATE */
.empty-state {
    min-height: 190px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    font-size: 13px;
}

.empty-state i {
    font-size: 34px;
    color: #d7e2f0;
    margin-bottom: 10px;
}

.empty-state-title {
    font-size: 13px;
    font-weight: 700;
    color: #111827;
}

.empty-state-subtitle {
    font-size: 12px;
    color: #6b7280;
}

/* PAGINATION */
.pagination-wrapper {
    padding: 14px 20px;
    display: flex;
    justify-content: flex-end;
    background: #f9fbff;
    border-top: 1px solid #edf0f4;
}

.pagination {
    margin-bottom: 0;
    font-size: 12px;
}

.pagination .page-link {
    padding: 4px 9px;
    font-size: 12px;
    border-radius: 6px;
    color: #374151;
    border: 1px solid #e5e7eb;
}

.pagination .page-item.active .page-link {
    background: #2f76dd;
    border-color: #2f76dd;
    color: #ffffff;
}
.ticket-pagination-footer {
    min-height: 64px;
    padding: 16px 20px;
    background: #ffffff;
    border-top: 1px solid #e5edf5;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.ticket-pagination-info {
    font-size: 12px;
    color: #6b7280;
    white-space: nowrap;
}

.ticket-pagination-links {
    display: flex;
    justify-content: flex-end;
}

.ticket-pagination-links .pagination {
    margin-bottom: 0;
    gap: 4px;
}

.ticket-pagination-links .page-link {
    min-width: 32px;
    height: 32px;
    padding: 0 10px;
    border-radius: 8px !important;
    border: 1px solid #dbe5f1;
    color: #2563eb;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ticket-pagination-links .page-item.active .page-link {
    background: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
}

.ticket-pagination-links .page-item.disabled .page-link {
    color: #9ca3af;
    background: #f8fafc;
}

@media (max-width: 768px) {
    .ticket-pagination-footer {
        flex-direction: column;
        align-items: flex-start;
    }

    .ticket-pagination-links {
        width: 100%;
        overflow-x: auto;
        justify-content: flex-start;
        padding-bottom: 4px;
    }
}

/* RESPONSIVE */
@media (max-width: 992px) {
    .page-title-card,
    .table-panel-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .toolbar-actions {
        width: 100%;
        justify-content: flex-start;
    }

    .search-box {
        width: 100%;
    }

    .people-tab {
        padding: 7px 14px;
    }
}
</style>
