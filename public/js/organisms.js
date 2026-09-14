const organismList = document.getElementById('organism-list');
const organismForm = document.getElementById('organism-form');
const organismFormTitle = document.getElementById('organism-form-title');
const organismFormError = document.getElementById('organism-form-error');
const organismFormSubmit = document.getElementById('organism-form-submit');
const organismFormCancel = document.getElementById('organism-form-cancel');
const organismIdField = document.getElementById('organism-id');
const speciesFieldset = document.getElementById('species-fieldset');
const spriteFieldset = document.getElementById('sprite-fieldset');
const tankField = document.getElementById('tank-field');
const dateAddedField = document.getElementById('date-added-field');
const tankSelect = document.getElementById('tank_id');

const speciesSearchInput = document.getElementById('species_search');
const speciesSuggestions = document.getElementById('species-suggestions');
const scientificNameInput = document.getElementById('scientific_name');
const commonNameInput = document.getElementById('common_name');
const organismTypeSelect = document.getElementById('organism_type');
const speciesSalinityInput = document.getElementById('species_salinity_type');
const externalTaxonIdInput = document.getElementById('external_taxon_id');

const spriteUploadFields = document.getElementById('sprite-upload-fields');
const spriteBankFields = document.getElementById('sprite-bank-fields');
const spriteBankContainer = document.getElementById('sprite-bank');
const spriteNameInput = document.getElementById('sprite_name');
const spritePhotoInput = document.getElementById('sprite_photo');

let selectedSpriteId = null;
let userSprites = [];
let speciesSearchTimer = null;

function showFormError(message) {
    organismFormError.textContent = message;
    organismFormError.hidden = false;
}

function clearFormError() {
    organismFormError.hidden = true;
    organismFormError.textContent = '';
}

function resetForm() {
    organismForm.reset();
    organismIdField.value = '';
    selectedSpriteId = null;
    speciesSuggestions.hidden = true;
    speciesFieldset.hidden = false;
    spriteFieldset.hidden = false;
    tankField.hidden = false;
    dateAddedField.hidden = false;
    organismFormTitle.textContent = 'Add an Organism';
    organismFormSubmit.textContent = 'Add Organism';
    organismFormCancel.hidden = true;
    clearFormError();
}

function fillEditForm(organism) {
    organismIdField.value = organism.id;
    document.getElementById('custom_name').value = organism.custom_name ?? '';
    document.getElementById('health_status').value = organism.health_status ?? 'Healthy';
    document.getElementById('growth_stage').value = organism.growth_stage ?? '';
    document.getElementById('current_size_inches').value = organism.current_size_inches ?? '';
    document.getElementById('organism_notes').value = organism.notes ?? '';

    // Tank/date/species/sprite aren't editable here -- hide those fields rather
    // than silently ignoring whatever the user types into them.
    speciesFieldset.hidden = true;
    spriteFieldset.hidden = true;
    tankField.hidden = true;
    dateAddedField.hidden = true;

    organismFormTitle.textContent = 'Edit Organism';
    organismFormSubmit.textContent = 'Update Organism';
    organismFormCancel.hidden = false;
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

    return tanks;
}

async function loadSprites() {
    const response = await fetch('api/pixel_art.php');
    userSprites = await response.json();

    spriteBankContainer.innerHTML = '';

    if (userSprites.length === 0) {
        spriteBankContainer.innerHTML = '<p>No saved sprites yet. Upload a photo instead.</p>';
        return;
    }

    for (const sprite of userSprites) {
        const card = document.createElement('div');
        card.className = 'sprite-bank-item';

        const pixelData = JSON.parse(sprite.pixel_data);
        card.appendChild(renderSpriteThumbnail(pixelData, sprite.grid_size));

        const label = document.createElement('p');
        label.textContent = sprite.sprite_name;
        card.appendChild(label);

        card.addEventListener('click', () => {
            selectedSpriteId = sprite.id;
            document.querySelectorAll('.sprite-bank-item').forEach((el) => el.classList.remove('selected'));
            card.classList.add('selected');
        });

        spriteBankContainer.appendChild(card);
    }
}

async function loadOrganisms() {
    const response = await fetch('api/organisms.php');
    const organisms = await response.json();

    if (organisms.length === 0) {
        organismList.innerHTML = '<p>No organisms yet. Add one below.</p>';
        return;
    }

    organismList.innerHTML = '';

    for (const organism of organisms) {
        const card = document.createElement('div');
        card.className = 'tank-card';

        const title = document.createElement('h4');
        title.textContent = organism.custom_name;

        const meta = document.createElement('p');
        meta.textContent = [
            organism.species_common_name || organism.species_scientific_name,
            `Tank: ${organism.tank_name}`,
            organism.health_status,
        ].filter(Boolean).join(' • ');

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.textContent = 'Edit';
        editButton.addEventListener('click', () => fillEditForm(organism));

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.textContent = 'Delete';
        deleteButton.addEventListener('click', () => deleteOrganism(organism.id));

        card.append(title, meta, editButton, deleteButton);
        organismList.appendChild(card);
    }
}

async function deleteOrganism(id) {
    if (!confirm('Delete this organism?')) {
        return;
    }

    await fetch(`api/organisms.php?id=${id}`, { method: 'DELETE' });
    await loadOrganisms();
}

speciesSearchInput.addEventListener('input', () => {
    clearTimeout(speciesSearchTimer);
    const query = speciesSearchInput.value.trim();

    if (query === '') {
        speciesSuggestions.hidden = true;
        return;
    }

    speciesSearchTimer = setTimeout(async () => {
        const response = await fetch(`api/species_search.php?q=${encodeURIComponent(query)}`);
        const results = await response.json();

        speciesSuggestions.innerHTML = '';

        if (!Array.isArray(results) || results.length === 0) {
            speciesSuggestions.hidden = true;
            return;
        }

        for (const result of results) {
            const item = document.createElement('div');
            item.className = 'species-suggestion';
            item.textContent = `${result.common_name || result.scientific_name} (${result.scientific_name})`;

            item.addEventListener('click', () => {
                scientificNameInput.value = result.scientific_name || '';
                commonNameInput.value = result.common_name || '';
                organismTypeSelect.value = result.organism_type_guess || '';
                externalTaxonIdInput.value = result.external_taxon_id || '';
                speciesSuggestions.hidden = true;
                speciesSearchInput.value = result.common_name || result.scientific_name;
            });

            speciesSuggestions.appendChild(item);
        }

        speciesSuggestions.hidden = false;
    }, 350);
});

document.querySelectorAll('input[name="sprite_mode"]').forEach((radio) => {
    radio.addEventListener('change', () => {
        const isUpload = radio.value === 'upload' && radio.checked;
        spriteUploadFields.hidden = !isUpload;
        spriteBankFields.hidden = isUpload;
    });
});

organismForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearFormError();

    const editingId = organismIdField.value;

    if (editingId) {
        const updateData = {
            custom_name: document.getElementById('custom_name').value.trim(),
            health_status: document.getElementById('health_status').value,
            growth_stage: document.getElementById('growth_stage').value || null,
            current_size_inches: document.getElementById('current_size_inches').value || null,
            notes: document.getElementById('organism_notes').value.trim() || null,
        };

        const response = await fetch(`api/organisms.php?id=${editingId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(updateData),
        });

        const result = await response.json();

        if (!response.ok) {
            showFormError(result.error || 'Something went wrong.');
            return;
        }

        resetForm();
        await loadOrganisms();
        return;
    }

    if (!tankSelect.value) {
        showFormError('Please select a tank.');
        return;
    }

    if (scientificNameInput.value.trim() === '') {
        showFormError('Please search for a species or enter a scientific name.');
        return;
    }

    const spriteMode = document.querySelector('input[name="sprite_mode"]:checked').value;
    let pixelArtId = null;

    if (spriteMode === 'upload') {
        if (!spritePhotoInput.files[0]) {
            showFormError('Please choose a photo to upload.');
            return;
        }

        const spriteData = new FormData();
        spriteData.append('sprite_name', spriteNameInput.value.trim() || scientificNameInput.value.trim());
        spriteData.append('photo', spritePhotoInput.files[0]);

        const spriteResponse = await fetch('api/pixel_art.php', { method: 'POST', body: spriteData });
        const spriteResult = await spriteResponse.json();

        if (!spriteResponse.ok) {
            showFormError(spriteResult.error || 'Could not generate a sprite from that photo.');
            return;
        }

        pixelArtId = spriteResult.id;
    } else {
        if (!selectedSpriteId) {
            showFormError('Please choose a sprite from your bank.');
            return;
        }
        pixelArtId = selectedSpriteId;
    }

    const organismData = {
        tank_id: tankSelect.value,
        custom_name: document.getElementById('custom_name').value.trim(),
        date_added: document.getElementById('date_added').value || null,
        health_status: document.getElementById('health_status').value,
        growth_stage: document.getElementById('growth_stage').value || null,
        current_size_inches: document.getElementById('current_size_inches').value || null,
        notes: document.getElementById('organism_notes').value.trim() || null,
        scientific_name: scientificNameInput.value.trim(),
        common_name: commonNameInput.value.trim() || null,
        organism_type: organismTypeSelect.value || null,
        salinity_type: speciesSalinityInput.value.trim() || null,
        external_taxon_id: externalTaxonIdInput.value || null,
        pixel_art_id: pixelArtId,
    };

    const response = await fetch('api/organisms.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(organismData),
    });

    const result = await response.json();

    if (!response.ok) {
        showFormError(result.error || 'Something went wrong.');
        return;
    }

    resetForm();
    await loadOrganisms();
    await loadSprites();
});

organismFormCancel.addEventListener('click', resetForm);

loadTanks();
loadSprites();
loadOrganisms();
