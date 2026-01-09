<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form method="POST" action="{{ route('changePassword') }}" autocomplete="off">
          @csrf

          <div class="modal-body">

            <div class="mb-3">
              <label for="oldPassword" class="form-label">Old Password</label>
              <input type="password" class="form-control" id="oldPassword" name="old_password" required>
            </div>

            <div class="mb-3">
              <label for="newPassword" class="form-label">New Password</label>
              <input type="password" class="form-control" id="newPassword" name="new_password" required>
            </div>

            <div class="mb-3">
              <label for="confirmPassword" class="form-label">Confirm New Password</label>
              <input type="password" class="form-control" id="confirmPassword" name="new_password_confirmation" required>
            </div>

            <!-- Validation errors -->
            @if ($errors->any())
              <div class="alert alert-danger mb-0">
                <ul class="mb-0 ps-3">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i data-feather="key" class="me-1"></i>
              Update Password
            </button>
          </div>

        </form>

      </div>
    </div>
  </div>
