<style>
    .employee-biometrics-page {
        --bio-primary: #2c7be5;
        --bio-primary-dark: #1b63c7;
        --bio-success: #00d27a;
        --bio-warning: #f5803e;
        --bio-info: #27bcfd;
        --bio-secondary: #748194;
        --bio-border: #d8e2ef;
        --bio-soft-bg: #f8fbff;
    }

    .employee-biometrics-page .min-w-0 {
        min-width: 0;
    }

    .bio-header-card {
        border-radius: .9rem;
    }

    .bio-header {
        background: linear-gradient(135deg, var(--bio-primary), var(--bio-primary-dark));
    }

    .bio-header-main {
        padding: 2rem;
    }

    .bio-header-description {
        color: rgba(255, 255, 255, .82);
        max-width: 780px;
    }

    .bio-breadcrumb .breadcrumb-item,
    .bio-breadcrumb .breadcrumb-item a,
    .bio-breadcrumb .breadcrumb-item.active {
        color: rgba(255, 255, 255, .86);
    }

    .bio-breadcrumb .breadcrumb-item+.breadcrumb-item::before {
        color: rgba(255, 255, 255, .55);
    }

    .bio-icon-circle {
        width: 2.35rem;
        height: 2.35rem;
        min-width: 2.35rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        line-height: 1;
    }

    .bio-icon-white {
        background: #ffffff;
        color: var(--bio-primary);
        box-shadow: 0 .25rem .75rem rgba(18, 38, 63, .16);
    }

    .bio-icon-primary {
        background: #e6f1ff;
        color: var(--bio-primary);
    }

    .bio-icon-success {
        background: #d9f8eb;
        color: var(--bio-success);
    }

    .bio-icon-warning {
        background: #fff0e8;
        color: var(--bio-warning);
    }

    .bio-icon-secondary {
        background: #edf2f9;
        color: var(--bio-secondary);
    }

    .bio-header-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border-radius: 999px;
        background: #fff;
        color: var(--bio-primary);
        font-weight: 700;
        font-size: .75rem;
        padding: .4rem .8rem;
        box-shadow: 0 .15rem .45rem rgba(18, 38, 63, .08);
    }

    .bio-header-pill-success {
        background: #d9f8eb;
        color: #00864e;
    }

    .bio-header-pill-warning {
        background: #fff0e8;
        color: #c46632;
    }

    .bio-header-pill-info {
        background: #e5f7ff;
        color: #1978a8;
    }

    .bio-sync-panel-wrap {
        height: 100%;
        padding: 1.25rem;
        background: rgba(18, 38, 63, .12);
    }

    .bio-sync-panel {
        height: 100%;
        background: rgba(255, 255, 255, .96);
        border-radius: .75rem;
        padding: 1.25rem;
        box-shadow: 0 .5rem 1rem rgba(18, 38, 63, .12);
    }

    .bio-metric-row {
        background: #fff;
        border-top: 1px solid rgba(216, 226, 239, .85);
    }

    .bio-metric-card {
        height: 100%;
        padding: 1.15rem 1.25rem;
        border-right: 1px solid var(--bio-border);
    }

    .bio-metric-row>div:last-child .bio-metric-card {
        border-right: 0;
    }

    .bio-metric-label {
        font-size: .66rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-bottom: .25rem;
    }

    .bio-metric-value {
        color: #12263f;
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1;
    }

    .bio-metric-caption {
        color: #748194;
        font-size: .72rem;
    }

    .bio-progress {
        height: .35rem;
        background: #edf2f9;
    }

    .bio-section-icon {
        width: 1.85rem;
        height: 1.85rem;
        min-width: 1.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .45rem;
        background: #e6f1ff;
        color: var(--bio-primary);
    }

    .bio-company-panel {
        background: var(--bio-soft-bg);
        border: 1px dashed var(--bio-border);
        border-radius: .75rem;
        padding: 1rem;
    }

    .bio-table-wrapper {
        max-height: none;
        overflow: visible;
    }

    .bio-table {
        min-width: 1280px;
        font-size: .76rem;
    }

    .bio-table thead {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #edf2f9;
    }

    .bio-table thead th {
        color: #344050;
        border-bottom: 1px solid var(--bio-border);
        font-size: .66rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        padding-top: .75rem;
        padding-bottom: .75rem;
        white-space: nowrap;
    }

    .bio-table tbody td {
        border-bottom: 1px solid #edf2f9;
        padding-top: .72rem;
        padding-bottom: .72rem;
    }

    .bio-row-excluded {
        background: #f9fbfd;
        opacity: .82;
    }

    .bio-row-excluded .bio-sticky-col {
        background: #f9fbfd !important;
    }

    .bio-employee-avatar-muted {
        background: #edf2f9;
        color: #748194;
    }

    .bio-table tbody tr:hover {
        background: rgba(44, 123, 229, .035);
    }

    .bio-table tbody tr:hover .bio-sticky-col {
        background: #f8fbff !important;
    }

    .bio-sticky-col {
        position: sticky;
        left: 0;
        z-index: 3;
        box-shadow: 1px 0 0 #edf2f9;
    }

    .bio-table thead .bio-sticky-col {
        z-index: 6;
        background: #edf2f9;
    }

    .bio-employee-col {
        min-width: 330px;
        max-width: 350px;
    }

    .bio-source-col {
        min-width: 430px;
        max-width: 520px;
    }

    .bio-device-col {
        min-width: 170px;
        max-width: 220px;
    }

    .bio-employee-cell {
        display: flex;
        align-items: center;
        gap: .85rem;
    }

    .bio-employee-avatar {
        width: 2.35rem;
        height: 2.35rem;
        min-width: 2.35rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e6f1ff;
        color: var(--bio-primary);
        font-weight: 800;
        font-size: .72rem;
        letter-spacing: .03em;
    }

    .bio-employee-name {
        max-width: 240px;
    }

    .bio-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: .28rem;
        background: #f5f8fc;
        color: #5e6e82;
        border: 1px solid #edf2f9;
        border-radius: 999px;
        padding: .14rem .45rem;
        font-size: .65rem;
        line-height: 1.2;
    }

    .bio-source-card {
        background: #f9fbfe;
        border-left: 3px solid rgba(44, 123, 229, .45);
        border-radius: .45rem;
        padding: .55rem .7rem;
    }

    .bio-source-line {
        display: grid;
        grid-template-columns: 86px minmax(0, 1fr);
        gap: .6rem;
        margin-bottom: .28rem;
    }

    .bio-source-line:last-child {
        margin-bottom: 0;
    }

    .bio-source-line span {
        color: #748194;
        font-size: .65rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .bio-source-line strong {
        color: #344050;
        font-size: .72rem;
        font-weight: 700;
        min-width: 0;
    }

    .bio-logs-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.35rem;
        padding: .2rem .6rem;
        border-radius: 999px;
        background: #e6f1ff;
        color: var(--bio-primary);
        border: 1px solid #cfe3ff;
        font-size: .68rem;
        font-weight: 800;
    }

    .bio-info-card {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        height: 100%;
        background: #fff;
        border-radius: .75rem;
        box-shadow: 0 .125rem .35rem rgba(18, 38, 63, .08);
        padding: 1rem;
    }

    .bio-empty-state {
        max-width: 420px;
        margin: 0 auto;
    }

    .bio-empty-icon {
        width: 4rem;
        height: 4rem;
        margin: 0 auto 1rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e6f1ff;
        color: var(--bio-primary);
        font-size: 1.5rem;
    }

    .bio-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        min-width: 82px;
        border-radius: 999px;
        padding: .35rem .75rem;
        font-size: .68rem;
        font-weight: 800;
        line-height: 1;
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: nowrap;
    }

    .bio-status-active {
        background: #d9f8eb;
        color: #00864e;
        border: 1px solid #9be7c2;
    }

    .bio-status-inactive {
        background: #edf2f9;
        color: #5e6e82;
        border: 1px solid #d8e2ef;
    }

    .bio-status-badge .fas {
        font-size: .65rem;
    }

    .bio-filter-form {
        width: 100%;
    }

    .bio-filter-field {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .bio-filter-label {
        display: flex;
        align-items: center;
        min-height: 1.25rem;
        margin-bottom: .5rem;
        line-height: 1.25;
    }

    .bio-filter-control.form-control,
    .bio-filter-control.form-select,
    .bio-filter-control .form-control,
    .bio-filter-control .input-group-text {
        min-height: 2.5rem;
    }

    .bio-filter-control .input-group-text {
        display: flex;
        align-items: center;
        justify-content: center;
        padding-right: .75rem;
        padding-left: .75rem;
    }

    .bio-filter-control .form-control {
        display: flex;
        align-items: center;
    }

    .bio-filter-help {
        min-height: 2rem;
        margin-top: .4rem;
        line-height: 1.35;
    }

    .bio-filter-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: .5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--bio-border);
    }

    .bio-filter-actions .btn {
        min-width: 110px;
    }

    @media (max-width: 1199.98px) {
        .bio-metric-card {
            border-bottom: 1px solid var(--bio-border);
        }

        .bio-metric-row>div:nth-child(2n) .bio-metric-card {
            border-right: 0;
        }

        .bio-sync-panel-wrap {
            padding-top: 0;
        }
    }

    @media (max-width: 575.98px) {
        .bio-header-main {
            padding: 1.25rem;
        }

        .bio-metric-card {
            border-right: 0;
        }
    }

    .employee-biometric-edit-page {
        --eb-primary: var(--falcon-primary, #2c7be5);
        --eb-primary-rgb: 44, 123, 229;
        --eb-info: #27bcfd;
        --eb-success: #00a86b;
        --eb-warning: #f6c343;
        --eb-danger: #e63757;
        --eb-secondary: #748194;
        --eb-heading: var(--falcon-headings-color, #344050);
        --eb-body: var(--falcon-body-color, #5e6e82);
        --eb-muted: var(--falcon-gray-600, #748194);
        --eb-border: var(--falcon-border-color, #d8e2ef);
        --eb-soft-bg: var(--falcon-gray-100, #f9fafd);
        --eb-card-bg: var(--falcon-card-bg, #fff);
    }

    .employee-biometric-edit-page,
    .employee-biometric-edit-page * {
        box-sizing: border-box;
    }

    .employee-biometric-edit-page .min-w-0 {
        min-width: 0;
    }

    .employee-biometric-edit-page .page-header-card,
    .employee-biometric-edit-page .app-card {
        border: 1px solid var(--eb-border);
        border-radius: .75rem;
        background: var(--eb-card-bg);
        box-shadow: 0 .25rem .75rem rgba(18, 38, 63, .06);
        overflow: hidden;
    }

    .employee-biometric-edit-page .page-header-card {
        border-top: 3px solid var(--eb-primary);
    }

    .employee-biometric-edit-page .breadcrumb-sm {
        font-size: .8125rem;
    }

    .employee-biometric-edit-page .breadcrumb-sm a {
        text-decoration: none;
    }

    .employee-biometric-edit-page .page-title-icon,
    .employee-biometric-edit-page .section-icon,
    .employee-biometric-edit-page .source-data-icon,
    .employee-biometric-edit-page .guide-icon,
    .employee-biometric-edit-page .field-icon,
    .employee-biometric-edit-page .protected-notice-icon,
    .employee-biometric-edit-page .app-alert-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        line-height: 1;
    }

    .employee-biometric-edit-page .page-title-icon {
        width: 3rem;
        height: 3rem;
        border-radius: .75rem;
        background: rgba(var(--eb-primary-rgb), .12);
        color: var(--eb-primary);
        font-size: 1.25rem;
        box-shadow: inset 0 0 0 1px rgba(var(--eb-primary-rgb), .08);
    }

    .employee-biometric-edit-page .page-title {
        color: var(--eb-heading);
        font-size: clamp(1.25rem, 2vw, 1.55rem);
        font-weight: 700;
        line-height: 1.25;
    }

    .employee-biometric-edit-page .page-subtitle {
        color: var(--eb-muted);
        font-size: .875rem;
        line-height: 1.5;
    }

    .employee-biometric-edit-page .app-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--eb-border);
        background: var(--eb-soft-bg);
    }

    .employee-biometric-edit-page .app-card-footer {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--eb-border);
        background: var(--eb-soft-bg);
    }

    .employee-biometric-edit-page .section-icon {
        width: 2.35rem;
        height: 2.35rem;
        border-radius: .65rem;
        font-size: .95rem;
    }

    .employee-biometric-edit-page .section-icon-primary {
        color: var(--eb-primary);
        background: rgba(var(--eb-primary-rgb), .12);
    }

    .employee-biometric-edit-page .section-icon-info {
        color: #0787b5;
        background: rgba(39, 188, 253, .14);
    }

    .employee-biometric-edit-page .section-icon-warning {
        color: #b7791f;
        background: rgba(246, 195, 67, .2);
    }

    .employee-biometric-edit-page .section-title {
        color: var(--eb-heading);
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .employee-biometric-edit-page .section-description {
        color: var(--eb-muted);
        font-size: .8rem;
        line-height: 1.45;
    }

    .employee-biometric-edit-page .status-pill,
    .employee-biometric-edit-page .protected-pill,
    .employee-biometric-edit-page .mini-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        border-radius: 999px;
        font-weight: 700;
        white-space: nowrap;
    }

    .employee-biometric-edit-page .status-pill {
        min-height: 1.8rem;
        padding: .35rem .7rem;
        border: 1px solid transparent;
        font-size: .75rem;
    }

    .employee-biometric-edit-page .status-pill-success {
        color: #08734c;
        background: #e7f8f0;
        border-color: #b9ead4;
    }

    .employee-biometric-edit-page .status-pill-secondary {
        color: #596579;
        background: #eef1f5;
        border-color: #d9e0e8;
    }

    .employee-biometric-edit-page .protected-pill {
        padding: .4rem .7rem;
        color: #596579;
        background: #fff;
        border: 1px solid var(--eb-border);
        font-size: .75rem;
    }

    .employee-biometric-edit-page .form-label {
        margin-bottom: .45rem;
        color: var(--eb-heading);
        font-size: .8125rem;
        font-weight: 700;
    }

    .employee-biometric-edit-page .form-control,
    .employee-biometric-edit-page .form-select,
    .employee-biometric-edit-page .input-group-text {
        min-height: 2.55rem;
        border-color: var(--eb-border);
        font-size: .875rem;
    }

    .employee-biometric-edit-page textarea.form-control {
        min-height: 6.5rem;
        resize: vertical;
    }

    .employee-biometric-edit-page .form-control:focus,
    .employee-biometric-edit-page .form-select:focus,
    .employee-biometric-edit-page .form-check-input:focus {
        border-color: rgba(var(--eb-primary-rgb), .6);
        box-shadow: 0 0 0 .2rem rgba(var(--eb-primary-rgb), .12);
    }

    .employee-biometric-edit-page .input-group-text {
        min-width: 2.7rem;
        justify-content: center;
        color: var(--eb-primary);
        background: rgba(var(--eb-primary-rgb), .06);
    }

    .employee-biometric-edit-page .app-input-group .form-control.is-invalid {
        z-index: 3;
    }

    .employee-biometric-edit-page .form-text {
        margin-top: .4rem;
        color: var(--eb-muted);
        font-size: .72rem;
        line-height: 1.45;
    }

    .employee-biometric-edit-page .payroll-switch-panel {
        min-height: 4.65rem;
        padding: .75rem;
        border: 1px solid var(--eb-border);
        border-radius: .6rem;
        background: var(--eb-soft-bg);
    }

    .employee-biometric-edit-page .field-icon {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: .6rem;
        font-size: .9rem;
    }

    .employee-biometric-edit-page .field-icon-success {
        color: #08734c;
        background: #e7f8f0;
    }

    .employee-biometric-edit-page .form-check-input {
        width: 2.55rem;
        height: 1.35rem;
        margin-top: 0;
        cursor: pointer;
    }

    .employee-biometric-edit-page .source-data-item {
        display: flex;
        align-items: flex-start;
        gap: .8rem;
        min-height: 7rem;
        height: 100%;
        padding: .9rem;
        border: 1px solid var(--eb-border);
        border-radius: .65rem;
        background: var(--eb-card-bg);
        transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }

    .employee-biometric-edit-page .source-data-item:hover {
        border-color: rgba(var(--eb-primary-rgb), .35);
        box-shadow: 0 .25rem .75rem rgba(18, 38, 63, .06);
        transform: translateY(-1px);
    }

    .employee-biometric-edit-page .source-data-icon {
        width: 2.15rem;
        height: 2.15rem;
        border-radius: .6rem;
        font-size: .85rem;
    }

    .employee-biometric-edit-page .source-data-icon-primary {
        color: var(--eb-primary);
        background: rgba(var(--eb-primary-rgb), .11);
    }

    .employee-biometric-edit-page .source-data-icon-info {
        color: #0787b5;
        background: rgba(39, 188, 253, .14);
    }

    .employee-biometric-edit-page .source-data-icon-success {
        color: #08734c;
        background: #e7f8f0;
    }

    .employee-biometric-edit-page .source-data-icon-warning {
        color: #9b6a17;
        background: rgba(246, 195, 67, .2);
    }

    .employee-biometric-edit-page .source-data-icon-secondary {
        color: #596579;
        background: #eef1f5;
    }

    .employee-biometric-edit-page .source-data-label {
        margin-bottom: .3rem;
        color: var(--eb-muted);
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .employee-biometric-edit-page .source-data-value {
        color: var(--eb-heading);
        font-size: .875rem;
        font-weight: 700;
        line-height: 1.45;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .employee-biometric-edit-page .source-data-value-mono,
    .employee-biometric-edit-page .summary-value-mono {
        font-family: var(--falcon-font-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace);
        font-size: .73rem;
        font-weight: 600;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .employee-biometric-edit-page .source-data-note {
        margin-top: .25rem;
        color: var(--eb-muted);
        font-size: .7rem;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .employee-biometric-edit-page .protected-notice {
        display: flex;
        align-items: flex-start;
        gap: .8rem;
        padding: .9rem 1rem;
        border: 1px solid #bde8f8;
        border-radius: .65rem;
        color: #12627d;
        background: #effaff;
    }

    .employee-biometric-edit-page .protected-notice-icon {
        width: 2.15rem;
        height: 2.15rem;
        border-radius: .6rem;
        color: #087fa8;
        background: #d7f3fd;
    }

    .employee-biometric-edit-page .protected-notice h6 {
        color: #12627d;
        font-size: .85rem;
    }

    .employee-biometric-edit-page .protected-notice p {
        font-size: .75rem;
        line-height: 1.5;
    }

    .employee-biometric-edit-page .employee-summary-head {
        padding: 1.4rem 1.25rem 1.25rem;
        text-align: center;
        border-bottom: 1px solid var(--eb-border);
        background: linear-gradient(180deg, rgba(var(--eb-primary-rgb), .06), rgba(var(--eb-primary-rgb), 0));
    }

    .employee-biometric-edit-page .employee-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 4.25rem;
        height: 4.25rem;
        margin-bottom: .85rem;
        border: .3rem solid #fff;
        border-radius: 50%;
        color: var(--eb-primary);
        background: rgba(var(--eb-primary-rgb), .14);
        box-shadow: 0 .35rem .9rem rgba(18, 38, 63, .12);
        font-size: 1.45rem;
    }

    .employee-biometric-edit-page .employee-summary-name {
        margin-bottom: .25rem;
        color: var(--eb-heading);
        font-size: 1rem;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .employee-biometric-edit-page .employee-summary-number {
        margin-bottom: .75rem;
        color: var(--eb-muted);
        font-size: .8rem;
        overflow-wrap: anywhere;
    }

    .employee-biometric-edit-page .summary-list {
        padding: .35rem 1.25rem .65rem;
    }

    .employee-biometric-edit-page .summary-row {
        display: grid;
        grid-template-columns: minmax(7.5rem, .9fr) minmax(0, 1.1fr);
        gap: .8rem;
        align-items: start;
        padding: .72rem 0;
        border-bottom: 1px dashed var(--eb-border);
    }

    .employee-biometric-edit-page .summary-row:last-child {
        border-bottom: 0;
    }

    .employee-biometric-edit-page .summary-label {
        display: flex;
        align-items: center;
        gap: .5rem;
        color: var(--eb-muted);
        font-size: .75rem;
        line-height: 1.4;
    }

    .employee-biometric-edit-page .summary-label>span {
        width: .9rem;
        color: var(--eb-primary);
        text-align: center;
    }

    .employee-biometric-edit-page .summary-value {
        min-width: 0;
        color: var(--eb-heading);
        font-size: .75rem;
        font-weight: 700;
        line-height: 1.45;
        text-align: right;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .employee-biometric-edit-page .mini-status {
        padding: .25rem .55rem;
        font-size: .68rem;
    }

    .employee-biometric-edit-page .mini-status-success {
        color: #08734c;
        background: #e7f8f0;
    }

    .employee-biometric-edit-page .mini-status-secondary {
        color: #596579;
        background: #eef1f5;
    }

    .employee-biometric-edit-page .guide-item {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: .75rem;
        margin-bottom: .65rem;
        border: 1px solid transparent;
        border-radius: .6rem;
        background: var(--eb-soft-bg);
    }

    .employee-biometric-edit-page .guide-icon {
        width: 2rem;
        height: 2rem;
        border-radius: .55rem;
        font-size: .8rem;
    }

    .employee-biometric-edit-page .guide-icon-info {
        color: #087fa8;
        background: #d7f3fd;
    }

    .employee-biometric-edit-page .guide-icon-success {
        color: #08734c;
        background: #e7f8f0;
    }

    .employee-biometric-edit-page .guide-icon-secondary {
        color: #596579;
        background: #e8edf3;
    }

    .employee-biometric-edit-page .guide-item h6 {
        color: var(--eb-heading);
        font-size: .8rem;
        font-weight: 700;
    }

    .employee-biometric-edit-page .guide-item p {
        color: var(--eb-muted);
        font-size: .72rem;
        line-height: 1.5;
    }

    .employee-biometric-edit-page .app-alert {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        padding: 1rem;
        border-radius: .7rem;
        box-shadow: 0 .25rem .75rem rgba(18, 38, 63, .05);
    }

    .employee-biometric-edit-page .app-alert-danger {
        color: #842239;
        border: 1px solid #f1b9c5;
        background: #fff1f4;
    }

    .employee-biometric-edit-page .app-alert-icon {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: .6rem;
        color: var(--eb-danger);
        background: #ffdce4;
    }

    .employee-biometric-edit-page .edit-summary-sticky {
        position: sticky;
        top: 1rem;
    }

    @media (max-width: 1199.98px) {
        .employee-biometric-edit-page .edit-summary-sticky {
            position: static;
        }
    }

    @media (max-width: 575.98px) {
        .employee-biometric-edit-page .page-title-icon {
            width: 2.7rem;
            height: 2.7rem;
            border-radius: .65rem;
        }

        .employee-biometric-edit-page .summary-row {
            grid-template-columns: 1fr;
            gap: .25rem;
        }

        .employee-biometric-edit-page .summary-value {
            padding-left: 1.4rem;
            text-align: left;
        }

        .employee-biometric-edit-page .source-data-item {
            min-height: auto;
        }
    }
</style>
