// Bootstrap modal instances
let addModal, editModal, deleteModal;

document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM READY");

    addModal = new bootstrap.Modal(document.getElementById("addModal"));
    editModal = new bootstrap.Modal(document.getElementById("editModal"));
    deleteModal = new bootstrap.Modal(document.getElementById("deleteModal"));

    loadItems();
});

document.getElementById("addModal").addEventListener("show.bs.modal", () => {
    const name = document.getElementById("add-item-name");
    const amount = document.getElementById("add-item-amount");

    name.value = "";
    amount.value = "";

    name.classList.remove("is-invalid");
    amount.classList.remove("is-invalid");
});

document.getElementById("editModal").addEventListener("show.bs.modal", () => {
    document.getElementById("edit-item-name").classList.remove("is-invalid");
});

async function loadItems() {
    try {
        const response = await fetch("http://127.0.0.1:8000/api/items");
        const items = await response.json();

        const tbody = document.getElementById("items-table-body");
        tbody.innerHTML = "";

        items.forEach(item => {
            const tr = document.createElement("tr");

        tr.innerHTML = `
            <td>${item.name}</td>
            <td>${parseFloat(item.amount).toFixed(2)}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-warning me-2" onclick="editItem(${item.id}, '${item.name}', ${item.amount})">
                    Edit
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteItem(${item.id})">
                    Delete
                </button>
            </td>
        `;

            tbody.appendChild(tr);
        });

    } catch (error) {
        console.error("Error loading items:", error);
    }
}

// -----------------------------
// Save new item (from Add modal)
// -----------------------------
async function saveNewItem() {
    if (!validateAddForm()) return;

    const name = document.getElementById("add-item-name").value.trim();
    const amount = document.getElementById("add-item-amount").value.trim();

    const response = await fetch("http://127.0.0.1:8000/api/items", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify({ name, amount })
    });

    if (response.ok) {
        addModal.hide();
        loadItems();
    }
}

// -----------------------------
// Open Edit modal
// -----------------------------
function editItem(id, name, amount) {
    document.getElementById("edit-item-id").value = id;
    document.getElementById("edit-item-name").value = name;
    document.getElementById("edit-item-amount").value = amount;

    editModal.show();
}

// -----------------------------
// Save edited item
// -----------------------------
async function saveEditedItem() {
    if (!validateEditForm()) return;

    const id = document.getElementById("edit-item-id").value;
    const name = document.getElementById("edit-item-name").value.trim();
    const amount = document.getElementById("edit-item-amount").value.trim();

    const response = await fetch(`http://127.0.0.1:8000/api/items/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify({ name, amount })
    });

    if (response.ok) {
        editModal.hide();
        loadItems();
    }
}

// -----------------------------
// Delete item
// -----------------------------
function deleteItem(id) {
    document.getElementById("delete-item-id").value = id;
    deleteModal.show();
}

async function confirmDelete() {
    const id = document.getElementById("delete-item-id").value;

    await fetch(`http://127.0.0.1:8000/api/items/${id}`, {
        method: "DELETE",
        headers: { "Accept": "application/json" }
    });

    deleteModal.hide();
    loadItems();
}

function validateAddForm() {
    const name = document.getElementById("add-item-name");
    const amount = document.getElementById("add-item-amount");

    let valid = true;

    if (!name.value.trim()) {
        name.classList.add("is-invalid");
        valid = false;
    } else {
        name.classList.remove("is-invalid");
    }

    if (!amount.value.trim() || isNaN(amount.value)) {
        amount.classList.add("is-invalid");
        valid = false;
    } else {
        amount.classList.remove("is-invalid");
    }

    return valid;
}

function validateEditForm() {
    const name = document.getElementById("edit-item-name");
    const amount = document.getElementById("edit-item-amount");

    let valid = true;

    if (!name.value.trim()) {
        name.classList.add("is-invalid");
        valid = false;
    } else {
        name.classList.remove("is-invalid");
    }

    if (!amount.value.trim() || isNaN(amount.value)) {
        amount.classList.add("is-invalid");
        valid = false;
    } else {
        amount.classList.remove("is-invalid");
    }

    return valid;
}

// Expose globally
window.editItem = editItem;
window.deleteItem = deleteItem;
window.saveNewItem = saveNewItem;
window.saveEditedItem = saveEditedItem;
window.deleteItem = deleteItem;
window.confirmDelete = confirmDelete;

