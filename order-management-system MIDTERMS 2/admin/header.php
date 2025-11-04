<?php
require_once __DIR__.'/../inc/auth.php';
if(!is_logged_in()){
    header('Location: ../index.php');
    exit;
}
$me = current_user();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>OMS - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">OMS</a>
    <div class="navbar-nav me-auto">
      <a href="products.php" class="btn btn-outline-primary btn-sm mx-1">Products</a>
      <a href="users.php" class="btn btn-outline-primary btn-sm mx-1">Users</a>
      <a href="reports.php" class="btn btn-outline-primary btn-sm mx-1">Reports</a>
    </div>
    <div class="ms-auto">
      <span class="me-3">Hello, <?php echo htmlspecialchars($me['username']) ?></span>
      <a class="btn btn-outline-secondary btn-sm" href="../api/logout.php">Logout</a>
    </div>
  </div>
</nav>
<div class="container my-4">
<div class="row">
  <div class="col-md-9">
