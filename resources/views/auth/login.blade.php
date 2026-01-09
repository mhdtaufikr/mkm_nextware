<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="description" content="NEXTWARE Login" />
  <meta name="author" content="" />

  <title>NEXTWARE | Login</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Font Awesome (optional) -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />

  <!-- Favicon -->
  <link rel="icon" href="{{ asset('assets/img/nextware/favicon.png') }}">

  <!-- Font (optional) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --nw-ink: #0b1b2b;
      --nw-text: #334155;
      --nw-muted: #64748b;
      --nw-border: #e6eef8;
      --nw-surface: #ffffff;
      --nw-bg: #f6f8fb;
      --nw-primary: #0b5ed7;
      --nw-primary-2: #0aa2c0;
      --nw-success: #16a34a;
      --nw-shadow: 0 14px 50px rgba(11, 27, 43, 0.14);
      --nw-shadow-soft: 0 8px 28px rgba(11, 27, 43, 0.08);
      --nw-radius: 16px;
    }

    html,
    body {
      height: 100%;
    }

    body {
      margin: 0;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background: var(--nw-bg);
      color: var(--nw-text);
      overflow: hidden;
    }

    /* Layout */
    .nw-shell {
      height: 100vh;
      display: flex;
      width: 100%;
      padding: 0;
      margin: 0;
    }

    /* Left hero */
    .nw-hero {
      position: relative;
      flex: 7;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 56px;
      background:
        radial-gradient(1200px 800px at 20% 20%, rgba(11, 94, 215, 0.18), transparent 55%),
        radial-gradient(900px 600px at 70% 30%, rgba(10, 162, 192, 0.18), transparent 55%),
        radial-gradient(900px 700px at 40% 85%, rgba(22, 163, 74, 0.14), transparent 55%),
        linear-gradient(135deg, #0b1b2b 0%, #0f2b48 40%, #0b1b2b 100%);
      color: #eaf2ff;
    }

    /* subtle animated shimmer */
    .nw-hero::before {
      content: "";
      position: absolute;
      inset: -40%;
      background: conic-gradient(from 180deg, rgba(255, 255, 255, 0.06), transparent, rgba(255, 255, 255, 0.06));
      animation: spin 16s linear infinite;
      filter: blur(20px);
      opacity: 0.8;
    }

    .nw-hero::after {
      content: "";
      position: absolute;
      inset: 0;
      background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
      background-size: 18px 18px;
      opacity: 0.20;
      pointer-events: none;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    .nw-hero-card {
      position: relative;
      z-index: 1;
      width: min(820px, 100%);
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.14);
      border-radius: calc(var(--nw-radius) + 4px);
      box-shadow: var(--nw-shadow);
      padding: 40px;
      backdrop-filter: blur(10px);
    }

    .nw-brand {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 18px;
    }

    .nw-brand .mark {
      width: 54px;
      height: 54px;
      border-radius: 14px;
      display: grid;
      place-items: center;
      background: linear-gradient(135deg, rgba(11, 94, 215, 0.9), rgba(10, 162, 192, 0.85));
      box-shadow: 0 10px 24px rgba(0, 0, 0, 0.22);
      border: 1px solid rgba(255, 255, 255, 0.18);
      overflow: hidden;
    }

    .nw-brand .mark img {
      width: 44px;
      height: 44px;
      object-fit: contain;
    }

    .nw-brand .name {
      line-height: 1.05;
    }

    .nw-brand .name h1 {
      font-size: 1.75rem;
      margin: 0;
      font-weight: 800;
      letter-spacing: 0.3px;
      color: #ffffff;
    }

    .nw-brand .name p {
      margin: 3px 0 0 0;
      color: rgba(234, 242, 255, 0.88);
      font-weight: 500;
      font-size: 0.98rem;
    }

    .nw-hero-card h2 {
      margin: 10px 0 10px;
      font-size: 2.25rem;
      font-weight: 800;
      letter-spacing: -0.5px;
      color: #ffffff;
    }

    .nw-hero-card p.lead {
      margin: 0;
      color: rgba(234, 242, 255, 0.86);
      font-size: 1.05rem;
      line-height: 1.55;
    }

    .nw-divider {
      margin: 18px 0;
      border-color: rgba(255, 255, 255, 0.12);
      opacity: 1;
    }

    .nw-feature-list {
      margin: 0;
      padding: 0;
      list-style: none;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .nw-feature {
      display: flex;
      gap: 10px;
      align-items: flex-start;
      padding: 12px 14px;
      border-radius: 14px;
      border: 1px solid rgba(255, 255, 255, 0.12);
      background: rgba(255, 255, 255, 0.06);
    }

    .nw-feature i {
      margin-top: 2px;
      color: rgba(234, 242, 255, 0.92);
    }

    .nw-feature b {
      color: #ffffff;
    }

    .nw-feature span {
      display: block;
      color: rgba(234, 242, 255, 0.80);
      font-size: 0.95rem;
      line-height: 1.35;
      margin-top: 2px;
    }

    /* Right login */
    .nw-login {
      flex: 3;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--nw-surface);
      box-shadow: -10px 0 40px rgba(11, 27, 43, 0.06);
      padding: 36px;
    }

    .nw-login-card {
      width: min(420px, 100%);
      padding: 6px;
    }

    .nw-login-header {
      text-align: left;
      margin-bottom: 18px;
    }

    .nw-login-header .mini-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 14px;
    }

    .nw-login-header .mini-brand img {
      width: 44px;
      height: 44px;
      object-fit: contain;
      border-radius: 12px;
      background: #f1f5ff;
      padding: 7px;
      border: 1px solid rgba(11, 94, 215, 0.10);
    }

    .nw-login-header h3 {
      margin: 0;
      font-weight: 800;
      font-size: 1.35rem;
      color: var(--nw-ink);
      letter-spacing: -0.2px;
    }

    .nw-login-header p {
      margin: 6px 0 0;
      color: var(--nw-muted);
      font-size: 0.95rem;
    }

    .nw-field {
      margin-bottom: 12px;
    }

    .form-control {
      height: 46px;
      border-radius: 12px;
      border: 1px solid var(--nw-border);
      padding-left: 44px;
      box-shadow: none;
    }

    .form-control:focus {
      border-color: rgba(11, 94, 215, 0.40);
      box-shadow: 0 0 0 0.2rem rgba(11, 94, 215, 0.12);
    }

    .nw-input {
      position: relative;
    }

    .nw-input i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
    }

    .nw-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 10px 0 14px;
      gap: 10px;
    }

    .nw-actions a {
      color: var(--nw-muted);
      text-decoration: none;
    }

    .nw-actions a:hover {
      color: var(--nw-primary);
      text-decoration: underline;
    }

    .btn-nw {
      height: 46px;
      border-radius: 12px;
      font-weight: 700;
      letter-spacing: 0.2px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .btn-nw-primary {
      background: linear-gradient(135deg, var(--nw-primary), var(--nw-primary-2));
      border: 0;
      color: #fff;
      box-shadow: var(--nw-shadow-soft);
    }

    .btn-nw-primary:hover {
      filter: brightness(1.02);
      transform: translateY(-1px);
    }

    .btn-nw-primary:active {
      transform: translateY(0);
    }

    .btn-nw-outline {
      background: #fff;
      border: 1px solid var(--nw-border);
      color: var(--nw-ink);
    }

    .btn-nw-outline:hover {
      border-color: rgba(11, 94, 215, 0.30);
      background: rgba(11, 94, 215, 0.04);
    }

    .nw-separator {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 16px 0;
      color: #94a3b8;
      font-size: 0.9rem;
    }

    .nw-separator hr {
      flex: 1;
      border-top: 1px solid var(--nw-border);
      margin: 0;
      opacity: 1;
    }

    .nw-foot {
      margin-top: 18px;
      font-size: 0.85rem;
      color: #94a3b8;
    }

    /* Responsive */
    @media (max-width: 992px) {
      body {
        overflow: auto;
      }

      .nw-shell {
        flex-direction: column;
        height: auto;
        min-height: 100vh;
      }

      .nw-hero {
        order: 2;
        flex: none;
        padding: 26px 18px;
      }

      .nw-hero-card {
        padding: 22px;
      }

      .nw-feature-list {
        grid-template-columns: 1fr;
      }

      .nw-login {
        order: 1;
        flex: none;
        padding: 22px 18px;
      }
    }

    @media (prefers-reduced-motion: reduce) {
      .nw-hero::before {
        animation: none;
      }

      .btn-nw-primary:hover {
        transform: none;
      }
    }
  </style>
</head>

<body>
  <div class="nw-shell">

    <!-- HERO / LEFT -->
    <section class="nw-hero" aria-label="NEXTWARE Overview">
      <div class="nw-hero-card">

        <div class="nw-brand">
          <div class="mark" aria-hidden="true">
            <!-- Use your NEXTWARE mark (optional). If you only have PNG, it still works. -->
            <img src="{{ asset('assets\img\Logo Option 3 (1).png') }}" alt="" />
          </div>
          <div class="name">
            <h1>NEXTWARE</h1>
            <p>Next-Generation Integrated Smart Warehouse</p>
          </div>
        </div>

        <h2>Warehouse operations, built for what’s next.</h2>
        <p class="lead">
          One platform to orchestrate inbound, outbound, inventory, and asset processes—
          enabling real-time visibility, control, and data-driven decisions.
        </p>

        <hr class="nw-divider" />

        <ul class="nw-feature-list" aria-label="Key features">
          <li class="nw-feature">
            <i class="fas fa-sitemap"></i>
            <div>
              <b>End-to-end Integration</b>
              <span>Connect inbound, outbound, inventory, asset, and approvals in one flow.</span>
            </div>
          </li>
          <li class="nw-feature">
            <i class="fas fa-bolt"></i>
            <div>
              <b>Operational Efficiency</b>
              <span>Reduce manual work, minimize errors, and accelerate throughput.</span>
            </div>
          </li>
          <li class="nw-feature">
            <i class="fas fa-chart-line"></i>
            <div>
              <b>Real-time Insights</b>
              <span>Dashboards and audit trails for better decisions and compliance.</span>
            </div>
          </li>
          <li class="nw-feature">
            <i class="fas fa-layer-group"></i>
            <div>
              <b>Scalable & Future-Ready</b>
              <span>Designed to grow with business needs and integrate with ERP systems.</span>
            </div>
          </li>
        </ul>

      </div>
    </section>

    <!-- LOGIN / RIGHT -->
    <aside class="nw-login" aria-label="Login">
      <div class="nw-login-card">

        <div class="nw-login-header">
          <div class="mini-brand">
            <img src="{{ asset('assets\img\Logo Option 3 (1).png') }}" alt="NEXTWARE" />
            <div>
              <h3>Sign in to NEXTWARE</h3>
              <p>Secure access for internal users</p>
            </div>
          </div>

          <!-- Alerts (keep your Laravel session logic) -->
          @if (session('statusLogin'))
            <div class="alert alert-warning" role="alert">
              <strong>{{ session('statusLogin') }}</strong>
            </div>
          @elseif(session('statusLogout'))
            <div class="alert alert-success" role="alert">
              <strong>{{ session('statusLogout') }}</strong>
            </div>
          @endif
        </div>

        <!-- Login Form -->
        <form action="{{ url('auth/login') }}" method="POST" autocomplete="off" aria-label="Login form">
          @csrf

          <div class="nw-field nw-input">
            <i class="fas fa-user"></i>
            <input type="text" class="form-control" placeholder="Username / Email" name="email" required />
          </div>

          <div class="nw-field nw-input">
            <i class="fas fa-lock"></i>
            <input type="password" class="form-control" placeholder="Password" name="password" required />
          </div>

          <div class="nw-actions">
            <div class="form-check m-0">
              <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
              <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>
            <a href="#" class="small">Forgot password?</a>
          </div>

          <button type="submit" class="btn btn-nw btn-nw-primary w-100">
            <i class="fas fa-sign-in-alt"></i>
            Log In
          </button>
        </form>

        <div class="nw-separator" role="separator" aria-label="Alternative sign-in">
          <hr><span>OR</span><hr>
        </div>

        <!-- SSO Login (Microsoft) -->
        <form action="{{ url('auth/microsoft') }}" method="GET" class="mb-2">
          @csrf
          <button type="submit" class="btn btn-nw btn-nw-outline w-100" aria-label="Continue with Microsoft">
            <i class="fab fa-windows" aria-hidden="true"></i>
            Continue with Microsoft
          </button>
        </form>

        <!-- Optional: Request Access -->
        <button type="button" class="btn btn-nw btn-nw-outline w-100" data-bs-toggle="modal" data-bs-target="#requestAccessModal">
          <i class="fas fa-user-plus" aria-hidden="true"></i>
          Request Access
        </button>

        <!-- Request Access Modal (route unchanged) -->
        <div class="modal fade" id="requestAccessModal" tabindex="-1" aria-labelledby="requestAccessModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
              <div class="modal-header">
                <h5 class="modal-title" id="requestAccessModalLabel">Request Access</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="requestAccessForm" action="{{ url('request/access') }}" method="POST">
                  @csrf
                  <div class="mb-3">
                    <label for="inputName" class="form-label">Name</label>
                    <input type="text" class="form-control" id="inputName" name="name" required style="padding-left: 14px;" />
                  </div>
                  <div class="mb-3">
                    <label for="inputEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="inputEmail" name="email" required style="padding-left: 14px;" />
                  </div>
                  <div class="mb-3">
                    <label for="inputDepartment" class="form-label">Department</label>
                    <input type="text" class="form-control" id="inputDepartment" name="department" required style="padding-left: 14px;" />
                  </div>
                  <div class="mb-3">
                    <label for="inputPlant" class="form-label">Plant</label>
                    <input type="text" class="form-control" id="inputPlant" name="plant" required style="padding-left: 14px;" />
                  </div>
                  <div class="mb-3">
                    <label for="inputPurpose" class="form-label">Purpose</label>
                    <textarea class="form-control" id="inputPurpose" name="purpose" rows="3" required style="padding-left: 14px; height: auto;"></textarea>
                  </div>
                  <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <div class="nw-foot text-center">
          <div class="mb-1">&copy; {{ now()->year }} PT Mitsubishi Krama Yudha Motors and Manufacturing</div>
          <div>Integrated today. Ready for tomorrow.</div>
        </div>

      </div>
    </aside>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
