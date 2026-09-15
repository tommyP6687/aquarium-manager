const speciesList = document.getElementById('species-list');
const speciesFormSection = document.getElementById('species-form-section');
const speciesForm = document.getElementById('species-form');
const speciesFormTitle = document.getElementById('species-form-title');
const speciesFormError = document.getElementById('species-form-error');
const speciesFormCancel = document.getElementById('species-form-cancel');
const speciesIdField = document.getElementById('species-id');

const FIELD_IDS = [
    'min_temp_f', 'max_temp_f', 'min_ph', 'max_ph', 'min_tank_size_gallons',
    'adult_size_inches', 'temperament', 'min_group_size', 'care_level', 'care_notes',
];
const BOOLEAN_FIELD_IDS = ['is_plant_safe', 'is_shrimp_safe'];

function showFormError(message) {
    speciesFormError.textContent = message;
    speciesFormError.hidden = false;
}

function clearFormError() {
    speciesFormError.hidden = true;
    speciesFormError.textContent = '';
}

function fillEditForm(species) {
    speciesIdField.value = species.id;

    for (const field of FIELD_IDS) {
        document.getElementById(field).value = species[field] ?? '';
    }
    for (const field of BOOLEAN_FIELD_IDS) {
        const value = species[field];
        document.getElementById(field).value = value === null ? '' : (Number(value) ? '1' : '0');
    }

    speciesFormTitle.textContent = `Edit ${species.common_name || species.scientific_name}`;
    speciesFormSection.hidden = false;
    clearFormError();
    speciesFormSection.scrollIntoView({ behavior: 'smooth' });
}

function hideForm() {
    speciesForm.reset();
    speciesFormSection.hidden = true;
    clearFormError();
}

async function loadSpecies() {
    const response = await fetch('api/species.php');
    const speciesRows = await response.json();

    if (speciesRows.length === 0) {
        speciesList.innerHTML = '<p>No species yet. Add an organism first, then come back here to fill in its care data.</p>';
        return;
    }

    speciesList.innerHTML = '';

    for (const species of speciesRows) {
        const card = document.createElement('div');
        card.className = 'tank-card';

        const title = document.createElement('h4');
        title.textContent = species.common_name || species.scientific_name;

        const meta = document.createElement('p');
        meta.textContent = [species.scientific_name, species.organism_type].filter(Boolean).join(' • ');

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.textContent = 'Edit Care Data';
        editButton.addEventListener('click', () => fillEditForm(species));

        card.append(title, meta, editButton);
        speciesList.appendChild(card);
    }
}

speciesForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearFormError();

    const id = speciesIdField.value;
    const data = {};

    for (const field of FIELD_IDS) {
        const value = document.getElementById(field).value.trim();
        data[field] = value === '' ? null : value;
    }
    for (const field of BOOLEAN_FIELD_IDS) {
        const value = document.getElementById(field).value;
        data[field] = value === '' ? null : value === '1';
    }

    const response = await fetch(`api/species.php?id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });

    const result = await response.json();

    if (!response.ok) {
        showFormError(result.error || 'Something went wrong.');
        return;
    }

    hideForm();
    await loadSpecies();
});

speciesFormCancel.addEventListener('click', hideForm);

loadSpecies();
