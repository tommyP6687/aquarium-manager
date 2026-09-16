const gridSizeSection = document.getElementById('grid-size-section');
const editorSection = document.getElementById('editor-section');
const editorError = document.getElementById('editor-error');
const newGridSizeSelect = document.getElementById('new_grid_size');
const startDrawingButton = document.getElementById('start-drawing-button');
const editorGrid = document.getElementById('editor-grid');
const paletteSwatches = document.getElementById('palette-swatches');
const customColorInput = document.getElementById('custom_color');
const toolPaintButton = document.getElementById('tool-paint');
const toolEraseButton = document.getElementById('tool-erase');
const toolFillButton = document.getElementById('tool-fill');
const spriteNameInput = document.getElementById('editor_sprite_name');
const saveSpriteButton = document.getElementById('save-sprite-button');

const WHITE = '#ffffff';

let gridSize = 24;
let pixelData = [];
let currentColor = customColorInput.value;
let currentTool = 'paint';
let isPointerDown = false;

function showEditorError(message) {
    editorError.textContent = message;
    editorError.hidden = false;
}

function clearEditorError() {
    editorError.hidden = true;
    editorError.textContent = '';
}

function setTool(tool) {
    currentTool = tool;
    toolPaintButton.classList.toggle('editor-tool-active', tool === 'paint');
    toolEraseButton.classList.toggle('editor-tool-active', tool === 'erase');
    toolFillButton.classList.toggle('editor-tool-active', tool === 'fill');
}

function renderPaletteSwatches() {
    paletteSwatches.innerHTML = '';

    for (const hex of PIXEL_ART_PALETTE) {
        const swatch = document.createElement('button');
        swatch.type = 'button';
        swatch.className = 'palette-swatch';
        swatch.style.backgroundColor = hex;
        swatch.title = hex;

        swatch.addEventListener('click', () => {
            currentColor = hex;
            document.querySelectorAll('.palette-swatch').forEach((el) => el.classList.remove('selected'));
            swatch.classList.add('selected');
        });

        paletteSwatches.appendChild(swatch);
    }
}

function cellAt(x, y) {
    return editorGrid.querySelector(`[data-x="${x}"][data-y="${y}"]`);
}

function paintCell(x, y, color) {
    pixelData[y][x] = color;
    const cell = cellAt(x, y);
    if (cell) {
        cell.style.backgroundColor = color;
    }
}

function floodFill(startX, startY, newColor) {
    const targetColor = pixelData[startY][startX];
    if (targetColor === newColor) {
        return;
    }

    const stack = [[startX, startY]];

    while (stack.length > 0) {
        const [x, y] = stack.pop();

        if (x < 0 || x >= gridSize || y < 0 || y >= gridSize) {
            continue;
        }
        if (pixelData[y][x] !== targetColor) {
            continue;
        }

        paintCell(x, y, newColor);

        stack.push([x + 1, y], [x - 1, y], [x, y + 1], [x, y - 1]);
    }
}

function applyToolAtCell(x, y) {
    if (currentTool === 'paint') {
        paintCell(x, y, currentColor);
    } else if (currentTool === 'erase') {
        paintCell(x, y, WHITE);
    } else if (currentTool === 'fill') {
        floodFill(x, y, currentColor);
    }
}

function cellFromEvent(event) {
    const target = document.elementFromPoint(event.clientX, event.clientY);
    if (!target || !target.classList.contains('editor-cell')) {
        return null;
    }
    return target;
}

function buildGrid() {
    editorGrid.innerHTML = '';
    editorGrid.style.gridTemplateColumns = `repeat(${gridSize}, 1fr)`;

    pixelData = [];

    for (let y = 0; y < gridSize; y++) {
        const row = [];
        for (let x = 0; x < gridSize; x++) {
            row.push(WHITE);

            const cell = document.createElement('div');
            cell.className = 'editor-cell';
            cell.dataset.x = x;
            cell.dataset.y = y;
            cell.style.backgroundColor = WHITE;
            editorGrid.appendChild(cell);
        }
        pixelData.push(row);
    }
}

startDrawingButton.addEventListener('click', () => {
    gridSize = parseInt(newGridSizeSelect.value, 10);
    buildGrid();
    renderPaletteSwatches();
    gridSizeSection.hidden = true;
    editorSection.hidden = false;
});

customColorInput.addEventListener('input', () => {
    currentColor = customColorInput.value;
    document.querySelectorAll('.palette-swatch').forEach((el) => el.classList.remove('selected'));
});

toolPaintButton.addEventListener('click', () => setTool('paint'));
toolEraseButton.addEventListener('click', () => setTool('erase'));
toolFillButton.addEventListener('click', () => setTool('fill'));

editorGrid.addEventListener('pointerdown', (event) => {
    const cell = cellFromEvent(event) || (event.target.classList.contains('editor-cell') ? event.target : null);
    if (!cell) {
        return;
    }

    isPointerDown = true;
    applyToolAtCell(Number(cell.dataset.x), Number(cell.dataset.y));

    if (currentTool !== 'fill') {
        document.addEventListener('pointermove', handlePointerMove);
    }
    document.addEventListener('pointerup', handlePointerUp);
});

function handlePointerMove(event) {
    if (!isPointerDown) {
        return;
    }

    const cell = cellFromEvent(event);
    if (cell) {
        applyToolAtCell(Number(cell.dataset.x), Number(cell.dataset.y));
    }
}

function handlePointerUp() {
    isPointerDown = false;
    document.removeEventListener('pointermove', handlePointerMove);
    document.removeEventListener('pointerup', handlePointerUp);
}

saveSpriteButton.addEventListener('click', async () => {
    clearEditorError();

    const spriteName = spriteNameInput.value.trim();
    if (spriteName === '') {
        showEditorError('Please enter a sprite name.');
        return;
    }

    const response = await fetch('api/pixel_art.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            sprite_name: spriteName,
            grid_size: gridSize,
            pixel_data: pixelData,
        }),
    });

    const result = await response.json();

    if (!response.ok) {
        showEditorError(result.error || 'Could not save this sprite.');
        return;
    }

    window.location.href = 'organisms.php';
});
