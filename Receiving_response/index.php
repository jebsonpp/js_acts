<?php
// index.php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="#">App</a>
      <div class="ms-auto">
        <a class="btn btn-outline-secondary me-2" href="all_users.php">All Users</a>
        <button id="logoutBtn" class="btn btn-danger">Logout</button>
      </div>
    </div>
  </nav>

  <main class="container py-5">
    <div class="card">
      <div class="card-body">
        <h3>Hello there <?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>.</h3>
        <p>First name: <?php echo htmlspecialchars($user['firstname'], ENT_QUOTES); ?></p>
        <p>Last name: <?php echo htmlspecialchars($user['lastname'], ENT_QUOTES); ?></p>
        <p>Admin: <?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></p>
      </div>
    </div>
  </main>

<script>
document.getElementById('logoutBtn').addEventListener('click', async () => {
  const fm = new FormData();
  fm.append('action', 'logout');
  await fetch('api.php', { method: 'POST', body: fm });
  window.location.href = 'login.php';
});
</script>
</body>
</html>
