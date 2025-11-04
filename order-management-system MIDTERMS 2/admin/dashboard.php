<?php
require_once __DIR__.'/header.php';
require_once __DIR__.'/../inc/db.php';
?>
<!-- POS UI: show up to 8 products, 2 categories -->
<h1 class="mb-3">Menu</h1>
<div class="card mb-4 p-3">
  <div class="row" id="menu">
    <?php
    $sql = "SELECT * FROM products ORDER BY date_added DESC LIMIT 8";
    $res = $mysqli->query($sql);
    while($p = $res->fetch_assoc()):
    ?>
    <div class="col-md-3">
      <div class="card mb-3">
        <?php if($p['image'] && file_exists(__DIR__ . '/../' . $p['image'])): ?>
          <img src="../<?php echo htmlspecialchars($p['image']) ?>" class="card-img-top" style="height:140px;object-fit:cover;">
        <?php else: ?>
          <div style="height:140px;background:#eee;display:flex;align-items:center;justify-content:center">No image</div>
        <?php endif; ?>
        <div class="card-body">
          <h5><?php echo htmlspecialchars($p['name']) ?></h5>
          <p><?php echo number_format($p['price'],2) ?> PHP</p>
          <input type="number" min="1" class="form-control qty mb-2" data-id="<?php echo $p['id'] ?>" placeholder="Enter how many">
          <button class="btn btn-primary add-to-cart" data-id="<?php echo $p['id'] ?>" data-name="<?php echo htmlspecialchars($p['name']) ?>" data-price="<?php echo $p['price'] ?>">Add to order</button>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
</div>

<div class="card p-3">
  <h3>Ordered Items</h3>
  <ul id="cart-list" class="list-group mb-3"></ul>
  <div class="mb-2">
    <label>Total:</label>
    <input id="total" class="form-control" readonly>
  </div>
  <button id="payBtn" class="btn btn-success">Pay!</button>
</div>

<script>
const cart = [];
function renderCart(){
  const ul = document.getElementById('cart-list');
  ul.innerHTML = '';
  let total = 0;
  cart.forEach((it, idx)=>{
    const li = document.createElement('li');
    li.className='list-group-item d-flex justify-content-between align-items-center';
    li.innerHTML = `<div>${it.name} x ${it.qty} <small class="text-muted">(${it.price} PHP)</small></div><div>${(it.price*it.qty).toFixed(2)} PHP <button class="btn btn-sm btn-danger ms-2" onclick="remove(${idx})">x</button></div>`;
    ul.appendChild(li);
    total += it.price*it.qty;
  });
  document.getElementById('total').value = total.toFixed(2);
}
function remove(idx){ cart.splice(idx,1); renderCart(); }
document.querySelectorAll('.add-to-cart').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const id = btn.dataset.id;
    const name = btn.dataset.name;
    const price = parseFloat(btn.dataset.price);
    const qtyInput = btn.parentElement.querySelector('.qty');
    const qty = parseInt(qtyInput.value) || 1;
    cart.push({id,name,price,qty});
    renderCart();
  });
});
document.getElementById('payBtn').addEventListener('click', function() {
  const totalElement = document.getElementById('total');
  const total = parseFloat(totalElement.value || 0);

  if (cart.length === 0) {
    Swal.fire('Empty Cart', 'Please add items before paying.', 'warning');
    return;
  }

  if (isNaN(total) || total <= 0) {
    Swal.fire('Invalid Total', 'Total amount must be greater than zero.', 'warning');
    return;
  }

  fetch('../api/orders.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      cart: cart,
      total: total
    })
  })
  .then(async (response) => {
    const text = await response.text();

    try {
      const res = JSON.parse(text);

      if (!response.ok || !res.success) {
        throw new Error(res.message || 'Failed to save order.');
      }

      Swal.fire('Order Saved!', 'Order ID: ' + res.id, 'success');
      cart.splice(0, cart.length);
      renderCart();

    } catch (err) {
      // If parsing fails, this means HTML or PHP error leaked into the response
      console.error('Non-JSON Response:', text);
      Swal.fire('Server Error', 'Unexpected response from server. Check console for details.', 'error');
    }
  })
  .catch((error) => {
    console.error('Fetch Error:', error);
    Swal.fire('Error', 'Failed to process order: ' + error.message, 'error');
  });
});
</script>

<?php require_once __DIR__.'/footer.php'; ?>
