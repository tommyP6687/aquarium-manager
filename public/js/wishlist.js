const wishlistList = document.getElementById('wishlist-list');
const wishlistForm = document.getElementById('wishlist-form');
const wishlistFormTitle = document.getElementById('wishlist-form-title');
const wishlistFormError = document.getElementById('wishlist-form-error');
const wishlistFormSubmit = document.getElementById('wishlist-form-submit');
const wishlistFormCancel = document.getElementById('wishlist-form-cancel');
const wishlistIdField = document.getElementById('wishlist-id');
const itemNameInput = document.getElementById('item_name');
const priceSuggestions = document.getElementById('price-suggestions');
const tankSelect = document.getElementById('tank_id');
const categoryPresetSelect = document.getElementById('category_preset');
const customCategoryField = document.getElementById('custom-category-field');
const categoryInput = document.getElementById('category');
const estimatedPriceInput = document.getElementById('estimated_price');
const storeOrSourceInput = document.getElementById('store_or_source');
const priorityInput = document.getElementById('priority');
const purchaseStatusInput = document.getElementById('purchase_status');
const compatibilityNotesInput = document.getElementById('compatibility_notes');
const notesInput = document.getElementById('wishlist_notes');

let priceSearchTimer = null;
let priceSearchController = null;

function showFormError(message) {
    wishlistFormError.textContent = message;
    wishlistFormError.hidden = false;
}

function clearFormError() {
    wishlistFormError.hidden = true;
    wishlistFormError.textContent = '';
}

function presetOptionValues() {
    return Array.from(categoryPresetSelect.options).map((option) => option.value);
}

function selectCategory(category) {
    if (category && presetOptionValues().includes(category) && category !== 'Other') {
        categoryPresetSelect.value = category;
        customCategoryField.hidden = true;
    } else if (category) {
        categoryPresetSelect.value = 'Other';
        customCategoryField.hidden = false;
        categoryInput.value = category;
    } else {
        categoryPresetSelect.value = 'Fish';
        customCategoryField.hidden = true;
    }
}

function resetForm() {
    wishlistForm.reset();
    wishlistIdField.value = '';
    priceSuggestions.hidden = true;
    selectCategory(null);
    priorityInput.value = 'Medium';
    purchaseStatusInput.value = 'Wanted';

    wishlistFormTitle.textContent = 'Add a Wishlist Item';
    wishlistFormSubmit.textContent = 'Add Item';
    wishlistFormCancel.hidden = true;
    clearFormError();
}

function fillEditForm(item) {
    wishlistIdField.value = item.id;
    itemNameInput.value = item.item_name;
    tankSelect.value = item.tank_id ?? '';
    selectCategory(item.category);
    estimatedPriceInput.value = item.estimated_price ?? '';
    storeOrSourceInput.value = item.store_or_source ?? '';
    priorityInput.value = item.priority;
    purchaseStatusInput.value = item.purchase_status;
    compatibilityNotesInput.value = item.compatibility_notes ?? '';
    notesInput.value = item.notes ?? '';

    wishlistFormTitle.textContent = 'Edit Wishlist Item';
    wishlistFormSubmit.textContent = 'Update Item';
    wishlistFormCancel.hidden = false;
    clearFormError();
}

async function loadTanks() {
    const response = await fetch('api/tanks.php');
    const tanks = await response.json();

    tankSelect.innerHTML = '<option value="">None yet</option>';
    for (const tank of tanks) {
        const option = document.createElement('option');
        option.value = tank.id;
        option.textContent = tank.custom_name;
        tankSelect.appendChild(option);
    }
}

async function loadWishlist() {
    const response = await fetch('api/wishlist_items.php');
    const items = await response.json();

    if (items.length === 0) {
        wishlistList.innerHTML = '<p>No wishlist items yet. Add one below.</p>';
        return;
    }

    wishlistList.innerHTML = '';

    for (const item of items) {
        const card = document.createElement('div');
        card.className = 'tank-card';

        const title = document.createElement('h4');
        title.textContent = item.item_name;

        const meta = document.createElement('p');
        meta.textContent = [
            item.category,
            item.estimated_price ? `$${item.estimated_price}` : null,
            item.tank_name ? `Tank: ${item.tank_name}` : null,
            item.priority,
            item.purchase_status,
        ].filter(Boolean).join(' • ');

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.textContent = 'Edit';
        editButton.addEventListener('click', () => fillEditForm(item));

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.textContent = 'Delete';
        deleteButton.addEventListener('click', () => deleteWishlistItem(item.id));

        card.append(title, meta, editButton, deleteButton);
        wishlistList.appendChild(card);
    }
}

async function deleteWishlistItem(id) {
    if (!confirm('Delete this wishlist item?')) {
        return;
    }

    await fetch(`api/wishlist_items.php?id=${id}`, { method: 'DELETE' });
    await loadWishlist();
}

itemNameInput.addEventListener('input', () => {
    clearTimeout(priceSearchTimer);
    const query = itemNameInput.value.trim();

    if (query === '') {
        priceSuggestions.hidden = true;
        return;
    }

    priceSearchTimer = setTimeout(async () => {
        // Cancel any still-pending search before starting a new one -- SerpApi calls
        // are slow enough that php -S's single-threaded server can queue several of
        // these up behind each other otherwise, making later keystrokes time out.
        if (priceSearchController) {
            priceSearchController.abort();
        }
        priceSearchController = new AbortController();

        let results;
        try {
            const response = await fetch(`api/price_search.php?q=${encodeURIComponent(query)}`, {
                signal: priceSearchController.signal,
            });
            results = await response.json();
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
            priceSuggestions.hidden = true;
            return;
        }

        priceSuggestions.innerHTML = '';

        if (!Array.isArray(results) || results.length === 0) {
            priceSuggestions.hidden = true;
            return;
        }

        for (const result of results) {
            const suggestion = document.createElement('div');
            suggestion.className = 'species-suggestion';
            suggestion.textContent = [result.title, result.price ? `$${result.price}` : null, result.store]
                .filter(Boolean)
                .join(' — ');

            suggestion.addEventListener('click', () => {
                if (result.title) {
                    itemNameInput.value = result.title;
                }
                if (result.price) {
                    estimatedPriceInput.value = result.price;
                }
                if (result.store) {
                    storeOrSourceInput.value = result.store;
                }
                priceSuggestions.hidden = true;
            });

            priceSuggestions.appendChild(suggestion);
        }

        priceSuggestions.hidden = false;
    }, 350);
});

categoryPresetSelect.addEventListener('change', () => {
    if (categoryPresetSelect.value === 'Other') {
        customCategoryField.hidden = false;
        categoryInput.value = '';
    } else {
        customCategoryField.hidden = true;
    }
});

wishlistForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearFormError();

    const editingId = wishlistIdField.value;

    const category = categoryPresetSelect.value === 'Other'
        ? categoryInput.value.trim()
        : categoryPresetSelect.value;

    if (itemNameInput.value.trim() === '') {
        showFormError('Please enter an item name.');
        return;
    }

    if (category === '') {
        showFormError('Please select or enter a category.');
        return;
    }

    const itemData = {
        item_name: itemNameInput.value.trim(),
        category,
        tank_id: tankSelect.value || null,
        estimated_price: estimatedPriceInput.value || null,
        store_or_source: storeOrSourceInput.value.trim() || null,
        priority: priorityInput.value,
        purchase_status: purchaseStatusInput.value,
        compatibility_notes: compatibilityNotesInput.value.trim() || null,
        notes: notesInput.value.trim() || null,
    };

    const url = editingId ? `api/wishlist_items.php?id=${editingId}` : 'api/wishlist_items.php';
    const method = editingId ? 'PUT' : 'POST';

    const response = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(itemData),
    });

    const result = await response.json();

    if (!response.ok) {
        showFormError(result.error || 'Something went wrong.');
        return;
    }

    resetForm();
    await loadWishlist();
});

wishlistFormCancel.addEventListener('click', resetForm);

(async function init() {
    await loadTanks();
    await loadWishlist();
})();
