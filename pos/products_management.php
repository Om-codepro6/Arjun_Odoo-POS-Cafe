<?php
include '../includes/auth_check.php';

// Fetch products from database
$products_query = $conn->prepare("SELECT id, name, category, price, tax FROM products ORDER BY created_at DESC");
$products_query->execute();
$products_result = $products_query->get_result();
$products = $products_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🍽️ Product Management - Cafe POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1559054664-1365ee7b4b1c?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat fixed;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            line-height: 1.6;
            height: 100vh;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            pointer-events: none;
            z-index: -1;
        }

        .admin-wrapper {
            max-width: 1200px;
            margin: 20px auto;
            height: calc(100vh - 40px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.7);
            border-radius: 12px;
            overflow: hidden;
            background: rgba(18, 18, 18, 0.95);
            backdrop-filter: blur(10px);
        }

        .breadcrumb-header {
            background: linear-gradient(135deg, #2a2a2a, #3a3a2a);
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
        }

        .btn-new {
            background: linear-gradient(135deg, #a27eb8, #8a5cb8);
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            margin-right: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        .btn-new:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(162, 126, 184, 0.3);
        }

        .action-main-btn {
            background: linear-gradient(135deg, #a27eb8, #8a5cb8);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .action-main-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(162, 126, 184, 0.3);
        }

        .product-grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            background-color: #333;
            height: calc(100% - 80px);
        }

        .form-section-container, .list-section-container {
            background-color: #121212;
            padding: 20px;
            height: 100%;
            overflow-y: auto;
        }

        .product-title-input { margin-bottom: 30px; }
        .product-title-input label { display: block; color: #888; font-size: 14px; margin-bottom: 5px; font-weight: 500; }

        .main-title-field {
            background: #1e1e1e;
            border: 1px solid #333;
            border-radius: 6px;
            width: 100%;
            font-size: 26px;
            color: white;
            padding: 15px;
            transition: border-color 0.2s;
            font-weight: 300;
        }

        .main-title-field:focus {
            border-color: #a27eb8;
            outline: none;
            box-shadow: 0 0 5px rgba(162, 126, 184, 0.5);
        }

        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; }
        .form-group label { display: block; color: #888; font-size: 13px; margin-bottom: 8px; font-weight: 500; }

        .input-unit-group {
            display: flex;
            gap: 10px;
        }

        .input-unit-group input {
            flex: 1;
        }

        .unit-select {
            flex: 0 0 80px;
        }

        select, input[type="text"], textarea {
            background: #1e1e1e;
            border: 1px solid #333;
            border-radius: 4px;
            color: white;
            width: 100%;
            padding: 8px;
            font-size: 14px;
        }

        select:focus, input[type="text"]:focus, textarea:focus {
            border-color: #a27eb8;
            outline: none;
            box-shadow: 0 0 5px rgba(162, 126, 184, 0.5);
        }

        .add-button-container {
            text-align: center;
            margin-top: 30px;
        }

        .add-btn {
            background: linear-gradient(135deg, #a27eb8, #8a5cb8);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(162, 126, 184, 0.4);
        }

        .odoo-list-table { width: 100%; border-collapse: collapse; margin-top: 20px; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.3); }
        .odoo-list-table th { text-align: left; color: #888; font-size: 13px; padding: 15px; border-bottom: 1px solid #333; background: #1a1a1a; }
        .odoo-list-table td { padding: 15px 10px; border-bottom: 1px solid #222; font-size: 14px; cursor: pointer; transition: background 0.2s; }
        .odoo-list-table tr:hover td { background: #2a2a2a; }

        .badge { padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: bold; }
        .badge-food { background-color: #2c4a6e; color: #8eb9ff; }
        .badge-drink { background-color: #4a412a; color: #ffcc66; }
        .badge-dessert { background-color: #4a2c2a; color: #ff8eb9; }
        .badge-snack { background-color: #2a4a2c; color: #8eff8e; }
        .badge-beverage { background-color: #4a2a4a; color: #ff8eff; }

        .list-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .selection-pill {
            background-color: #2c4a6e;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            display: inline-block;
            font-size: 12px;
        }

        .btn-secondary {
            background: #333;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .text-purple { color: #d0b0e6; }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <div class="breadcrumb-header">
        <div class="left-head">
            <a href="index.php" class="btn-new">← Back to POS</a>
            <span class="path">🍽️ Products / <span class="current">🍔 New</span></span>
        </div>
        <div class="right-head">
            <button class="action-main-btn" onclick="saveProduct()"><i class="fa-solid fa-cloud-arrow-up"></i> 💾 Save</button>
        </div>
    </div>

    <div class="product-grid-container">
        
        <div class="form-section-container">
            <div class="product-title-input">
                <label>🍔 Product</label>
                <input type="text" id="productName" placeholder="e.g. Burger with cheese" class="main-title-field">
            </div>

            <div>
                <div class="form-row">
                    <div class="form-group">
                        <label>📂 Category</label>
                        <div class="select-wrapper">
                            <select id="category">
                                <option>Drink</option>
                                <option selected>Food</option>
                                <option>Dessert</option>
                                <option>Snack</option>
                                <option>Beverage</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>💰 Prices</label>
                        <div class="input-unit-group">
                            <input type="text" id="price" placeholder="0.00">
                            <select id="unit" class="unit-select">
                                <option>Unit</option>
                                <option>KG</option>
                                <option>Liter</option>
                                <option>Piece</option>
                                <option>Box</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>📝 Product Description</label>
                        <textarea id="description" placeholder="e.g. Burger with cheese"></textarea>
                    </div>
                    <div class="form-group">
                        <label>🧾 Tax</label>
                        <select id="tax" class="bottom-border-select">
                            <option>5%</option>
                            <option>12%</option>
                            <option>18%</option>
                            <option>28%</option>
                            <option>0%</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Add Button at Bottom Center -->
            <div class="add-button-container">
                <button class="add-btn" onclick="addProduct()">➕ Add</button>
            </div>
        </div>

        <div class="list-section-container">
            <div class="list-controls">
                <div class="selection-pill"><?php echo count($products); ?> Products</div>
                <div class="action-dropdown">
                    <button class="btn-secondary">* Action <i class="fa-solid fa-caret-down"></i></button>
                </div>
            </div>
            
            <table class="odoo-list-table">
                <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>🍽️ Products</th>
                        <th>💰 Prices</th>
                        <th>🧾 Tax</th>
                        <th>📂 Category</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    <?php foreach ($products as $product): ?>
                    <tr onclick="loadProduct('<?php echo htmlspecialchars($product['name']); ?>', '<?php echo htmlspecialchars($product['category']); ?>', '<?php echo htmlspecialchars($product['price']); ?>', '<?php echo htmlspecialchars($product['description'] ?? ''); ?>', '<?php echo htmlspecialchars($product['tax']); ?>%')">
                        <td><input type="checkbox"></td>
                        <td class="text-purple"><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($product['tax']); ?>%</td>
                        <td><span class="badge badge-<?php echo strtolower($product['category']); ?>"><?php echo htmlspecialchars($product['category']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function addProduct() {
    const name = document.getElementById('productName').value.trim();
    const category = document.getElementById('category').value;
    const price = document.getElementById('price').value;
    const description = document.getElementById('description').value;
    const tax = document.getElementById('tax').value;

    if (name) {
        // For now, just add to table (in real implementation, send to server)
        const tbody = document.getElementById('productTableBody');
        const row = document.createElement('tr');
        row.onclick = () => loadProduct(name, category, price, description, tax);
        row.innerHTML = `
            <td><input type="checkbox"></td>
            <td class="text-purple">${name}</td>
            <td>$${price}</td>
            <td>${tax}</td>
            <td><span class="badge badge-${category.toLowerCase()}">${category}</span></td>
        `;
        tbody.appendChild(row);
        clearForm();
        alert('Product added to list! (Note: Not saved to database yet)');
    }
}

function loadProduct(name, category, price, description, tax) {
    document.getElementById('productName').value = name;
    document.getElementById('category').value = category;
    document.getElementById('price').value = price;
    document.getElementById('description').value = description;
    document.getElementById('tax').value = tax;
}

function saveProduct() {
    alert('Save functionality not implemented yet. Products are displayed from database.');
}

function clearForm() {
    document.getElementById('productName').value = '';
    document.getElementById('price').value = '';
    document.getElementById('description').value = '';
}
</script>

</body>
</html>