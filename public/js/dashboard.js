const needsAttentionList = document.getElementById('needs-attention-list');
const dashboardTankList = document.getElementById('dashboard-tank-list');
const upcomingRemindersList = document.getElementById('upcoming-reminders-list');

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

function renderTanks(tanks, organismsByTank, spritesById) {
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
    const [tanks, organisms, sprites, reminders] = await Promise.all([
        fetch('api/tanks.php').then((r) => r.json()),
        fetch('api/organisms.php').then((r) => r.json()),
        fetch('api/pixel_art.php').then((r) => r.json()),
        fetch('api/reminders.php').then((r) => r.json()),
    ]);

    const organismsByTank = new Map();
    for (const organism of organisms) {
        const list = organismsByTank.get(organism.tank_id) || [];
        list.push(organism);
        organismsByTank.set(organism.tank_id, list);
    }

    const spritesById = new Map(sprites.map((sprite) => [sprite.id, sprite]));

    renderUpcomingReminders(reminders);
    renderNeedsAttention(organisms);
    renderTanks(tanks, organismsByTank, spritesById);
}

loadDashboard();
