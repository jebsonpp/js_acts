<?php
// Simple login
require_once 'inc/db.php';
session_start();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if($user = $res->fetch_assoc()){
        if(hash('sha256', $password) === $user['password'] && $user['status'] === 'active'){
            $_SESSION['user'] = $user;
            header('Location: ./admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials or suspended account.';
        }
    } else {
        $error = 'Invalid credentials.';
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login - OMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h3 class="card-title mb-3">Login</h3>
          <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <form method="post">
            <div class="mb-2">
              <input name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-2">
              <input name="password" type="password" class="form-control" placeholder="Password" required>
            </div>
            <button class="btn btn-primary">Login</button>
          </form>
          <hr>
      </div>
    </div>
  </div>
</div>
</body>
</html>
