<style>
    /* ===== Profile Header ===== */
    .profile-header-card .profile-hero {
        height: 120px;
        background: linear-gradient(135deg, #0d6efd 0%, #6f42c1 100%);
    }

    .profile-avatar {
        width: 84px;
        height: 84px;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .12);
    }

    .small-muted {
        font-size: .86rem;
        color: #6c757d;
    }

    .mono-icon {
        width: 16px;
        text-align: center;
    }

    /* ===== QR Box ===== */
    .qr-card {
        min-width: 150px;
        text-align: center;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
    }

    .qr-box {
        display: inline-flex;
        padding: 8px;
        border-radius: 10px;
        border: 1px dashed #d1d5db;
        background: #fff;
        margin-bottom: 6px;
    }

    .qr-id-label {
        font-size: 12px;
        color: #6b7280;
        line-height: 1.2;
    }

    .qr-id-value {
        font-weight: 700;
        font-size: 14px;
        letter-spacing: .5px;
    }

    .qr-empty {
        font-size: 13px;
        color: #6b7280;
        padding: 14px 0;
    }

    /* ===== Employment History Timeline ===== */
    .eh-timeline {
        position: relative;
    }

    .eh-item {
        display: flex;
        gap: 12px;
        padding: 10px 0;
    }

    .eh-left {
        position: relative;
        width: 18px;
        flex: 0 0 18px;
    }

    .eh-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #9ca3af;
        position: absolute;
        top: 6px;
        left: 4px;
    }

    .eh-dot.is-present {
        background: #16a34a;
    }

    .eh-line {
        position: absolute;
        top: 18px;
        left: 8px;
        width: 2px;
        height: calc(100% - 18px);
        background: #e5e7eb;
    }

    .eh-content {
        width: 100%;
    }

    .eh-top {
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }

    .eh-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 3px;
        font-size: 12px;
        color: #6b7280;
    }

    .eh-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 2px 10px;
        border-radius: 999px;
        background: #f3f4f6;
        color: #374151;
        font-size: 12px;
    }

    .eh-pill-success {
        background: #dcfce7;
        color: #166534;
    }

    .eh-desc {
        margin-top: 6px;
        font-size: 13px;
        color: #374151;
    }

    /* ===== Attachments sidebar ===== */
    .attachment-row a {
        text-decoration: none;
    }

    .attachment-row a:hover {
        text-decoration: underline;
    }

    /* ===== Logs ===== */
    .badge-subtle-success { background: #dcfce7; color: #166534; }
    .badge-subtle-warning { background: #fef3c7; color: #92400e; }
    .badge-subtle-primary { background: #dbeafe; color: #1e40af; }
    .badge-subtle-info    { background: #cffafe; color: #155e75; }
    .badge-subtle-danger  { background: #fee2e2; color: #991b1b; }
    .badge-subtle-secondary { background: #f3f4f6; color: #374151; }
</style>