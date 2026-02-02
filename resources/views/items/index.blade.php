@extends('layouts.app')

@section('content')

<!-- Add Item Modal -->
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

<!-- Edit Item Modal -->
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

<!-- Delete Modal -->
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

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="bi bi-box-seam"></i> Items
    </h1>

    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-circle"></i> Add
    </button>
</div>

<!-- Items Table -->
<table class="table table-striped table-hover align-middle">
    <thead class="table-dark">
        <tr>
            <th style="width: 40%">Name</th>
            <th style="width: 20%">Amount</th>
            <th style="width: 40%" class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody id="items-table-body"></tbody>
</table>

@endsection
