@extends('layouts.master')

@section('content')
<main>
  <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-fluid px-4">
      <div class="page-header-content pt-4">
        <div class="row align-items-center justify-content-between">
          <div class="col-auto mt-4">
            <h1 class="page-header-title">
              <div class="page-header-icon"><i data-feather="link"></i></div>
              API Endpoint
            </h1>
            <div class="page-header-subtitle">Master Data - Manage API endpoints</div>
          </div>

          <div class="col-auto mt-4">
            <button class="btn btn-light" id="btnAdd">
              <i data-feather="plus" class="me-1"></i>
              Add Endpoint
            </button>
          </div>

        </div>
      </div>
    </div>
  </header>

  <div class="container-fluid px-4 mt-n10">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div>
          <h3 class="card-title mb-0">List of API Endpoints</h3>
          <small class="text-muted">Create / edit / delete using modal.</small>
        </div>
      </div>

      @include('partials.alert')

      <div class="card-body">
        <div class="table-responsive">
          <table id="tableApiEndpoint" class="table table-striped table-bordered w-100">
            <thead>
              <tr>
                <th style="width:60px;">#</th>
                <th>Name</th>
                <th>Code</th>
                <th>Method</th>
                <th>URL</th>
                <th>Active</th>
                <th style="width:120px;">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($endpoints as $i => $e)
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td>{{ $e->name }}</td>
                  <td><span class="badge bg-light text-dark">{{ $e->code }}</span></td>
                  <td><span class="badge bg-primary">{{ strtoupper($e->method) }}</span></td>
                  <td class="text-break">
                    {{ trim(($e->base_url ?? '') . ($e->path ?? '')) }}
                  </td>
                  <td>
                    @if($e->is_active)
                      <span class="badge bg-success">Active</span>
                    @else
                      <span class="badge bg-secondary">Inactive</span>
                    @endif
                  </td>
                  <td class="text-nowrap">
                    <button class="btn btn-sm btn-outline-primary btnEdit"
                      data-id="{{ $e->id }}">
                      Edit
                    </button>
                    <button class="btn btn-sm btn-outline-danger btnDelete"
                      data-id="{{ $e->id }}"
                      data-name="{{ $e->name }}">
                      Delete
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</main>

<!-- ===================== MODAL: CREATE / EDIT ===================== -->
<div class="modal fade" id="endpointModal" tabindex="-1" aria-labelledby="endpointModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="endpointModalLabel">Add API Endpoint</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="endpointForm" method="POST" autocomplete="off">
        @csrf
        <input type="hidden" id="formMethod" value="POST">
        <input type="hidden" id="endpointId" value="">

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Name</label>
              <input type="text" class="form-control" name="name" id="name" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Code (unique)</label>
              <input type="text" class="form-control" name="code" id="code" required placeholder="SAP_GET_MATERIAL">
              <small class="text-muted">Uppercase + underscore recommended.</small>
            </div>

            <div class="col-md-4">
              <label class="form-label">Method</label>
              <select class="form-select" name="method" id="method" required>
                <option value="GET">GET</option>
                <option value="POST">POST</option>
                <option value="PUT">PUT</option>
                <option value="DELETE">DELETE</option>
                <option value="PATCH">PATCH</option>
              </select>
            </div>

            <div class="col-md-8">
              <label class="form-label">Base URL</label>
              <input type="text" class="form-control" name="base_url" id="base_url" placeholder="https://api.company.com">
            </div>

            <div class="col-md-12">
              <label class="form-label">Path</label>
              <input type="text" class="form-control" name="path" id="path" placeholder="/v1/materials">
            </div>

            <div class="col-md-12">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" id="description" rows="2"></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label">Auth Type</label>
              <select class="form-select" name="auth_type" id="auth_type">
                <option value="none">none</option>
                <option value="basic">basic</option>
                <option value="bearer">bearer</option>
                <option value="api_key">api_key</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Active</label>
              <select class="form-select" name="is_active" id="is_active">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Auth Key (optional)</label>
              <input type="text" class="form-control" name="auth_key" id="auth_key" placeholder="Authorization / x-api-key">
            </div>

            <div class="col-md-6">
              <label class="form-label">Auth Value (optional)</label>
              <input type="text" class="form-control" name="auth_value" id="auth_value" placeholder="Bearer token / API Key">
            </div>

            <div class="col-md-12">
              <label class="form-label">Headers (JSON)</label>
              <textarea class="form-control" name="headers" id="headers" rows="2"
                placeholder='{"Content-Type":"application/json"}'></textarea>
            </div>

            <div class="col-md-12">
              <label class="form-label">Params (JSON)</label>
              <textarea class="form-control" name="params" id="params" rows="2"
                placeholder='{"plant":"HQ"}'></textarea>
            </div>

            <div class="col-md-12">
              <label class="form-label">Body Template (JSON)</label>
              <textarea class="form-control" name="body_template" id="body_template" rows="3"
                placeholder='{"example":"value"}'></textarea>
            </div>

            <div class="col-12">
              <div class="alert alert-danger d-none" id="formError"></div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="btnSave">
            <i data-feather="save" class="me-1"></i> Save
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- ===================== MODAL: DELETE ===================== -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Delete Endpoint</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p class="mb-0">Are you sure you want to delete:</p>
          <p class="fw-bold mt-2" id="deleteName">-</p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Yes, Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
    $(document).ready(function() {
      // DataTables
      const table = $("#tableApiEndpoint").DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false
      });

      const endpointModal = new bootstrap.Modal(document.getElementById('endpointModal'));
      const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

      const baseIndexUrl = "{{ url('/api-endpoint') }}";

      function resetForm() {
        $("#endpointForm")[0].reset();
        $("#endpointId").val('');
        $("#formMethod").val('POST');
        $("#endpointModalLabel").text('Add API Endpoint');
        $("#formError").addClass('d-none').text('');
      }

      function fillForm(data) {
        $("#endpointId").val(data.id);
        $("#name").val(data.name);
        $("#code").val(data.code);
        $("#method").val(data.method);
        $("#base_url").val(data.base_url ?? '');
        $("#path").val(data.path ?? '');
        $("#description").val(data.description ?? '');
        $("#auth_type").val(data.auth_type ?? 'none');
        $("#auth_key").val(data.auth_key ?? '');
        $("#auth_value").val(data.auth_value ?? '');
        $("#headers").val(data.headers ? JSON.stringify(data.headers) : '');
        $("#params").val(data.params ? JSON.stringify(data.params) : '');
        $("#body_template").val(data.body_template ? JSON.stringify(data.body_template) : '');
        $("#is_active").val(data.is_active ? 1 : 0);
      }

      // Add
      $("#btnAdd").on("click", function() {
        resetForm();
        endpointModal.show();
        setTimeout(() => { if (window.feather) feather.replace(); }, 10);
      });

      // Edit (load via fetch)
      $(".btnEdit").on("click", function() {
        const id = $(this).data("id");
        resetForm();
        $("#endpointModalLabel").text("Edit API Endpoint");
        $("#formMethod").val("PUT");

        $.get(`${baseIndexUrl}/${id}`, function(res) {
          fillForm(res.data);
          endpointModal.show();
          setTimeout(() => { if (window.feather) feather.replace(); }, 10);
        }).fail(function(xhr) {
          alert("Failed to load data.");
        });
      });

      // Save (Create / Update)
      $("#endpointForm").on("submit", function(e) {
        e.preventDefault();

        const id = $("#endpointId").val();
        const method = $("#formMethod").val();
        const url = (method === "PUT") ? `${baseIndexUrl}/${id}` : baseIndexUrl;

        // Prepare payload
        const payload = {
          _token: "{{ csrf_token() }}",
          name: $("#name").val(),
          code: $("#code").val(),
          method: $("#method").val(),
          base_url: $("#base_url").val(),
          path: $("#path").val(),
          description: $("#description").val(),
          auth_type: $("#auth_type").val(),
          auth_key: $("#auth_key").val(),
          auth_value: $("#auth_value").val(),
          is_active: $("#is_active").val()
        };

        // JSON fields (optional)
        payload.headers = $("#headers").val();
        payload.params = $("#params").val();
        payload.body_template = $("#body_template").val();

        if (method === "PUT") payload._method = "PUT";

        $.ajax({
          url: url,
          type: "POST",
          data: payload,
          success: function() {
            window.location.reload();
          },
          error: function(xhr) {
            let msg = "Failed to save.";
            if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
            if (xhr.responseJSON?.errors) {
              msg = Object.values(xhr.responseJSON.errors).flat().join("<br>");
            }
            $("#formError").removeClass("d-none").html(msg);
          }
        });
      });

      // Delete
      $(".btnDelete").on("click", function() {
        const id = $(this).data("id");
        const name = $(this).data("name");
        $("#deleteName").text(name);
        $("#deleteForm").attr("action", `${baseIndexUrl}/${id}`);
        deleteModal.show();
      });
    });
  </script>
@endsection
