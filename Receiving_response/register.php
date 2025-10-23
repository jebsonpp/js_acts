<?php
// register.php
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
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://unpkg.com/core-js-bundle@3.19.3/minified.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-7">
        <div class="card shadow-sm">
          <div class="card-body">
            <h4 class="card-title mb-4">Register</h4>
            <form id="regForm">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Username</label>
                  <input id="username" name="username" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">First name</label>
                  <input id="firstname" name="firstname" class="form-control" required>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Last name</label>
                  <input id="lastname" name="lastname" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Password</label>
                  <input id="password" name="password" type="password" class="form-control" required>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Confirm password</label>
                <input id="confirm_password" type="password" class="form-control" required>
              </div>

              <div class="d-flex justify-content-between">
                <button class="btn btn-success" type="submit">Register</button>
                <a class="btn btn-link" href="login.php">Back to login</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
async function checkUsernameExists(username) {
  try {
    const res = await fetch(`api.php?action=check_username&username=${encodeURIComponent(username)}`);
    const data = await res.json();
    return data.exists;
  } catch (error) {
    console.error('Error checking username:', error);
    return false;
  }
}

document.getElementById('regForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const username = document.getElementById('username').value.trim();
  const firstname = document.getElementById('firstname').value.trim();
  const lastname = document.getElementById('lastname').value.trim();
  const password = document.getElementById('password').value;
  const confirm = document.getElementById('confirm_password').value;

  if (!username || !firstname || !lastname || !password || !confirm) {
    Swal.fire('Empty', 'All fields are required', 'warning');
    return;
  }

  if (password.length < 8) {
    Swal.fire('Weak password', 'Password must be at least 8 characters', 'warning');
    return;
  }

  if (password !== confirm) {
    Swal.fire('Mismatch', 'Passwords do not match', 'warning');
    return;
  }

  const exists = await checkUsernameExists(username);
  if (exists) {
    Swal.fire('Taken', 'Username already exists', 'warning');
    return;
  }

  const form = new FormData();
  form.append('action', 'register');
  form.append('username', username);
  form.append('firstname', firstname);
  form.append('lastname', lastname);
  form.append('password', password);

  const res = await fetch('api.php', { method: 'POST', body: form });
  const json = await res.json();

  if (json.success) {
    Swal.fire('Success', 'Registered. You may now login.', 'success').then(() => {
      window.location.href = 'login.php';
    });
  } else {
    Swal.fire('Error', json.message || 'Registration failed', 'error');
  }
});
</script>
</body>
</html>
