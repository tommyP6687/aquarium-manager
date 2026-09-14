const maintenanceLogList = document.getElementById('maintenance-log-list');
const maintenanceLogForm = document.getElementById('maintenance-log-form');
const maintenanceLogFormTitle = document.getElementById('maintenance-log-form-title');
const maintenanceLogFormError = document.getElementById('maintenance-log-form-error');
const maintenanceLogFormSubmit = document.getElementById('maintenance-log-form-submit');
const maintenanceLogFormCancel = document.getElementById('maintenance-log-form-cancel');
const maintenanceLogIdField = document.getElementById('maintenance-log-id');
const tankField = document.getElementById('tank-field');
const tankSelect = document.getElementById('tank_id');
const performedAtInput = document.getElementById('performed_at');
const taskTypePresetSelect = document.getElementById('task_type_preset');
const customTaskTypeField = document.getElementById('custom-task-type-field');
const taskTypeInput = document.getElementById('task_type');
const detailsInput = document.getElementById('details');
const notesInput = document.getElementById('maintenance_notes');

let tanksById = new Map();

function showFormError(message) {
    maintenanceLogFormError.textContent = message;
    maintenanceLogFormError.hidden = false;
}

function clearFormError() {
    maintenanceLogFormError.hidden = true;
    maintenanceLogFormError.textContent = '';
}

function formatDatetimeLocal(date) {
    const pad = (n) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function presetOptionValues() {
    return Array.from(taskTypePresetSelect.options).map((option) => option.value);
}

function selectTaskType(taskType) {
    if (taskType && presetOptionValues().includes(taskType) && taskType !== 'Other') {
        taskTypePresetSelect.value = taskType;
        customTaskTypeField.hidden = true;
    } else if (taskType) {
        taskTypePresetSelect.value = 'Other';
        customTaskTypeField.hidden = false;
        taskTypeInput.value = taskType;
    } else {
        taskTypePresetSelect.value = 'Feeding';
        customTaskTypeField.hidden = true;
    }
}

function resetForm() {
    maintenanceLogForm.reset();
    maintenanceLogIdField.value = '';
    tankField.hidden = false;
    performedAtInput.value = formatDatetimeLocal(new Date());
    selectTaskType(null);

    maintenanceLogFormTitle.textContent = 'Log a Task';
    maintenanceLogFormSubmit.textContent = 'Log Task';
    maintenanceLogFormCancel.hidden = true;
    clearFormError();
}

function fillEditForm(log) {
    maintenanceLogIdField.value = log.id;
    performedAtInput.value = log.performed_at.replace(' ', 'T').slice(0, 16);
    selectTaskType(log.task_type);
    detailsInput.value = log.details ?? '';
    notesInput.value = log.notes ?? '';

    // Tank isn't reassignable here -- hide it rather than silently ignoring a change.
    tankField.hidden = true;

    maintenanceLogFormTitle.textContent = 'Edit Task';
    maintenanceLogFormSubmit.textContent = 'Update Task';
    maintenanceLogFormCancel.hidden = false;
    clearFormError();
}

async function loadTanks() {
    const response = await fetch('api/tanks.php');
    const tanks = await response.json();

    tanksById = new Map(tanks.map((tank) => [String(tank.id), tank]));

    tankSelect.innerHTML = '<option value="">Select a tank</option>';
    for (const tank of tanks) {
        const option = document.createElement('option');
        option.value = tank.id;
        option.textContent = tank.custom_name;
        tankSelect.appendChild(option);
    }
}

async function loadMaintenanceLogs() {
    const response = await fetch('api/maintenance_logs.php');
    const logs = await response.json();

    if (logs.length === 0) {
        maintenanceLogList.innerHTML = '<p>No maintenance logs yet. Log one below.</p>';
        return;
    }

    maintenanceLogList.innerHTML = '';

    for (const log of logs) {
        const card = document.createElement('div');
        card.className = 'tank-card';

        const title = document.createElement('h4');
        title.textContent = log.task_type;

        const meta = document.createElement('p');
        meta.textContent = [`Tank: ${log.tank_name}`, log.performed_at, log.details].filter(Boolean).join(' • ');

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.textContent = 'Edit';
        editButton.addEventListener('click', () => fillEditForm(log));

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.textContent = 'Delete';
        deleteButton.addEventListener('click', () => deleteMaintenanceLog(log.id));

        card.append(title, meta, editButton, deleteButton);
        maintenanceLogList.appendChild(card);
    }
}

async function deleteMaintenanceLog(id) {
    if (!confirm('Delete this log?')) {
        return;
    }

    await fetch(`api/maintenance_logs.php?id=${id}`, { method: 'DELETE' });
    await loadMaintenanceLogs();
}

taskTypePresetSelect.addEventListener('change', () => {
    if (taskTypePresetSelect.value === 'Other') {
        customTaskTypeField.hidden = false;
        taskTypeInput.value = '';
    } else {
        customTaskTypeField.hidden = true;
    }
});

maintenanceLogForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearFormError();

    const editingId = maintenanceLogIdField.value;

    const taskType = taskTypePresetSelect.value === 'Other'
        ? taskTypeInput.value.trim()
        : taskTypePresetSelect.value;

    if (taskType === '') {
        showFormError('Please select or enter a task type.');
        return;
    }

    const logData = {
        performed_at: performedAtInput.value.replace('T', ' '),
        task_type: taskType,
        details: detailsInput.value.trim() || null,
        notes: notesInput.value.trim() || null,
    };

    let url = 'api/maintenance_logs.php';
    let method = 'POST';

    if (editingId) {
        url = `api/maintenance_logs.php?id=${editingId}`;
        method = 'PUT';
    } else {
        if (!tankSelect.value) {
            showFormError('Please select a tank.');
            return;
        }
        logData.tank_id = tankSelect.value;
    }

    const response = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(logData),
    });

    const result = await response.json();

    if (!response.ok) {
        showFormError(result.error || 'Something went wrong.');
        return;
    }

    resetForm();
    await loadMaintenanceLogs();
});

maintenanceLogFormCancel.addEventListener('click', resetForm);

(async function init() {
    await loadTanks();
    performedAtInput.value = formatDatetimeLocal(new Date());
    await loadMaintenanceLogs();
})();
