<?php $__env->startPush('styles'); ?>
    <style>
        .page-hero-card {
            border-radius: 18px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        }

        .hero-icon {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(13, 110, 253, 0.12);
            color: #0d6efd;
            font-size: 1.3rem;
        }

        .monitor-card,
        .jo-card,
        .filter-card {
            border-radius: 18px;
            overflow: hidden;
        }

        .monitor-tile,
        .insight-card {
            border: 1px solid #e9ecef;
            border-radius: 16px;
            background: #fff;
            padding: 1rem;
            transition: .2s ease;
            height: 100%;
        }

        .monitor-tile:hover,
        .insight-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .tile-label {
            font-size: .8rem;
            color: #6c757d;
            margin-bottom: .35rem;
            font-weight: 600;
        }

        .tile-value {
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.1;
            color: #212529;
        }

        .tile-subtext {
            font-size: .8rem;
            color: #6c757d;
            margin-top: .35rem;
        }

        .status-open {
            border-left: 4px solid #f0ad4e;
        }

        .status-progress {
            border-left: 4px solid #0dcaf0;
        }

        .status-fixed {
            border-left: 4px solid #198754;
        }

        .status-closed {
            border-left: 4px solid #6c757d;
        }

        .insight-title {
            font-size: .85rem;
            font-weight: 700;
            color: #6c757d;
            margin-bottom: .75rem;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .insight-main {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: .75rem;
            gap: .75rem;
        }

        .insight-main strong {
            font-size: 1.1rem;
            color: #0d6efd;
        }

        .insight-list .insight-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .45rem 0;
            border-top: 1px dashed rgba(0, 0, 0, 0.08);
            font-size: .88rem;
        }

        .search-box {
            width: 320px;
        }

        .jo-table-wrap {
            max-height: 620px;
        }

        .jo-table thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background: #f8f9fa;
            font-size: .8rem;
            font-weight: 700;
            color: #495057;
            padding-top: .9rem;
            padding-bottom: .9rem;
            white-space: nowrap;
        }

        .jo-table tbody td {
            padding-top: 1rem;
            padding-bottom: 1rem;
            vertical-align: middle;
            border-color: #f1f3f5;
        }

        .jo-table tbody tr:hover {
            background: #fafcff;
        }

        .table-chip {
            display: inline-flex;
            align-items: center;
            padding: .35rem .6rem;
            border-radius: 999px;
            background: rgba(13, 110, 253, 0.08);
            color: #0d6efd;
            font-size: .78rem;
            font-weight: 600;
        }

        .empty-state {
            padding: 3rem 1rem;
        }

        .empty-state-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #0d6efd;
            font-size: 1.2rem;
        }

        .form-hint {
            font-size: .78rem;
            color: #6c757d;
        }

        .cctv-modal {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-body {
            scroll-behavior: smooth;
        }

        .cctv-modal .modal-body {
            overflow-y: auto;
            max-height: calc(90vh - 120px);
        }

        .cctv-modal-header {
            padding: 1.5rem 1.5rem 1rem;
            background: linear-gradient(135deg, #f8fbff 0%, #ffffff 100%);
        }

        .modal-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(13, 110, 253, 0.12);
            color: #0d6efd;
            font-size: 1.15rem;
        }

        .form-section {
            background: #fff;
            border: 1px solid #edf2f7;
            border-radius: 18px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: .95rem;
            font-weight: 700;
            color: #344054;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .form-control-modern,
        .form-select-modern {
            min-height: 44px;
            border-radius: 12px;
            border: 1px solid #dbe2ea;
            box-shadow: none;
        }

        .form-control-modern:focus,
        .form-select-modern:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.12);
        }

        textarea.form-control-modern {
            min-height: auto;
            resize: vertical;
            padding-top: .75rem;
        }

        .items-card {
            background: #f8fafc;
            border: 1px dashed #dbe2ea;
            border-radius: 16px;
            padding: 1rem;
        }

        .item-row-modern {
            background: #fff;
            border: 1px solid #edf2f7;
            border-radius: 14px;
            padding: .75rem;
        }

        .pagination {
            font-size: 14px !important;
            margin: 0 !important;
        }

        .pagination .page-link {
            padding: 4px 10px !important;
            font-size: 14px !important;
            border-radius: 8px !important;
            color: #4a4a4a !important;
            border: 1px solid #d0d5dd !important;
            background: #f8f9fa !important;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
            font-weight: 600 !important;
        }

        .pagination .page-link:hover {
            background: #e9ecef !important;
            border-color: #c4c9cf !important;
        }

        .pagination .page-item.disabled .page-link {
            opacity: .5 !important;
        }

        .pagination .page-item {
            margin: 0 2px !important;
        }

        @media (max-width: 767.98px) {
            .search-box {
                width: 100%;
            }

            .tile-value {
                font-size: 1.5rem;
            }

            .cctv-modal-header {
                padding: 1rem 1rem .75rem;
            }

            .form-section {
                padding: .85rem;
            }
        }
    </style>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/it_department/concern/partials/styles.blade.php ENDPATH**/ ?>