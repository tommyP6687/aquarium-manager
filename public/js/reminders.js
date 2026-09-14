const upcomingList = document.getElementById('upcoming-reminder-list');
const completedList = document.getElementById('completed-reminder-list');
const reminderForm = document.getElementById('reminder-form');
const reminderFormTitle = document.getElementById('reminder-form-title');
const reminderFormError = document.getElementById('reminder-form-error');
const reminderFormSubmit = document.getElementById('reminder-form-submit');
const reminderFormCancel = document.getElementById('reminder-form-cancel');
const reminderIdField = document.getElementById('reminder-id');
const tankField = document.getElementById('tank-field');
const tankSelect = document.getElementById('tank_id');
const titleInput = document.getElementById('title');
const taskTypePresetSelect = document.getElementById('task_type_preset');
const customTaskTypeField = document.getElementById('custom-task-type-field');
const taskTypeInput = document.getElementById('task_type');
const dueAtInput = document.getElementById('due_at');
const repeatIntervalInput = document.getElementById('repeat_interval_days');
const notesInput = document.getElementById('reminder_notes');

function showFormError(message) {
    reminderFormError.textContent = message;
    reminderFormError.hidden = false;
}

function clearFormError() {
    reminderFormError.hidden = true;
    reminderFormError.textContent = '';
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
        taskTypePresetSelect.value = 'Feed fish';
        customTaskTypeField.hidden = true;
    }
}

function resetForm() {
    reminderForm.reset();
    reminderIdField.value = '';
    tankField.hidden = false;
    dueAtInput.value = formatDatetimeLocal(new Date());
    selectTaskType(null);

    reminderFormTitle.textContent = 'Add a Reminder';
    reminderFormSubmit.textContent = 'Add Reminder';
    reminderFormCancel.hidden = true;
    clearFormError();
}

function fillEditForm(reminder) {
    reminderIdField.value = reminder.id;
    titleInput.value = reminder.title;
    dueAtInput.value = reminder.due_at.replace(' ', 'T').slice(0, 16);
    repeatIntervalInput.value = reminder.repeat_interval_days ?? '';
    notesInput.value = reminder.notes ?? '';
    selectTaskType(reminder.task_type);

    // Tank isn't reassignable here -- hide it rather than silently ignoring a change.
    tankField.hidden = true;

    reminderFormTitle.textContent = 'Edit Reminder';
    reminderFormSubmit.textContent = 'Update Reminder';
    reminderFormCancel.hidden = false;
    clearFormError();
}

async function loadTanks() {
    const response = await fetch('api/tanks.php');
    const tanks = await response.json();

    tankSelect.innerHTML = '<option value="">Select a tank</option>';
    for (const tank of tanks) {
        const option = document.createElement('option');
        option.value = tank.id;
        option.textContent = tank.custom_name;
        tankSelect.appendChild(option);
    }
}

function renderReminderCard(reminder, { showActions }) {
    const card = document.createElement('div');
    card.className = 'tank-card';

    const title = document.createElement('h4');
    title.textContent = reminder.title;

    const meta = document.createElement('p');
    const metaParts = [reminder.task_type, `Tank: ${reminder.tank_name}`, `Due: ${reminder.due_at}`];
    if (reminder.repeat_interval_days) {
        metaParts.push(`Repeats every ${reminder.repeat_interval_days} day(s)`);
    }
    meta.textContent = metaParts.join(' • ');

    card.append(title, meta);

    if (showActions) {
        const logCheckboxLabel = document.createElement('label');
        const logCheckbox = document.createElement('input');
        logCheckbox.type = 'checkbox';
        logCheckbox.checked = true;
        logCheckboxLabel.append(logCheckbox, ' Also log this as maintenance');

        const completeButton = document.createElement('button');
        completeButton.type = 'button';
        completeButton.textContent = 'Complete';
        completeButton.addEventListener('click', () => completeReminder(reminder.id, logCheckbox.checked));

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.textContent = 'Edit';
        editButton.addEventListener('click', () => fillEditForm(reminder));

        card.append(logCheckboxLabel, completeButton, editButton);
    }

    const deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.textContent = 'Delete';
    deleteButton.addEventListener('click', () => deleteReminder(reminder.id));
    card.appendChild(deleteButton);

    return card;
}

async function loadReminders() {
    const response = await fetch('api/reminders.php');
    const reminders = await response.json();

    const upcoming = reminders.filter((reminder) => !Number(reminder.is_completed));
    const completed = reminders.filter((reminder) => Number(reminder.is_completed));

    upcomingList.innerHTML = upcoming.length === 0 ? '<p>No upcoming reminders.</p>' : '';
    for (const reminder of upcoming) {
        upcomingList.appendChild(renderReminderCard(reminder, { showActions: true }));
    }

    completedList.innerHTML = completed.length === 0 ? '<p>No completed reminders yet.</p>' : '';
    for (const reminder of completed) {
        completedList.appendChild(renderReminderCard(reminder, { showActions: false }));
    }
}

async function completeReminder(id, createMaintenanceLog) {
    await fetch(`api/reminders.php?id=${id}&action=complete`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ create_maintenance_log: createMaintenanceLog }),
    });
    await loadReminders();
}

async function deleteReminder(id) {
    if (!confirm('Delete this reminder?')) {
        return;
    }

    await fetch(`api/reminders.php?id=${id}`, { method: 'DELETE' });
    await loadReminders();
}

taskTypePresetSelect.addEventListener('change', () => {
    if (taskTypePresetSelect.value === 'Other') {
        customTaskTypeField.hidden = false;
        taskTypeInput.value = '';
    } else {
        customTaskTypeField.hidden = true;
    }
});

reminderForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearFormError();

    const editingId = reminderIdField.value;

    const taskType = taskTypePresetSelect.value === 'Other'
        ? taskTypeInput.value.trim()
        : taskTypePresetSelect.value;

    if (titleInput.value.trim() === '') {
        showFormError('Please enter a title.');
        return;
    }

    if (taskType === '') {
        showFormError('Please select or enter a task type.');
        return;
    }

    if (dueAtInput.value === '') {
        showFormError('Please choose a due date/time.');
        return;
    }

    const reminderData = {
        title: titleInput.value.trim(),
        task_type: taskType,
        due_at: dueAtInput.value.replace('T', ' '),
        repeat_interval_days: repeatIntervalInput.value || null,
        notes: notesInput.value.trim() || null,
    };

    let url = 'api/reminders.php';
    let method = 'POST';

    if (editingId) {
        url = `api/reminders.php?id=${editingId}`;
        method = 'PUT';
    } else {
        if (!tankSelect.value) {
            showFormError('Please select a tank.');
            return;
        }
        reminderData.tank_id = tankSelect.value;
    }

    const response = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(reminderData),
    });

    const result = await response.json();

    if (!response.ok) {
        showFormError(result.error || 'Something went wrong.');
        return;
    }

    resetForm();
    await loadReminders();
});

reminderFormCancel.addEventListener('click', resetForm);

(async function init() {
    await loadTanks();
    dueAtInput.value = formatDatetimeLocal(new Date());
    await loadReminders();
})();
