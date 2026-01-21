  <!-- Modal Detail OTDP (Single modal untuk Inbound & Outbound) -->
  <div class="modal fade" id="otdpDetailModal" tabindex="-1" aria-labelledby="otdpDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="otdpDetailModalLabel">Detail OTDP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modal-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="modal-content" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Date:</strong> <span id="detail-date"></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Type:</strong> <span id="detail-type" class="text-uppercase badge bg-info"></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Cutting Center/Rack:</strong> <span id="detail-cc" class="badge bg-secondary"></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Plan:</strong> <span id="detail-plan-qty" class="text-primary fw-bold"></span> |
                            <strong>Actual:</strong> <span id="detail-actual-qty" class="text-success fw-bold"></span>
                        </div>
                    </div>

                    <ul class="nav nav-tabs" id="detailTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="planning-tab" data-bs-toggle="tab"
                                    data-bs-target="#planning" type="button" role="tab">
                                Planning Data (<span id="planning-count">0</span>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="actual-tab" data-bs-toggle="tab"
                                    data-bs-target="#actual" type="button" role="tab">
                                Actual Data - Orders (<span id="actual-count">0</span>)
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="detailTabsContent">
                        <!-- Planning Tab -->
                        <div class="tab-pane fade show active" id="planning" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Plan Date</th>
                                            <th>Cutting Center</th>
                                            <th>Code</th>
                                            <th>Qty</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody id="planning-table-body">
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="4" class="text-end">Total:</th>
                                            <th id="planning-total-qty">0</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Actual Tab -->
                        <div class="tab-pane fade" id="actual" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Detail ID</th>
                                            <th>Order ID</th>
                                            <th>Ref Number</th>
                                            <th>Code</th>
                                            <th>Serial Number</th>
                                            <th>SKU</th>
                                            <th>Product Name</th>
                                            <th>Rack</th>
                                            <th>Qty</th>
                                            <th>Order Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="actual-table-body">
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="8" class="text-end">Total:</th>
                                            <th id="actual-total-qty">0</th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- ✅ Declare selectedLocation HANYA SEKALI di awal, SEBELUM semua script -->
<script>
    const selectedLocation = @json($selected);

    // ✅ Global function untuk load modal (OUTSIDE am5.ready)
    function loadDetailModal(type, cuttingCenter, day) {
        const modal = new bootstrap.Modal(document.getElementById('otdpDetailModal'));

        $('#modal-loading').show();
        $('#modal-content').hide();

        modal.show();

        $.ajax({
            url: "{{ route('home.detail') }}",
            type: 'GET',
            data: {
                location_code: selectedLocation.location_code,
                external_id: selectedLocation.external_id,
                type: type,
                day: day,
                cutting_center: cuttingCenter
            },
            success: function(response) {
                $('#modal-loading').hide();
                $('#modal-content').show();

                // Fill summary
                $('#detail-date').text(response.summary.date);
                $('#detail-type').text(response.summary.type);
                $('#detail-cc').text(response.summary.cutting_center);
                $('#detail-plan-qty').text(response.summary.total_plan_qty);
                $('#detail-actual-qty').text(response.summary.total_actual_qty);

                // Update counts
                $('#planning-count').text(response.planning.length);
                $('#actual-count').text(response.actual.length);

                // Fill planning table
                let planningHtml = '';
                let planningTotal = 0;
                if (response.planning.length > 0) {
                    response.planning.forEach(item => {
                        planningTotal += parseInt(item.qty) || 0;
                        planningHtml += `
                            <tr>
                                <td>${item.id}</td>
                                <td>${item.plan_date}</td>
                                <td><span class="badge bg-secondary">${item.cutting_center}</span></td>
                                <td>${item.code || '-'}</td>
                                <td class="text-end">${item.qty}</td>
                                <td><span class="badge bg-info">${item.type}</span></td>
                            </tr>
                        `;
                    });
                } else {
                    planningHtml = '<tr><td colspan="6" class="text-center text-muted">No planning data for this date</td></tr>';
                }
                $('#planning-table-body').html(planningHtml);
                $('#planning-total-qty').text(planningTotal);

                // Fill actual table
                let actualHtml = '';
                let actualTotal = 0;
                if (response.actual.length > 0) {
                    response.actual.forEach(item => {
                        actualTotal += parseInt(item.qty) || 0;
                        const statusColor = item.status.toLowerCase() === 'done' ? 'success' : 'warning';
                        actualHtml += `
                            <tr>
                                <td>${item.id}</td>
                                <td>${item.order_id}</td>
                                <td>${item.ref_number || '-'}</td>
                                <td>${item.code || '-'}</td>
                                <td>${item.serial_number || '-'}</td>
                                <td><strong>${item.sku}</strong></td>
                                <td>${item.product_name}</td>
                                <td><span class="badge bg-secondary">${item.rack || '-'}</span></td>
                                <td class="text-end">${item.qty}</td>
                                <td>${new Date(item.order_date).toLocaleString('id-ID', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}</td>
                                <td><span class="badge bg-${statusColor}">${item.status}</span></td>
                            </tr>
                        `;
                    });
                } else {
                    actualHtml = '<tr><td colspan="11" class="text-center text-muted">No actual data for this date</td></tr>';
                }
                $('#actual-table-body').html(actualHtml);
                $('#actual-total-qty').text(actualTotal);
            },
            error: function(xhr) {
                $('#modal-loading').hide();
                $('#modal-content').show();
                $('#modal-content').html(`
                    <div class="alert alert-danger">
                        <h5>Error loading data</h5>
                        <p>${xhr.responseJSON?.message || xhr.responseText || 'Unknown error'}</p>
                    </div>
                `);
            }
        });
    }
</script>