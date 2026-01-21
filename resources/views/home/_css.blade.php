<style>
    /* ✅ EXTREME COMPACT MODE */

    /* 1. Header Page - Super Compact */
    .page-header {
        padding-bottom: 1rem !important; /* Dari 2rem → 1rem */
        padding-top: 0.5rem !important;
    }

    .page-header-content {
        padding-top: 0.5rem !important; /* Kurangi padding top */
    }

    .page-header-title {
        font-size: 1.25rem !important;
        margin-bottom: 0 !important;
    }

    .page-header-subtitle {
        font-size: 0.8rem !important;
        margin-top: 0.25rem !important;
    }

    .page-header-icon i {
        width: 20px !important;
        height: 20px !important;
    }

    /* 2. Container Margins - Super Tight */
    .container-fluid.mt-n15 {
        margin-top: -120px !important; /* Dari -15 → lebih naik lagi */
    }

    .container-fluid {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }

    /* 3. Cards - Ultra Compact */
    .card-custom {
        margin-bottom: 0.25rem !important; /* Dari 0.5rem → 0.25rem */
    }

    .card-custom .card-header {
        padding: 0.35rem 0.75rem !important; /* Lebih kecil lagi */
    }

    .card-custom .card-body {
        padding: 0.5rem 0.75rem !important; /* Dari 0.75rem → 0.5rem */
    }

    .card-title {
        font-size: 0.9rem !important;
        margin-bottom: 0 !important;
        font-weight: 600;
    }

    /* 4. Stock Strength Specific */
    #stockStrengthChart {
        height: 240px !important; /* Dari 280px → 240px */
        margin-bottom: 0 !important;
    }

    .alert-light {
        padding: 0.35rem 0.75rem !important;
        margin-bottom: 0 !important;
        margin-top: 0.25rem !important;
        font-size: 0.7rem !important;
    }

    /* 5. Badges - Smaller */
    .badge {
        padding: 0.2rem 0.4rem !important;
        font-size: 0.65rem !important;
        font-weight: 500;
    }

    /* 6. OTDP Charts - Super Compact */
    .chart-custom {
        height: 200px !important; /* Dari 220px → 200px */
        margin-top: 0 !important;
    }

    .indicator-table {
        font-size: 0.7rem !important;
        margin-bottom: 0.25rem !important;
    }

    .indicator-table th,
    .indicator-table td {
        padding: 0.2rem 0.4rem !important; /* Lebih kecil lagi */
        line-height: 1.2;
    }

    .signal {
        width: 24px !important;
        height: 24px !important;
        font-size: 0.65rem !important;
        padding: 0.15rem !important;
    }

    /* 7. Carousel Controls - Compact */
    .carousel-control-prev,
    .carousel-control-next {
        width: 25px !important;
        opacity: 0.7;
    }

    .carousel-indicators {
        margin-bottom: 0.25rem !important;
    }

    .carousel-indicators [data-bs-target] {
        width: 6px !important;
        height: 6px !important;
        margin: 0 3px !important;
    }

    /* 8. Table - Compact */
    .table-sm {
        font-size: 0.75rem !important;
    }

    .table-sm th,
    .table-sm td {
        padding: 0.3rem !important;
        line-height: 1.3;
    }

    .table-responsive {
        margin-top: 0.25rem !important;
    }

    /* 9. Buttons - Smaller */
    .btn-sm {
        padding: 0.2rem 0.4rem !important;
        font-size: 0.75rem !important;
        line-height: 1.2;
    }

    .view-detail-btn {
        padding: 0.15rem 0.35rem !important;
    }

    /* 10. Icons - Smaller */
    [data-feather] {
        width: 14px !important;
        height: 14px !important;
    }

    /* 11. Row spacing */
    .row {
        margin-left: -0.5rem !important;
        margin-right: -0.5rem !important;
    }

    .row > * {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }

    /* 12. Legend Compact */
    .alert-light .row .col-md-3,
    .alert-light .row .col-md-12 {
        padding: 0.15rem !important;
        line-height: 1.4;
    }

    /* 13. Toggle Table Button */
    #toggleTableBtn {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.7rem !important;
    }

    /* 14. Collapse smooth */
    .collapse {
        transition: height 0.25s ease !important;
    }

    /* 15. Remove extra margins */
    p {
        margin-bottom: 0.25rem !important;
    }

    h6 {
        margin-bottom: 0.25rem !important;
    }

    /* 16. Form Select Compact */
    .form-select-sm {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.8rem !important;
    }

    /* 17. Main content */
    main {
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* 18. Responsive Full Width */
    @media (min-width: 1400px) {
        .container-fluid {
            max-width: 100%;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
    }

    @media (max-width: 1399px) {
        .chart-custom {
            height: 180px !important;
        }

        #stockStrengthChart {
            height: 220px !important;
        }
    }

    /* 19. Chart text size */
    .chart-container text {
        font-size: 0.7rem !important;
    }

    /* 20. Status badges in header */
    .card-header .badge {
        padding: 0.15rem 0.35rem !important;
        font-size: 0.6rem !important;
    }
    .table-danger {
        background-color: #f8d7da !important;
    }
    .table-warning {
        background-color: #fff3cd !important;
    }
    .table-info {
        background-color: #cff4fc !important;
    }
    .table-success {
        background-color: #d1e7dd !important;
    }

    .bg-opacity-25 {
        --bs-bg-opacity: 0.25;
    }

    .view-detail-btn {
        padding: 0.25rem 0.5rem;
    }

    /* ✅ Smooth animation untuk collapse */
    .collapse {
        transition: height 0.35s ease;
    }

    .collapsing {
        transition: height 0.35s ease;
    }
</style>