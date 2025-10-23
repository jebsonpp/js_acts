<?php
// login.php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://unpkg.com/core-js-bundle@3.19.3/minified.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h4 class="card-title mb-4">Login</h4>
            <form id="loginForm">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input id="username" name="username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input id="password" name="password" type="password" class="form-control" required>
              </div>
              <div class="d-flex justify-content-between align-items-center">
                <button class="btn btn-primary" type="submit">Login</button>
                <a href="register.php">Register</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value;

  if (!username || !password) {
    Swal.fire('Empty', 'Please fill both fields', 'warning');
    return;
  }

  const form = new FormData();
  form.append('action', 'login');
  form.append('username', username);
  form.append('password', password);

  const res = await fetch('api.php', { method: 'POST', body: form });
  const data = await res.json();

  if (data.success) {
    // redirect to index
    window.location.href = 'index.php';
  } else {
    Swal.fire('Error', data.message || 'Login failed', 'error');
  }
});
</script>
</body>
</html>
