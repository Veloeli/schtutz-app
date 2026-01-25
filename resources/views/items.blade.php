<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Items</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <!-- Load Laravel's JS bundle -->
    @vite(['resources/js/app.js'])
</head>

<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Item name</label>
          <input type="text" id="add-item-name" class="form-control" required>
          <div class="invalid-feedback">Please enter a name.</div>
        </div>
        <div class="mb-3">
          <label class="form-label">Amount</label>
          <input type="number" step="0.01" id="add-item-amount" class="form-control" required>
          <div class="invalid-feedback">Please enter an amount.</div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" onclick="saveNewItem()">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Item name</label>
          <input type="text" id="edit-item-name" class="form-control" required>
          <div class="invalid-feedback">Please enter a name.</div>
        </div>
        <div class="mb-3">
          <label class="form-label">Amount</label>
          <input type="number" step="0.01" id="edit-item-amount" class="form-control" required>
          <div class="invalid-feedback">Please enter an amount.</div>
        </div>
        <input type="hidden" id="edit-item-id">
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" onclick="saveEditedItem()">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Confirm Deletion</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <p class="mb-0">Are you sure you want to delete this item?</p>
        <input type="hidden" id="delete-item-id">
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger" onclick="confirmDelete()">Delete</button>
      </div>
    </div>
  </div>
</div>

<body class="bg-light">

<div class="container py-5">

    <h1 class="mb-4 text-center">Items</h1>

    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add</button>

    <table class="table table-striped table-hover mt-3">
    <thead class="table-dark">
        <tr>
            <th style="width: 40%">Name</th>
            <th style="width: 20%">Amount</th>
            <th style="width: 40%" class="text-end">Actions</th>
        </tr>
    </thead>
        <tbody id="items-table-body"></tbody>
    </table>

</div>

<!-- Bootstrap JS -->
<script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>
