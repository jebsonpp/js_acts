<?php
require_once __DIR__.'/header.php';
require_once __DIR__.'/../inc/db.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
  // basic file upload and insert
  $name = $_POST['name'];
  $price = $_POST['price'];
  $category = $_POST['category'];
  $added_by = $me['id'];
  $imgpath = null;
  
  if(isset($_FILES['image']) && $_FILES['image']['error']===0){
    // Validate image
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['image']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if(in_array($ext, $allowed)){
      $t = time();
      $target = '../uploads/'. $t . '_' . basename($_FILES['image']['name']);
      if(move_uploaded_file($_FILES['image']['tmp_name'], $target)){
        $imgpath = str_replace('../', '', $target);
      }
    }
  }
  $stmt = $mysqli->prepare("INSERT INTO products (name, price, category, image, added_by) VALUES (?,?,?,?,?)");
  $stmt->bind_param('sdssi', $name, $price, $category, $imgpath, $added_by);
  $stmt->execute();
  header('Location: products.php');
  exit;
}

//delete handler
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    // Delete image file if exists
    $result = $mysqli->query("SELECT image FROM products WHERE id = $id");
    if($img = $result->fetch_assoc()){
        if($img['image'] && file_exists(__DIR__.'/../'.$img['image'])){
            unlink(__DIR__.'/../'.$img['image']);
        }
    }
    $mysqli->query("DELETE FROM products WHERE id = $id");
    header('Location: products.php');
    exit;
}

$products = $mysqli->query("SELECT p.*, u.username as added_by_name FROM products p LEFT JOIN users u ON p.added_by=u.id ORDER BY p.date_added DESC");
?>
<style>
.product-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}
</style>

<h2>Manage Products</h2>
<form method="post" enctype="multipart/form-data" class="row g-2 mb-3">
  <div class="col-md-4"><input name="name" class="form-control" placeholder="Name" required></div>
  <div class="col-md-2"><input name="price" type="number" step="0.01" class="form-control" placeholder="Price" required></div>
  <div class="col-md-3"><select name="category" class="form-control"><option>Food</option><option>Drinks</option></select></div>
  <div class="col-md-2"><input name="image" type="file" class="form-control"></div>
  <div class="col-md-1"><button class="btn btn-primary">Add</button></div>
</form>

<table class="table table-striped">
  <thead><tr><th>Image</th><th>Name</th><th>Price</th><th>Category</th><th>Added By</th><th>Date</th><th>Action</th></tr></thead>
  <tbody>
  <?php while($p = $products->fetch_assoc()): ?>
    <tr>
      <td>
        <?php if($p['image']): ?>
          <img src="../<?php echo htmlspecialchars($p['image']) ?>" class="product-img" alt="<?php echo htmlspecialchars($p['name']) ?>">
        <?php else: ?>
          <img src="../uploads/no-image.png" class="product-img" alt="No Image">
        <?php endif; ?>
      </td>
      <td><?php echo htmlspecialchars($p['name']) ?></td>
      <td><?php echo number_format($p['price'],2) ?></td>
      <td><?php echo htmlspecialchars($p['category']) ?></td>
      <td><?php echo htmlspecialchars($p['added_by_name']) ?></td>
      <td><?php echo $p['date_added'] ?></td>
      <td>
        <button class="btn btn-danger btn-sm" onclick="deleteProduct(<?php echo $p['id'] ?>)">Delete</button>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>

<script>
function deleteProduct(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This product will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?delete=${id}`;
        }
    })
}
</script>

<?php require_once __DIR__.'/footer.php'; ?>
