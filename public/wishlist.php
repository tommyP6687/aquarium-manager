<?php
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aquarium Manager - Wishlist</title>
        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <?php include __DIR__ . '/../includes/nav.php'; ?>

        <main class="tanks-page">
            <h2>Wishlist</h2>

            <section id="wishlist-list" class="tank-list">
                <p>Loading wishlist...</p>
            </section>

            <section class="tank-form-section">
                <h3 id="wishlist-form-title">Add a Wishlist Item</h3>
                <p id="wishlist-form-error" class="error-message" hidden></p>

                <form id="wishlist-form">
                    <input type="hidden" id="wishlist-id" value="">

                    <div class="input-component">
                        <label for="item_name">Item Name:</label>
                        <input type="text" id="item_name" required autocomplete="off">
                    </div>

                    <div id="price-suggestions" class="species-suggestions" hidden></div>

                    <div class="input-component">
                        <label for="tank_id">Desired Tank:</label>
                        <select id="tank_id">
                            <option value="">None yet</option>
                        </select>
                    </div>

                    <div class="input-component">
                        <label for="category_preset">Category:</label>
                        <select id="category_preset">
                            <option>Fish</option>
                            <option>Invertebrate</option>
                            <option>Plant</option>
                            <option>Coral</option>
                            <option>Equipment</option>
                            <option>Decoration</option>
                            <option>Food</option>
                            <option>Fertilizer</option>
                            <option>Medication</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="input-component" id="custom-category-field" hidden>
                        <label for="category">Custom Category:</label>
                        <input type="text" id="category">
                    </div>

                    <div class="equipment-row">
                        <div class="input-component">
                            <label for="estimated_price">Estimated Price:</label>
                            <input type="number" step="0.01" id="estimated_price">
                        </div>
                        <div class="input-component">
                            <label for="store_or_source">Store/Source:</label>
                            <input type="text" id="store_or_source">
                        </div>
                    </div>

                    <div class="input-component">
                        <label for="priority">Priority:</label>
                        <select id="priority">
                            <option>Low</option>
                            <option selected>Medium</option>
                            <option>High</option>
                        </select>
                    </div>

                    <div class="input-component">
                        <label for="purchase_status">Purchase Status:</label>
                        <select id="purchase_status">
                            <option selected>Wanted</option>
                            <option>Purchased</option>
                            <option>Cancelled</option>
                        </select>
                    </div>

                    <div class="input-component">
                        <label for="compatibility_notes">Compatibility Notes:</label>
                        <textarea id="compatibility_notes" rows="2"></textarea>
                    </div>

                    <div class="input-component">
                        <label for="wishlist_notes">Notes:</label>
                        <textarea id="wishlist_notes" rows="2"></textarea>
                    </div>

                    <div class="action-component">
                        <button type="submit" id="wishlist-form-submit" class="btn-secondary">Add Item</button>
                        <button type="button" id="wishlist-form-cancel" class="btn-secondary" hidden>Cancel</button>
                    </div>
                </form>
            </section>
        </main>

        <script src="js/wishlist.js"></script>
    </body>
</html>
