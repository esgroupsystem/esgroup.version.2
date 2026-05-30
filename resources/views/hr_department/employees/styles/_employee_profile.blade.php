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
    .badge-subtle-success {
        background: #dcfce7;
        color: #166534;
    }

    .badge-subtle-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-subtle-primary {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-subtle-info {
        background: #cffafe;
        color: #155e75;
    }

    .badge-subtle-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-subtle-secondary {
        background: #f3f4f6;
        color: #374151;
    }


    :root {
        --vh-accent: #e63757;
        --vh-accent-warm: #f5803e;
        --vh-accent-blue: #2c7be5;
        --vh-accent-green: #00d27a;
        --vh-surface: #ffffff;
        --vh-surface-alt: #f9fbfd;
        --vh-border: #e3e6ea;
        --vh-text: #344050;
        --vh-muted: #748194;
        --vh-timeline-w: 3px;
        --vh-dot-size: 12px;
        --vh-radius: 0.6rem;
        --vh-transition: 0.2s ease;
    }

    /* ── Card shell ── */
    .vh-card {
        border: 1px solid var(--vh-border);
        border-radius: var(--vh-radius);
        background: var(--vh-surface);
        box-shadow: 0 2px 12px rgba(52, 64, 80, .07);
        overflow: hidden;
    }

    .vh-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .85rem 1.25rem;
        background: var(--vh-surface-alt);
        border-bottom: 1px solid var(--vh-border);
        gap: .5rem;
    }

    .vh-card-title {
        font-size: .875rem;
        font-weight: 700;
        color: var(--vh-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: .45rem;
    }

    .vh-card-title .icon-wrap {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        background: rgba(231, 55, 87, .1);
        display: grid;
        place-items: center;
        color: var(--vh-accent);
        font-size: .75rem;
        flex-shrink: 0;
    }

    /* ── Stats strip ── */
    .vh-stats {
        display: flex;
        gap: 0;
        border-bottom: 1px solid var(--vh-border);
        background: var(--vh-surface-alt);
    }

    .vh-stat-item {
        flex: 1;
        padding: .7rem 1rem;
        text-align: center;
        border-right: 1px solid var(--vh-border);
        position: relative;
    }

    .vh-stat-item:last-child {
        border-right: none;
    }

    .vh-stat-value {
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1;
        color: var(--vh-text);
    }

    .vh-stat-value.danger {
        color: var(--vh-accent);
    }

    .vh-stat-value.warning {
        color: var(--vh-accent-warm);
    }

    .vh-stat-value.primary {
        color: var(--vh-accent-blue);
    }

    .vh-stat-label {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--vh-muted);
        margin-top: .2rem;
    }

    /* ── Timeline ── */
    .vh-timeline {
        padding: 1.25rem 1.25rem 0.5rem;
        position: relative;
    }

    .vh-timeline::before {
        content: '';
        position: absolute;
        left: calc(1.25rem + 5px);
        top: 0;
        bottom: 0;
        width: var(--vh-timeline-w);
        background: linear-gradient(to bottom,
                var(--vh-border) 0%,
                rgba(227, 230, 234, 0) 100%);
        border-radius: 99px;
    }

    /* ── Single IR item ── */
    .vh-item {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        position: relative;
        animation: vh-fadeUp .35s ease both;
    }

    .vh-item:last-child {
        margin-bottom: 0;
    }

    @keyframes vh-fadeUp {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .vh-item:nth-child(1) {
        animation-delay: .05s;
    }

    .vh-item:nth-child(2) {
        animation-delay: .10s;
    }

    .vh-item:nth-child(3) {
        animation-delay: .15s;
    }

    .vh-item:nth-child(4) {
        animation-delay: .20s;
    }

    .vh-item:nth-child(5) {
        animation-delay: .25s;
    }

    /* ── Left rail (dot) ── */
    .vh-rail {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex-shrink: 0;
        padding-top: .35rem;
        width: 13px;
        position: relative;
        z-index: 1;
    }

    .vh-dot {
        width: var(--vh-dot-size);
        height: var(--vh-dot-size);
        border-radius: 50%;
        background: var(--vh-surface);
        border: 2px solid var(--vh-border);
        flex-shrink: 0;
        transition: border-color var(--vh-transition),
            box-shadow var(--vh-transition);
    }

    .vh-dot.has-suspension {
        border-color: var(--vh-accent);
        background: rgba(231, 55, 87, .12);
    }

    .vh-dot.has-sda {
        border-color: var(--vh-accent-warm);
        background: rgba(245, 128, 62, .12);
    }

    .vh-dot.plain {
        border-color: var(--vh-accent-blue);
        background: rgba(44, 123, 229, .12);
    }

    /* ── Content bubble ── */
    .vh-bubble {
        flex: 1;
        border: 1px solid var(--vh-border);
        border-radius: var(--vh-radius);
        background: var(--vh-surface);
        overflow: hidden;
        transition: border-color var(--vh-transition),
            box-shadow var(--vh-transition);
        cursor: default;
    }

    .vh-bubble:hover {
        border-color: #c6ccd6;
        box-shadow: 0 4px 16px rgba(52, 64, 80, .09);
    }

    .vh-bubble-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .75rem;
        padding: .7rem 1rem;
        background: var(--vh-surface-alt);
        border-bottom: 1px solid var(--vh-border);
    }

    .vh-ir-number {
        font-size: .875rem;
        font-weight: 700;
        color: var(--vh-accent-blue);
        white-space: nowrap;
    }

    .vh-badges {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        align-items: center;
    }

    .vh-count {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        font-size: .7rem;
        font-weight: 700;
        padding: .15rem .5rem;
        border-radius: 99px;
        background: #edf2ff;
        color: var(--vh-accent-blue);
        border: 1px solid #c5d6f9;
    }

    .vh-badge-sda {
        font-size: .67rem;
        font-weight: 700;
        padding: .15rem .5rem;
        border-radius: 4px;
        background: rgba(245, 128, 62, .15);
        color: #c95b05;
        border: 1px solid rgba(245, 128, 62, .35);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .vh-badge-sus {
        font-size: .67rem;
        font-weight: 700;
        padding: .15rem .5rem;
        border-radius: 4px;
        background: rgba(231, 55, 87, .12);
        color: var(--vh-accent);
        border: 1px solid rgba(231, 55, 87, .3);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .vh-bubble-body {
        padding: .65rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .vh-meta {
        font-size: .75rem;
        color: var(--vh-muted);
        display: flex;
        align-items: center;
        gap: .35rem;
    }

    .vh-actions {
        display: flex;
        gap: .35rem;
        flex-shrink: 0;
    }

    .vh-btn-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        border: 1px solid var(--vh-border);
        background: var(--vh-surface);
        display: grid;
        place-items: center;
        font-size: .75rem;
        cursor: pointer;
        transition: background var(--vh-transition),
            border-color var(--vh-transition),
            color var(--vh-transition);
        color: var(--vh-muted);
        text-decoration: none;
    }

    .vh-btn-icon:hover {
        background: #eef1f6;
        color: var(--vh-text);
    }

    .vh-btn-icon.view:hover {
        color: var(--vh-accent-blue);
        border-color: var(--vh-accent-blue);
    }

    .vh-btn-icon.trash:hover {
        color: var(--vh-accent);
        border-color: var(--vh-accent);
    }

    /* ── Empty state ── */
    .vh-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--vh-muted);
    }

    .vh-empty .vh-empty-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto .75rem;
        border-radius: 50%;
        background: #f0f3f6;
        display: grid;
        place-items: center;
        font-size: 1.2rem;
        color: #b5bec9;
    }

    /* ── Modal upgrades ── */
    .vh-modal .modal-header {
        background: var(--vh-surface-alt);
        border-bottom: 1px solid var(--vh-border);
        padding: 1rem 1.25rem;
    }

    .vh-modal .modal-title {
        font-size: .95rem;
        font-weight: 700;
        color: var(--vh-text);
    }

    .vh-modal .modal-body {
        padding: 1.25rem;
    }

    .vh-modal-summary {
        display: flex;
        gap: .75rem;
        flex-wrap: wrap;
        align-items: center;
        background: var(--vh-surface-alt);
        border: 1px solid var(--vh-border);
        border-radius: var(--vh-radius);
        padding: .75rem 1rem;
        margin-bottom: 1rem;
    }

    .vh-modal-summary .label {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--vh-muted);
    }

    .vh-modal-summary .value {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--vh-text);
        line-height: 1;
    }

    .vh-offense-card {
        border: 1px solid var(--vh-border);
        border-radius: var(--vh-radius);
        overflow: hidden;
        margin-bottom: .75rem;
        transition: border-color var(--vh-transition);
    }

    .vh-offense-card:hover {
        border-color: #c6ccd6;
    }

    .vh-offense-card:last-child {
        margin-bottom: 0;
    }

    .vh-offense-head {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .55rem .85rem;
        background: var(--vh-surface-alt);
        border-bottom: 1px solid var(--vh-border);
    }

    .vh-section-tag {
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: .15rem .5rem;
        border-radius: 4px;
        background: rgba(44, 123, 229, .1);
        color: var(--vh-accent-blue);
        border: 1px solid rgba(44, 123, 229, .2);
    }

    .vh-offense-desc {
        padding: .65rem .85rem;
        font-size: .8rem;
        color: var(--vh-muted);
        line-height: 1.6;
    }

    .vh-detail-box {
        border-radius: var(--vh-radius);
        padding: .85rem 1rem;
        margin-top: .75rem;
    }

    .vh-detail-box.sda {
        background: rgba(245, 128, 62, .07);
        border: 1px solid rgba(245, 128, 62, .28);
    }

    .vh-detail-box.sus {
        background: rgba(231, 55, 87, .07);
        border: 1px solid rgba(231, 55, 87, .28);
    }

    .vh-detail-box .box-title {
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: .55rem;
    }

    .vh-detail-box.sda .box-title {
        color: #c95b05;
    }

    .vh-detail-box.sus .box-title {
        color: var(--vh-accent);
    }

    .vh-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .4rem;
    }

    .vh-detail-row .dkey {
        color: var(--vh-muted);
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .1rem;
    }

    .vh-detail-row .dval {
        font-weight: 700;
        color: var(--vh-text);
        font-size: .78rem;
    }
</style>
