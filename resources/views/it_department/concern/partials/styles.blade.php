@push('styles')
    <style>
        /* ================= TABLE ================= */
        .cctv-simple-table {
            font-size: .82rem;
            min-width: 950px;
        }

        .cctv-simple-table thead th {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .02em;
            color: #5e6e82;
            padding: .7rem 0;
            white-space: nowrap;
        }

        .cctv-simple-table tbody td {
            padding: .6rem 0;
            vertical-align: middle;
        }

        .bus-text {
            max-width: 320px;
            font-size: .76rem;
        }

        .item-text {
            max-width: 230px;
        }

        .assignee-text {
            max-width: 150px;
        }

        .bus-text,
        .item-text,
        .assignee-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn-reveal {
            width: 28px;
            height: 28px;
            padding: 0;
            border-radius: .375rem;
        }

        /* ================= PAGINATION ================= */
        .pagination {
            margin-bottom: 0 !important;
            font-size: 13px !important;
        }

        .pagination .page-link {
            padding: 4px 9px !important;
            font-size: 13px !important;
            border-radius: 6px !important;
        }

        /* ================= SELECT2 ================= */
        .select2-container--bootstrap-5 .select2-dropdown {
            z-index: 9999;
        }

        .select2-container--bootstrap-5 .select2-results__options {
            max-height: 240px !important;
            overflow-y: auto !important;
        }

        .select2-container--bootstrap-5 .select2-results__option {
            font-size: .82rem;
            padding: .45rem .75rem;
        }

        .select2-container--bootstrap-5 .select2-search__field {
            font-size: .85rem;
            padding: .35rem .5rem;
        }

        /* ================= MODAL REDESIGN ================= */
        .cctv-edit-modal .modal-content {
            background: #f8fafd;
            border-radius: 1rem;
        }

        /* Header */
        .cctv-edit-modal .modal-header {
            background: linear-gradient(135deg, #2c7be5, #1a5fd0);
            border: none;
        }

        /* Body */
        .cctv-edit-modal .modal-body {
            max-height: calc(100vh - 180px);
            overflow-y: auto;
            padding: 1.5rem;
        }

        /* Footer sticky */
        .cctv-edit-modal .modal-footer {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid #e3e6ed;
            z-index: 10;
        }

        /* Cards */
        .cctv-edit-modal .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .05);
        }

        /* Locked fields */
        .cctv-edit-modal .form-control[readonly],
        .cctv-edit-modal textarea[readonly] {
            background: #edf2f9 !important;
            color: #6c7a91;
            border: none;
            cursor: not-allowed;
            box-shadow: inset 3px 0 0 rgba(44, 123, 229, .35);
        }

        /* Item rows */
        .cctv-edit-modal .item-row {
            background: #fff;
            border-radius: .75rem;
            border: 1px solid #e3e6ed;
            padding: .75rem;
            transition: .15s ease;
        }

        .cctv-edit-modal .item-row:hover {
            box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .06);
        }

        /* Buttons */
        .cctv-edit-modal .btn-primary {
            background: #2c7be5;
            border: none;
        }

        .cctv-edit-modal .btn-primary:hover {
            background: #1a68d1;
        }

        /* ================= SMALL SCREENS ================= */
        @media (max-width: 991.98px) {
            .cctv-simple-table {
                min-width: 850px;
            }

            .bus-text {
                max-width: 260px;
            }
        }
    </style>
@endpush
