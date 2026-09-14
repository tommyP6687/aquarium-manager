function renderSpriteThumbnail(pixelData, gridSize) {
    const wrapper = document.createElement('div');
    wrapper.className = 'sprite-thumbnail';
    wrapper.style.gridTemplateColumns = `repeat(${gridSize}, 1fr)`;

    for (const row of pixelData) {
        for (const hex of row) {
            const cell = document.createElement('div');
            cell.style.backgroundColor = hex;
            wrapper.appendChild(cell);
        }
    }

    return wrapper;
}
