<?php
// all_users.php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
if (!$user['is_admin']) {
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>All Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://unpkg.com/core-js-bundle@3.19.3/minified.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
    <div class="container">
      <a class="navbar-brand" href="index.php">App</a>
      <div class="ms-auto">
        <button id="addUserBtn" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addUserModal">Add user</button>
        <button id="logoutBtn" class="btn btn-danger">Logout</button>
      </div>
    </div>
  </nav>

  <main class="container">
    <div class="mb-3">
      <input id="searchInput" class="form-control" placeholder="Search users (username, firstname, lastname)">
    </div>
    <div class="table-responsive">
      <table class="table table-striped" id="usersTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>First name</th>
            <th>Last name</th>
            <th>Is Admin</th>
            <th>Date added</th>
          </tr>
        </thead>
        <tbody id="usersBody"></tbody>
      </table>
    </div>
  </main>

  <!-- Add User Modal -->
  <div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="addUserForm" class="needs-validation">
          <div class="modal-header">
            <h5 class="modal-title">Add user</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-2">
              <label class="form-label">Username</label>
              <input id="add_username" name="username" class="form-control" required>
            </div>
            <div class="mb-2">
              <label class="form-label">First name</label>
              <input id="add_firstname" name="firstname" class="form-control" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Last name</label>
              <input id="add_lastname" name="lastname" class="form-control" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Password</label>
              <input id="add_password" name="password" type="password" class="form-control" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Confirm password</label>
              <input id="add_confirm" type="password" class="form-control" required>
            </div>
            <div class="form-check">
              <input id="add_is_admin" name="is_admin" class="form-check-input" type="checkbox" value="1">
              <label class="form-check-label">Is Admin</label>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
            <button class="btn btn-primary" type="submit">Add user</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const usersBody = document.getElementById('usersBody');
const searchInput = document.getElementById('searchInput');

async function fetchUsers() {
    try {
        const res = await fetch('api.php?action=get_users');
        const json = await res.json();
        
        if (!json.success) {
            Swal.fire('Error', json.message || 'Unable to fetch users', 'error');
            return [];
        }
        return json.users;
    } catch (error) {
        console.error('Error fetching users:', error);
        Swal.fire('Error', 'Failed to fetch users', 'error');
        return [];
    }
}

function renderRows(users) {
  usersBody.innerHTML = '';
  for (const u of users) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${u.id}</td>
      <td>${escapeHtml(u.username)}</td>
      <td>${escapeHtml(u.firstname)}</td>
      <td>${escapeHtml(u.lastname)}</td>
      <td>${parseInt(u.is_admin) === 1 ? 'Yes' : 'No'}</td>
      <td>${u.date_added}</td>
    `;
    usersBody.appendChild(tr);
  }
}

function escapeHtml(s) {
  if (!s) return '';
  return s.replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;');
}

let cachedUsers = [];

async function loadAndShow() {
    try {
        cachedUsers = await fetchUsers();
        if (Array.isArray(cachedUsers)) {
            renderRows(cachedUsers);
        } else {
            console.error('Invalid users data:', cachedUsers);
            Swal.fire('Error', 'Invalid data received from server', 'error');
        }
    } catch (error) {
        console.error('Error in loadAndShow:', error);
        Swal.fire('Error', 'Failed to load users', 'error');
    }
}

searchInput.addEventListener('input', (e) => {
  const q = e.target.value.trim().toLowerCase();
  if (!q) {
    renderRows(cachedUsers);
    return;
  }
  const filtered = cachedUsers.filter(u =>
    (u.username + ' ' + u.firstname + ' ' + u.lastname).toLowerCase().includes(q)
  );
  renderRows(filtered);
});

// add user form handling
document.getElementById('addUserForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    try {
        const username = document.getElementById('add_username').value.trim();
        const firstname = document.getElementById('add_firstname').value.trim();
        const lastname = document.getElementById('add_lastname').value.trim();
        const password = document.getElementById('add_password').value;
        const confirm = document.getElementById('add_confirm').value;
        const is_admin = document.getElementById('add_is_admin').checked ? '1' : '0';

        // Validation
        if (!username || !firstname || !lastname || !password || !confirm) {
            Swal.fire('Error', 'All fields are required', 'warning');
            return;
        }

        if (password.length < 8) {
            Swal.fire('Error', 'Password must be at least 8 characters', 'warning');
            return;
        }

        if (password !== confirm) {
            Swal.fire('Error', 'Passwords do not match', 'warning');
            return;
        }

        // Check username availability
        const exists = await checkUsernameExists(username);
        if (exists) {
            Swal.fire('Error', 'Username already exists', 'warning');
            return;
        }

        // Submit form
        const form = new FormData();
        form.append('action', 'add_user');
        form.append('username', username);
        form.append('firstname', firstname);
        form.append('lastname', lastname);
        form.append('password', password);
        form.append('is_admin', is_admin);

        const res = await fetch('api.php', {
            method: 'POST',
            body: form
        });

        const json = await res.json();

        if (json.success) {
            Swal.fire('Success', 'User added successfully', 'success');
            document.getElementById('addUserForm').reset();
            const modalEl = document.getElementById('addUserModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();
            await loadAndShow(); // Refresh the users list
        } else {
            throw new Error(json.message || 'Failed to add user');
        }
    } catch (error) {
        console.error('Error adding user:', error);
        Swal.fire('Error', error.message || 'Failed to add user', 'error');
    }
});

document.getElementById('logoutBtn').addEventListener('click', async () => {
  const fm = new FormData();
  fm.append('action', 'logout');
  await fetch('api.php', { method: 'POST', body: fm });
  window.location.href = 'login.php';
});

async function checkUsernameExists(username) {
    try {
        const res = await fetch(`api.php?action=check_username&username=${encodeURIComponent(username)}`);
        const json = await res.json();
        return json.exists;
    } catch (error) {
        console.error('Error checking username:', error);
        return false;
    }
}

// initial load
loadAndShow();
</script>
</body>
</html>
