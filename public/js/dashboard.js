const needsAttentionList = document.getElementById('needs-attention-list');
const dashboardTankList = document.getElementById('dashboard-tank-list');
const upcomingRemindersList = document.getElementById('upcoming-reminders-list');
const compatibilityWarningsList = document.getElementById('compatibility-warnings-list');

function renderCompatibilityWarnings(warnings) {
    if (warnings.length === 0) {
        compatibilityWarningsList.innerHTML = '<p>No compatibility warnings right now.</p>';
        return;
    }

    compatibilityWarningsList.innerHTML = '';

    for (const warning of warnings) {
        const card = document.createElement('div');
        card.className = 'tank-card';

        const title = document.createElement('h4');
        title.textContent = warning.custom_name || warning.tank_name;

        const meta = document.createElement('p');
        meta.textContent = [warning.tank_name !== title.textContent ? `Tank: ${warning.tank_name}` : null, warning.warning]
            .filter(Boolean)
            .join(' • ');

        card.append(title, meta);
        compatibilityWarningsList.appendChild(card);
    }
}

function renderUpcomingReminders(reminders) {
    const upcoming = reminders.filter((reminder) => !Number(reminder.is_completed));

    if (upcoming.length === 0) {
        upcomingRemindersList.innerHTML = '<p>No upcoming reminders.</p>';
        return;
    }

    upcomingRemindersList.innerHTML = '';

    for (const reminder of upcoming) {
        const card = document.createElement('div');
        card.className = 'tank-card';

        const title = document.createElement('h4');
        title.textContent = reminder.title;

        const meta = document.createElement('p');
        meta.textContent = [reminder.task_type, `Tank: ${reminder.tank_name}`, `Due: ${reminder.due_at}`].join(' • ');

        card.append(title, meta);
        upcomingRemindersList.appendChild(card);
    }
}

function renderNeedsAttention(organisms) {
    const flagged = organisms.filter((organism) => organism.health_status !== 'Healthy');

    if (flagged.length === 0) {
        needsAttentionList.innerHTML = '<p>Everything looks healthy.</p>';
        return;
    }

    needsAttentionList.innerHTML = '';

    for (const organism of flagged) {
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

        card.append(title, meta);
        needsAttentionList.appendChild(card);
    }
}

function renderTanks(tanks, organismsByTank, spritesById, healthByTank) {
    if (tanks.length === 0) {
        dashboardTankList.innerHTML = '<p>No tanks yet. Add one to get started.</p>';
        return;
    }

    dashboardTankList.innerHTML = '';

    for (const tank of tanks) {
        const card = document.createElement('div');
        card.className = 'tank-card';

        const title = document.createElement('h4');
        title.textContent = tank.custom_name;

        const meta = document.createElement('p');
        meta.textContent = [tank.tank_type, tank.salinity_type, tank.volume_gallons ? `${tank.volume_gallons} gal` : null]
            .filter(Boolean)
            .join(' • ');

        card.append(title, meta);

        const health = healthByTank.get(tank.id);
        if (health) {
            const healthLine = document.createElement('p');
            healthLine.textContent = `Health: ${health.score}/100 — ${health.status}`;
            const suggestion = document.createElement('p');
            suggestion.textContent = health.suggestion;
            card.append(healthLine, suggestion);
        }

        const organisms = organismsByTank.get(tank.id) || [];

        if (organisms.length === 0) {
            const empty = document.createElement('p');
            empty.textContent = 'No organisms in this tank yet.';
            card.appendChild(empty);
        } else {
            const organismRow = document.createElement('div');
            organismRow.className = 'sprite-bank';

            for (const organism of organisms) {
                const item = document.createElement('div');
                item.className = 'sprite-bank-item';

                const sprite = spritesById.get(organism.pixel_art_id);
                if (sprite) {
                    item.appendChild(renderSpriteThumbnail(JSON.parse(sprite.pixel_data), sprite.grid_size));
                }

                const label = document.createElement('p');
                label.textContent = organism.custom_name;
                item.appendChild(label);

                organismRow.appendChild(item);
            }

            card.appendChild(organismRow);
        }

        dashboardTankList.appendChild(card);
    }
}

async function loadDashboard() {
    const [tanks, organisms, sprites, reminders, warnings, healthScores] = await Promise.all([
        fetch('api/tanks.php').then((r) => r.json()),
        fetch('api/organisms.php').then((r) => r.json()),
        fetch('api/pixel_art.php').then((r) => r.json()),
        fetch('api/reminders.php').then((r) => r.json()),
        fetch('api/compatibility_warnings.php').then((r) => r.json()),
        fetch('api/tank_health.php').then((r) => r.json()),
    ]);

    const healthByTank = new Map(healthScores.map((health) => [health.tank_id, health]));

    const organismsByTank = new Map();
    for (const organism of organisms) {
        const list = organismsByTank.get(organism.tank_id) || [];
        list.push(organism);
        organismsByTank.set(organism.tank_id, list);
    }

    const spritesById = new Map(sprites.map((sprite) => [sprite.id, sprite]));

    renderUpcomingReminders(reminders);
    renderCompatibilityWarnings(warnings);
    renderNeedsAttention(organisms);
    renderTanks(tanks, organismsByTank, spritesById, healthByTank);
}

loadDashboard();
