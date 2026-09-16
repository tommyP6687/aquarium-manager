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

function openSpriteLightbox(pixelData, gridSize) {
    let overlay = document.getElementById('sprite-lightbox-overlay');

    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'sprite-lightbox-overlay';
        overlay.className = 'sprite-lightbox-overlay';
        overlay.addEventListener('click', () => {
            overlay.hidden = true;
        });
        document.body.appendChild(overlay);
    }

    overlay.innerHTML = '';
    const large = renderSpriteThumbnail(pixelData, gridSize);
    large.classList.add('sprite-thumbnail-large');
    overlay.appendChild(large);
    overlay.hidden = false;
}

// Attaches a click-to-expand handler to a rendered sprite thumbnail. Kept
// separate (opt-in) rather than baked into renderSpriteThumbnail itself,
// since some callers (the "choose from my sprites" bank picker in
// organisms.js) already use a click on the same element to mean "select
// this sprite" -- expand-on-click there would silently break selection.
function enableSpriteExpand(element, pixelData, gridSize) {
    element.classList.add('sprite-expandable');
    element.addEventListener('click', (event) => {
        event.stopPropagation();
        openSpriteLightbox(pixelData, gridSize);
    });
}
