<?php
require_once __DIR__.'/header.php';
require_once __DIR__.'/../inc/db.php';

// Only superadmin can manage
if($me['role']!=='superadmin'){
  echo '<div class="alert alert-danger">Only superadmin can manage users.</div>';
  require_once __DIR__.'/footer.php';
  exit;
}

// create admin
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['create'])){
  $username = $_POST['username'];
  $password = $_POST['password'];
  $role = 'admin';
  $hash = hash('sha256', $password);
  $stmt = $mysqli->prepare("INSERT INTO users (username, password, role) VALUES (?,?,?)");
  $stmt->bind_param('sss',$username,$hash,$role);
  $stmt->execute();
  header('Location: users.php');
  exit;
}

// suspend toggle
if(isset($_GET['suspend'])){
  $id = intval($_GET['suspend']);
  $mysqli->query("UPDATE users SET status='suspended' WHERE id=$id");
  header('Location: users.php');
  exit;
}
if(isset($_GET['activate'])){
  $id = intval($_GET['activate']);
  $mysqli->query("UPDATE users SET status='active' WHERE id=$id");
  header('Location: users.php');
  exit;
}

$users = $mysqli->query("SELECT * FROM users ORDER BY date_added DESC");
?>
<h2>Manage Admins</h2>
<form method="post" class="row g-2 mb-3">
  <div class="col-md-4"><input name="username" class="form-control" placeholder="Username" required></div>
  <div class="col-md-4"><input name="password" type="password" class="form-control" placeholder="Password" required></div>
  <div class="col-md-2"><button name="create" class="btn btn-primary">Create Admin</button></div>
</form>

<table class="table">
<thead><tr><th>Username</th><th>Role</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
<tbody>
<?php while($u=$users->fetch_assoc()): ?>
<tr>
  <td><?php echo htmlspecialchars($u['username']) ?></td>
  <td><?php echo $u['role'] ?></td>
  <td><?php echo $u['status'] ?></td>
  <td><?php echo $u['date_added'] ?></td>
  <td>
    <?php if($u['status']==='active'): ?>
      <a href="?suspend=<?php echo $u['id'] ?>" class="btn btn-sm btn-warning">Suspend</a>
    <?php else: ?>
      <a href="?activate=<?php echo $u['id'] ?>" class="btn btn-sm btn-success">Activate</a>
    <?php endif; ?>
  </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<?php require_once __DIR__.'/footer.php'; ?>
