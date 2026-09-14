const FRESHWATER_PARAMETERS = ['Temperature', 'pH', 'Ammonia', 'Nitrite', 'Nitrate', 'GH', 'KH', 'TDS', 'Phosphate'];
const SALTWATER_PARAMETERS = ['Salinity', 'Calcium', 'Alkalinity', 'Magnesium', 'Phosphate', 'Nitrate', 'Temperature', 'pH'];

const PARAMETER_UNIT_DEFAULTS = {
    Temperature: '°F',
    pH: '',
    Ammonia: 'ppm',
    Nitrite: 'ppm',
    Nitrate: 'ppm',
    GH: 'dGH',
    KH: 'dKH',
    TDS: 'ppm',
    Phosphate: 'ppm',
    Salinity: 'ppt',
    Calcium: 'ppm',
    Alkalinity: 'dKH',
    Magnesium: 'ppm',
};

const waterTestList = document.getElementById('water-test-list');
const waterTestForm = document.getElementById('water-test-form');
const waterTestFormTitle = document.getElementById('water-test-form-title');
const waterTestFormError = document.getElementById('water-test-form-error');
const waterTestFormSubmit = document.getElementById('water-test-form-submit');
const waterTestFormCancel = document.getElementById('water-test-form-cancel');
const waterTestIdField = document.getElementById('water-test-id');
const tankField = document.getElementById('tank-field');
const tankSelect = document.getElementById('tank_id');
const testedAtInput = document.getElementById('tested_at');
const parameterPresetSelect = document.getElementById('parameter_preset');
const customParameterField = document.getElementById('custom-parameter-field');
const parameterNameInput = document.getElementById('parameter_name');
const valueInput = document.getElementById('value');
const unitInput = document.getElementById('unit');
const notesInput = document.getElementById('water_test_notes');

let tanksById = new Map();

function showFormError(message) {
    waterTestFormError.textContent = message;
    waterTestFormError.hidden = false;
}

function clearFormError() {
    waterTestFormError.hidden = true;
    waterTestFormError.textContent = '';
}

function formatDatetimeLocal(date) {
    const pad = (n) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function parametersForSalinity(salinityType) {
    const normalized = (salinityType || '').toLowerCase();

    if (normalized.includes('salt')) {
        return SALTWATER_PARAMETERS;
    }
    if (normalized.includes('fresh')) {
        return FRESHWATER_PARAMETERS;
    }

    return [...new Set([...FRESHWATER_PARAMETERS, ...SALTWATER_PARAMETERS])];
}

function populateParameterPreset(salinityType, selected) {
    const parameters = parametersForSalinity(salinityType);

    parameterPresetSelect.innerHTML = '';
    for (const parameter of parameters) {
        const option = document.createElement('option');
        option.value = parameter;
        option.textContent = parameter;
        parameterPresetSelect.appendChild(option);
    }
    const otherOption = document.createElement('option');
    otherOption.value = 'Other';
    otherOption.textContent = 'Other';
    parameterPresetSelect.appendChild(otherOption);

    if (selected && parameters.includes(selected)) {
        parameterPresetSelect.value = selected;
        customParameterField.hidden = true;
    } else if (selected) {
        parameterPresetSelect.value = 'Other';
        customParameterField.hidden = false;
        parameterNameInput.value = selected;
    } else {
        customParameterField.hidden = true;
    }
}

function resetForm() {
    waterTestForm.reset();
    waterTestIdField.value = '';
    tankField.hidden = false;
    testedAtInput.value = formatDatetimeLocal(new Date());

    const firstTank = tanksById.values().next().value;
    populateParameterPreset(firstTank ? firstTank.salinity_type : null);

    waterTestFormTitle.textContent = 'Log a Reading';
    waterTestFormSubmit.textContent = 'Log Reading';
    waterTestFormCancel.hidden = true;
    clearFormError();
}

function fillEditForm(reading) {
    waterTestIdField.value = reading.id;
    testedAtInput.value = reading.tested_at.replace(' ', 'T').slice(0, 16);
    valueInput.value = reading.value;
    unitInput.value = reading.unit ?? '';
    notesInput.value = reading.notes ?? '';

    const tank = tanksById.get(String(reading.tank_id));
    populateParameterPreset(tank ? tank.salinity_type : null, reading.parameter_name);

    // Tank isn't reassignable here -- hide it rather than silently ignoring a change.
    tankField.hidden = true;

    waterTestFormTitle.textContent = 'Edit Reading';
    waterTestFormSubmit.textContent = 'Update Reading';
    waterTestFormCancel.hidden = false;
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

    populateParameterPreset(tanks.length > 0 ? tanks[0].salinity_type : null);
}

async function loadWaterTests() {
    const response = await fetch('api/water_tests.php');
    const readings = await response.json();

    if (readings.length === 0) {
        waterTestList.innerHTML = '<p>No readings yet. Log one below.</p>';
        return;
    }

    waterTestList.innerHTML = '';

    for (const reading of readings) {
        const card = document.createElement('div');
        card.className = 'tank-card';

        const title = document.createElement('h4');
        title.textContent = `${reading.parameter_name}: ${reading.value}${reading.unit ? ' ' + reading.unit : ''}`;

        const meta = document.createElement('p');
        meta.textContent = [`Tank: ${reading.tank_name}`, reading.tested_at].join(' • ');

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.textContent = 'Edit';
        editButton.addEventListener('click', () => fillEditForm(reading));

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.textContent = 'Delete';
        deleteButton.addEventListener('click', () => deleteWaterTest(reading.id));

        card.append(title, meta, editButton, deleteButton);
        waterTestList.appendChild(card);
    }
}

async function deleteWaterTest(id) {
    if (!confirm('Delete this reading?')) {
        return;
    }

    await fetch(`api/water_tests.php?id=${id}`, { method: 'DELETE' });
    await loadWaterTests();
}

tankSelect.addEventListener('change', () => {
    const tank = tanksById.get(tankSelect.value);
    populateParameterPreset(tank ? tank.salinity_type : null);
});

parameterPresetSelect.addEventListener('change', () => {
    if (parameterPresetSelect.value === 'Other') {
        customParameterField.hidden = false;
        parameterNameInput.value = '';
        unitInput.value = '';
    } else {
        customParameterField.hidden = true;
        unitInput.value = PARAMETER_UNIT_DEFAULTS[parameterPresetSelect.value] ?? '';
    }
});

waterTestForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearFormError();

    const editingId = waterTestIdField.value;

    const parameterName = parameterPresetSelect.value === 'Other'
        ? parameterNameInput.value.trim()
        : parameterPresetSelect.value;

    if (parameterName === '') {
        showFormError('Please select or enter a parameter name.');
        return;
    }

    if (valueInput.value === '') {
        showFormError('Please enter a value.');
        return;
    }

    const readingData = {
        tested_at: testedAtInput.value.replace('T', ' '),
        parameter_name: parameterName,
        value: Number(valueInput.value),
        unit: unitInput.value.trim() || null,
        notes: notesInput.value.trim() || null,
    };

    let url = 'api/water_tests.php';
    let method = 'POST';

    if (editingId) {
        url = `api/water_tests.php?id=${editingId}`;
        method = 'PUT';
    } else {
        if (!tankSelect.value) {
            showFormError('Please select a tank.');
            return;
        }
        readingData.tank_id = tankSelect.value;
    }

    const response = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(readingData),
    });

    const result = await response.json();

    if (!response.ok) {
        showFormError(result.error || 'Something went wrong.');
        return;
    }

    resetForm();
    await loadWaterTests();
});

waterTestFormCancel.addEventListener('click', resetForm);

(async function init() {
    await loadTanks();
    testedAtInput.value = formatDatetimeLocal(new Date());
    await loadWaterTests();
})();
