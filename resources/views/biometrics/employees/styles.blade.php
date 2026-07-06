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
                max-height: calc(100vh - 15rem);
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

                .bio-table-wrapper {
                    max-height: none;
                }
            }
        </style>
