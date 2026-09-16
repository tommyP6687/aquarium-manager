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

// Prefers the S3-hosted PNG snapshot (cheap to render, exercises the cloud
// storage read path) for small thumbnails; falls back to the client-side
// CSS-grid render when no image_url exists yet (S3 not configured, or the
// upload failed at save time) -- so this always works regardless of AWS setup.
function renderSpriteImage(sprite) {
    if (sprite.image_url) {
        const img = document.createElement('img');
        img.className = 'sprite-thumbnail';
        img.src = sprite.image_url;
        img.alt = sprite.sprite_name || 'sprite';
        return img;
    }

    return renderSpriteThumbnail(JSON.parse(sprite.pixel_data), sprite.grid_size);
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
