<?php
require_once __DIR__.'/header.php';
require_once __DIR__.'/../inc/db.php';
?>
<h2>Order Reports</h2>
<form id="filterForm" class="row g-2 mb-3">
  <div class="col-md-3">
    <label class="form-label">Start Date</label>
    <input type="date" name="start" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label">End Date</label>
    <input type="date" name="end" class="form-control">
  </div>
  <div class="col-md-2">
    <label class="form-label">&nbsp;</label>
    <button type="button" id="filterBtn" class="btn btn-primary d-block">Filter</button>
  </div>
  <div class="col-md-2">
    <label class="form-label">&nbsp;</label>
    <button type="button" id="printBtn" class="btn btn-secondary d-block">Generate PDF</button>
  </div>
</form>

<div class="card">
  <div class="card-body">
    <table class="table table-bordered" id="reportTable">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Items</th>
          <th>Total (PHP)</th>
          <th>Processed By</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
        <tr>
          <td colspan="2" class="text-end fw-bold">Grand Total:</td>
          <td class="fw-bold" id="grandTotal">0.00</td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
async function fetchReports() {
  const form = document.getElementById('filterForm');
  const params = new URLSearchParams(new FormData(form));
  
  try {
    const res = await fetch(`../api/reports.php?${params.toString()}`);
    const data = await res.json();
    
    if (!data.success) throw new Error(data.message);
    
    const tbody = document.querySelector('#reportTable tbody');
    tbody.innerHTML = '';
    
    data.rows.forEach(row => {
      const tr = document.createElement('tr');
      const items = row.items_json.map(i => 
        `${i.name} x${i.qty} (${parseFloat(i.price).toFixed(2)} PHP)`
      ).join('<br>');
      
      tr.innerHTML = `
        <td>${row.id}</td>
        <td>${items}</td>
        <td>${parseFloat(row.total).toFixed(2)}</td>
        <td>${row.username}</td>
        <td>${new Date(row.date_added).toLocaleDateString()}</td>
      `;
      tbody.appendChild(tr);
    });
    
    document.getElementById('grandTotal').textContent = 
      parseFloat(data.total_sum).toFixed(2);
      
  } catch (error) {
    Swal.fire('Error', error.message, 'error');
  }
}

document.getElementById('filterBtn').addEventListener('click', fetchReports);

document.getElementById('printBtn').addEventListener('click', async () => {
  const element = document.getElementById('reportTable');
  const opt = {
    margin: 1,
    filename: 'order-report.pdf',
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: 'in', format: 'letter', orientation: 'landscape' }
  };
  
  try {
    await html2pdf().set(opt).from(element).save();
  } catch (error) {
    Swal.fire('Error', 'Failed to generate PDF', 'error');
  }
});

// Load initial data
fetchReports();
</script>

<?php require_once __DIR__.'/footer.php'; ?>
