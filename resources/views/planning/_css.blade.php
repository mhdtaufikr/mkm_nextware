<style>
    /* ✅ Freeze Table Header */
    .table-wrapper {
        max-height: 600px;
        overflow-y: auto;
        overflow-x: auto;
        position: relative;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }

    .table-planning {
        margin-bottom: 0;
    }

    .table-planning thead th {
        position: sticky;
        top: 0;
        background-color: #5a9fd4;
        color: white;
        z-index: 10;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
    }

    /* ✅ Freeze First Column (Cutting Center) */
    .table-planning tbody th {
        position: sticky;
        left: 0;
        background-color: #f8f9fa;
        z-index: 5;
        box-shadow: 2px 0 2px -1px rgba(0, 0, 0, 0.1);
        font-weight: 600;
    }

    /* ✅ Freeze Second Column (Code) - SEMUA row */
    .table-planning tbody td:first-of-type {
        position: sticky;
        left: 150px; /* Sesuaikan dengan lebar kolom Cutting Center */
        background-color: #ffffff;
        z-index: 4;
        box-shadow: 2px 0 2px -1px rgba(0, 0, 0, 0.1);
        font-weight: 500;
    }

    /* ✅ Freeze header Code column */
    .table-planning thead th:nth-child(2) {
        position: sticky;
        left: 150px; /* Sama dengan left di tbody */
        z-index: 11;
        background-color: #5a9fd4;
        box-shadow: 2px 0 2px -1px rgba(0, 0, 0, 0.1);
    }

    /* ✅ Freeze Second Column (Code) */
    .table-planning tbody td:first-child {
        position: sticky;
        left: 0;
        background-color: #ffffff;
        z-index: 4;
        box-shadow: 2px 0 2px -1px rgba(0, 0, 0, 0.1);
        font-weight: 500;
    }

    /* ✅ Input Styling */
    .qty-input {
        width: 70px;
        text-align: center;
        padding: 4px 8px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        transition: all 0.2s;
        background-color: transparent;
    }

    .qty-input:focus {
        border-color: #5a9fd4;
        box-shadow: 0 0 0 0.2rem rgba(90, 159, 212, 0.15);
        outline: none;
    }

    .qty-input.is-dirty {
        border-color: #ffc107;
        background-color: #fff8e1;
    }

    .qty-input.is-valid {
        border-color: #28a745;
        background-color: #e8f5e9;
    }

    .qty-input.is-invalid {
        border-color: #dc3545;
        background-color: #ffebee;
    }

    /* ✅ Hover Effect */
    .table-planning tbody tr:hover {
        background-color: #f5f7fa;
    }

    /* ✅ Weekend Column Highlight - Abu-abu gelap */
    .weekend-col {
        background-color: #d6d8db !important;
    }

    .weekend-col .qty-input {
        background-color: #e9ecef;
    }

    /* ✅ Weekday Column - Putih bersih */
    .weekday-col {
        background-color: #ffffff;
    }

    /* ✅ Header Cell Styling */
    .table-planning thead th {
        font-size: 12px;
        padding: 10px 8px;
        white-space: nowrap;
        text-align: center;
    }

    .table-planning tbody td,
    .table-planning tbody th {
        font-size: 13px;
        padding: 8px;
        vertical-align: middle;
    }

    /* ✅ Scrollbar Styling */
    .table-wrapper::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    .table-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 5px;
    }

    .table-wrapper::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 5px;
    }

    .table-wrapper::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* ✅ Header weekend styling */
    .table-planning thead th.weekend-col {
        background-color: #7f8c8d;
        color: white;
    }

    /* ✅ Row striping untuk readability */
    .table-planning tbody tr:nth-child(even) {
        background-color: #fafbfc;
    }

    .table-planning tbody tr:nth-child(even) .weekend-col {
        background-color: #c8cbce !important;
    }
</style>