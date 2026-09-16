const CHECKBOX_FIELDS = [
    'has_filter', 'has_co2', 'has_fertilizer', 'has_lighting', 'has_substrate', 'has_heater', 'is_cycled',
];

const NUMBER_FIELDS = ['volume_gallons'];

const TEXT_FIELDS = [
    'custom_name', 'tank_type', 'salinity_type', 'tank_shape',
    'filter_type', 'co2_type', 'fertilizer_routine',
    'lighting_type', 'lighting_schedule', 'lighting_intensity',
    'substrate_type', 'heater_type', 'cycle_start_date', 'notes',
];

const tankList = document.getElementById('tank-list');
const tankForm = document.getElementById('tank-form');
const tankFormTitle = document.getElementById('tank-form-title');
const tankFormError = document.getElementById('tank-form-error');
const tankFormSubmit = document.getElementById('tank-form-submit');
const tankFormCancel = document.getElementById('tank-form-cancel');
const tankIdField = document.getElementById('tank-id');

function showFormError(message) {
    tankFormError.textContent = message;
    tankFormError.hidden = false;
}

function clearFormError() {
    tankFormError.hidden = true;
    tankFormError.textContent = '';
}

function resetForm() {
    tankForm.reset();
    tankIdField.value = '';
    tankFormTitle.textContent = 'Add a Tank';
    tankFormSubmit.textContent = 'Add Tank';
    tankFormCancel.hidden = true;
    clearFormError();
}

function fillForm(tank) {
    tankIdField.value = tank.id;

    for (const field of TEXT_FIELDS) {
        document.getElementById(field).value = tank[field] ?? '';
    }
    for (const field of NUMBER_FIELDS) {
        document.getElementById(field).value = tank[field] ?? '';
    }
    for (const field of CHECKBOX_FIELDS) {
        document.getElementById(field).checked = Boolean(Number(tank[field]));
    }

    tankFormTitle.textContent = 'Edit Tank';
    tankFormSubmit.textContent = 'Update Tank';
    tankFormCancel.hidden = false;
    clearFormError();
}

function collectFormData() {
    const data = {};

    for (const field of TEXT_FIELDS) {
        const value = document.getElementById(field).value.trim();
        data[field] = value === '' ? null : value;
    }
    for (const field of NUMBER_FIELDS) {
        const value = document.getElementById(field).value;
        data[field] = value === '' ? null : Number(value);
    }
    for (const field of CHECKBOX_FIELDS) {
        data[field] = document.getElementById(field).checked;
    }

    return data;
}

function renderTankList(tanks) {
    if (tanks.length === 0) {
        tankList.innerHTML = '<p>No tanks yet. Add one below.</p>';
        return;
    }

    tankList.innerHTML = '';

    for (const tank of tanks) {
        const card = document.createElement('div');
        card.className = 'tank-card';

        const title = document.createElement('h4');
        title.textContent = tank.custom_name;

        const meta = document.createElement('p');
        meta.textContent = [tank.tank_type, tank.salinity_type, tank.volume_gallons ? `${tank.volume_gallons} gal` : null]
            .filter(Boolean)
            .join(' • ');

        const viewTankLink = document.createElement('a');
        viewTankLink.href = `virtual_tank.php?tank_id=${tank.id}`;
        viewTankLink.className = 'btn-secondary';
        viewTankLink.textContent = 'View Virtual Tank';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.textContent = 'Edit';
        editButton.addEventListener('click', () => fillForm(tank));

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.textContent = 'Delete';
        deleteButton.addEventListener('click', () => deleteTank(tank.id));

        card.append(title, meta, viewTankLink, editButton, deleteButton);
        tankList.appendChild(card);
    }
}

async function loadTanks() {
    const response = await fetch('api/tanks.php');
    const tanks = await response.json();
    renderTankList(tanks);
}

async function deleteTank(id) {
    if (!confirm('Delete this tank?')) {
        return;
    }

    await fetch(`api/tanks.php?id=${id}`, { method: 'DELETE' });
    await loadTanks();

    if (tankIdField.value === String(id)) {
        resetForm();
    }
}

tankForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearFormError();

    const id = tankIdField.value;
    const data = collectFormData();

    const url = id ? `api/tanks.php?id=${id}` : 'api/tanks.php';
    const method = id ? 'PUT' : 'POST';

    const response = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });

    const result = await response.json();

    if (!response.ok) {
        showFormError(result.error || 'Something went wrong.');
        return;
    }

    resetForm();
    await loadTanks();
});

tankFormCancel.addEventListener('click', resetForm);

loadTanks();
