const stage = document.getElementById('virtual-tank-stage');
const emptyMessage = document.getElementById('virtual-tank-empty');
const titleHeading = document.getElementById('virtual-tank-title');

const BASE_SPRITE_SIZE = 50;
const SUBSTRATE_FRACTION = 0.12;

function shapeClassFor(tankShape) {
    const shape = (tankShape || '').toLowerCase();

    if (shape.includes('bow') || shape.includes('round') || shape.includes('cylinder')) {
        return 'virtual-tank-bowfront';
    }
    if (shape.includes('hex')) {
        return 'virtual-tank-hex';
    }
    if (shape.includes('cube') || shape.includes('square')) {
        return 'virtual-tank-cube';
    }
    return 'virtual-tank-rectangular';
}

function scaleForOrganism(organism) {
    const stageName = organism.growth_stage || organism.estimated_growth_stage;
    if (stageName === 'Juvenile') {
        return 0.6;
    }
    if (stageName === 'Sub-adult') {
        return 0.8;
    }
    return 1.0;
}

function randomBetween(min, max) {
    return min + Math.random() * (max - min);
}

function placeSwimmer(element, spriteSize, { nearBottom }) {
    const stageWidth = stage.clientWidth;
    const stageHeight = stage.clientHeight;
    const substrateTop = stageHeight * (1 - SUBSTRATE_FRACTION);

    const maxLeft = Math.max(0, stageWidth - spriteSize);
    const startLeft = randomBetween(0, maxLeft * 0.4);
    const endLeft = randomBetween(maxLeft * 0.6, maxLeft);

    const top = nearBottom
        ? substrateTop - spriteSize
        : randomBetween(stageHeight * 0.05, substrateTop - spriteSize * 2);

    element.style.top = `${Math.max(0, top)}px`;
    element.style.setProperty('--start-left', `${startLeft}px`);
    element.style.setProperty('--end-left', `${endLeft}px`);
    element.style.setProperty('--swim-duration', `${nearBottom ? randomBetween(10, 16) : randomBetween(4, 8)}s`);
    element.style.setProperty('--swim-delay', `${randomBetween(0, 3)}s`);
    element.classList.add('swimming');
}

function placeRooted(element, spriteSize, index, total) {
    const stageWidth = stage.clientWidth;
    const slotWidth = stageWidth / total;
    const left = slotWidth * (index + 0.5) - spriteSize / 2;

    element.style.left = `${Math.max(0, Math.min(stageWidth - spriteSize, left))}px`;
    element.style.bottom = '8%';
    element.style.setProperty('--sway-duration', `${randomBetween(2.5, 4.5)}s`);
    element.classList.add('rooted');
}

async function loadVirtualTank() {
    const params = new URLSearchParams(window.location.search);
    const tankId = params.get('tank_id');

    if (!tankId) {
        emptyMessage.textContent = 'No tank specified.';
        emptyMessage.hidden = false;
        return;
    }

    const [tank, organisms, sprites] = await Promise.all([
        fetch(`api/tanks.php?id=${tankId}`).then((r) => r.json()),
        fetch(`api/organisms.php?tank_id=${tankId}`).then((r) => r.json()),
        fetch('api/pixel_art.php').then((r) => r.json()),
    ]);

    if (tank && tank.custom_name) {
        titleHeading.textContent = `Virtual Tank: ${tank.custom_name}`;
        document.title = `Aquarium Manager - ${tank.custom_name}`;
    }

    stage.classList.remove('virtual-tank-rectangular');
    stage.classList.add(shapeClassFor(tank ? tank.tank_shape : null));

    if (!Array.isArray(organisms) || organisms.length === 0) {
        emptyMessage.hidden = false;
        return;
    }

    emptyMessage.hidden = true;

    const spritesById = new Map(sprites.map((sprite) => [sprite.id, sprite]));

    const rooted = organisms.filter((o) => !['Fish', 'Invertebrate'].includes(o.species_organism_type));
    const swimmers = organisms.filter((o) => ['Fish', 'Invertebrate'].includes(o.species_organism_type));

    for (const organism of swimmers) {
        const sprite = spritesById.get(organism.pixel_art_id);
        if (!sprite) {
            continue;
        }

        const element = renderSpriteImage(sprite);
        const size = BASE_SPRITE_SIZE * scaleForOrganism(organism);

        element.className += ' virtual-organism';
        element.style.width = `${size}px`;
        element.style.height = `${size}px`;
        element.title = organism.custom_name;

        stage.appendChild(element);
        placeSwimmer(element, size, { nearBottom: organism.species_organism_type === 'Invertebrate' });
    }

    rooted.forEach((organism, index) => {
        const sprite = spritesById.get(organism.pixel_art_id);
        if (!sprite) {
            return;
        }

        const element = renderSpriteImage(sprite);
        const size = BASE_SPRITE_SIZE * scaleForOrganism(organism);

        element.className += ' virtual-organism';
        element.style.width = `${size}px`;
        element.style.height = `${size}px`;
        element.title = organism.custom_name;

        stage.appendChild(element);
        placeRooted(element, size, index, rooted.length);
    });
}

loadVirtualTank();
