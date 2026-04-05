<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odoo POS Terminal</title>
    <style>
        :root {
            --bg-dark: #121212;
            --card-bg: #1e1e1e;
            --header-bg: #252525;
            --primary-purple: #714B67;
            --text: #e0e0e0;
            --border: #333;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #000;
            color: var(--text);
            margin: 0;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .pos-container {
            width: 1100px;
            background: var(--bg-dark);
            border: 1px solid var(--border);
            border-radius: 4px;
            min-height: 600px;
        }

        /* --- Top Menu --- */
        .top-menu {
            display: flex;
            background: var(--header-bg);
            padding: 10px;
            gap: 10px;
            border-bottom: 1px solid var(--border);
        }

        .menu-btn {
            background: transparent;
            border: 1px solid #666;
            color: white;
            padding: 6px 15px;
            border-radius: 6px;
            cursor: pointer;
        }

        .menu-btn.active {
            background: #444;
            border-color: #eee;
        }

        /* --- View Management --- */
        .view { display: none; padding: 20px; }
        .view.active { display: block; }

        /* --- SCREEN 1: FLOOR VIEW --- */
        .floor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 20px;
        }

        .table-card {
            width: 100px;
            height: 100px;
            border: 2px solid #4da3ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #4da3ff;
            cursor: pointer;
            transition: 0.2s;
        }

        .table-card:hover { background: rgba(77, 163, 255, 0.1); }

        /* --- SCREEN 2: REGISTER VIEW --- */
        .register-layout {
            display: flex;
            gap: 15px;
        }

        /* Left Side: Categories & Products */
        .product-section { flex: 2; }
        
        .category-bar { display: flex; gap: 10px; margin-bottom: 20px; }
        .cat-item { 
            padding: 8px 15px; 
            cursor: pointer; 
            border-radius: 4px; 
            font-weight: bold;
            border: 2px solid transparent;
            transition: 0.2s;
        }
        .cat-item.active {
            border-color: #fff;
            background: #555 !important;
        }
        .cat-all { background: #444; }
        .cat-purple { background: #714B67; }
        .cat-gold { background: #8e6d11; }
        .cat-green { background: #1a5e4b; }

        .search-bar { width: 100%; padding: 10px; background: #222; border: 1px solid var(--border); color: white; margin-bottom: 15px; border-radius: 4px; }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .product-card {
            background: #252525;
            height: 120px;
            position: relative;
            border-bottom: 3px solid var(--primary-purple);
            cursor: pointer;
            transition: 0.2s;
        }

        .product-card:hover {
            background: #2a2a2a;
            transform: scale(1.05);
        }

        .product-card.hidden {
            display: none;
        }

        .product-info {
            position: absolute;
            bottom: 0; width: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            justify-content: space-between;
            padding: 5px;
            font-size: 12px;
            box-sizing: border-box;
        }

        /* Right Side: Order Summary */
        .order-sidebar {
            flex: 1;
            background: #1e1e1e;
            padding: 15px;
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }

        .order-list { 
            flex-grow: 1; 
            border-bottom: 1px dashed #555; 
            padding-bottom: 10px; 
            overflow-y: auto;
            max-height: 300px;
        }
        
        .order-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            margin-bottom: 10px;
            padding: 8px;
            background: #252525;
            border-radius: 4px;
            font-size: 13px;
        }

        .item-details {
            flex-grow: 1;
        }

        .item-remove {
            background: #c62828;
            border: none;
            color: white;
            padding: 4px 8px;
            cursor: pointer;
            border-radius: 3px;
            margin-left: 10px;
        }

        .total-section { 
            font-size: 20px; 
            margin: 15px 0; 
            display: flex; 
            justify-content: space-between;
            font-weight: bold;
        }

        .action-btns { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .send-btn { background: #d4a5bc; color: #000; border: none; padding: 15px; font-weight: bold; border-radius: 4px; cursor: pointer; }
        .pay-btn { background: #444; color: white; border: 1px solid #888; padding: 15px; font-weight: bold; border-radius: 4px; cursor: pointer; }

    </style>
</head>
<body>

<div class="pos-container">
    <div class="top-menu">
        <button id="btn-floor" class="menu-btn active" onclick="showView('floor')">Table</button>
        <button id="btn-register" class="menu-btn" onclick="showView('register')">Register</button>
        <button class="menu-btn">Orders</button>
    </div>

    <div id="view-floor" class="view active">
        <h3 style="text-align: center;">Floor View</h3>
        <div class="floor-grid">
            <div class="table-card" onclick="openTable(1)">1</div>
            <div class="table-card" onclick="openTable(2)">2</div>
            <div class="table-card" onclick="openTable(3)">3</div>
            <div class="table-card" onclick="openTable(4)">4</div>
            <div class="table-card" onclick="openTable(5)">5</div>
            <div class="table-card" onclick="openTable(6)">6</div>
            <div class="table-card" onclick="openTable(7)">7</div>
        </div>
    </div>

    <div id="view-register" class="view">
        <div class="register-layout">
            
            <div class="product-section">
                <div class="category-bar">
                    <div class="cat-item cat-all active" onclick="filterCategory('all', this)">All</div>
                    <div class="cat-item cat-purple" onclick="filterCategory('quick-bites', this)">Quick Bites</div>
                    <div class="cat-item cat-gold" onclick="filterCategory('drinks', this)">Drinks</div>
                    <div class="cat-item cat-green" onclick="filterCategory('dessert', this)">Dessert</div>
                </div>
                
                <input type="text" class="search-bar" placeholder="Search product......" id="searchBar" onkeyup="searchProducts()">

                <div class="product-grid" id="productGrid">
                    <!-- Products will be loaded here by JS -->
                </div>
            </div>

            <div class="order-sidebar">
                <div class="order-list" id="orderList">
                    <!-- Order items will be added here -->
                </div>

                <div class="total-section">
                    <span>Total</span>
                    <span>$ <span id="totalPrice">0</span></span>
                </div>

                <div class="action-btns">
                    <button class="send-btn" onclick="sendOrder()">Send<br><small id="qtyDisplay">Qty: 0</small></button>
                    <button class="pay-btn" onclick="processPayment()">Payment</button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // ===== PRODUCT & CART LOGIC =====
    // Product Data
    const products = [
        { id: 1, name: 'Burger', price: 15, category: 'quick-bites' },
        { id: 2, name: 'Pizza', price: 250, category: 'quick-bites' },
        { id: 3, name: 'Maggie', price: 70, category: 'quick-bites' },
        { id: 4, name: 'Fries', price: 120, category: 'quick-bites' },
        { id: 5, name: 'Sandwich', price: 150, category: 'quick-bites' },
        { id: 6, name: 'Coffee', price: 50, category: 'drinks' },
        { id: 7, name: 'Tea', price: 35, category: 'drinks' },
        { id: 8, name: 'Diet Coke', price: 70, category: 'drinks' },
        { id: 9, name: 'Fanta', price: 60, category: 'drinks' },
        { id: 10, name: 'Milkshake', price: 140, category: 'drinks' },
        { id: 11, name: 'Ice Cream', price: 80, category: 'dessert' },
        { id: 12, name: 'Pudding', price: 90, category: 'dessert' },
        { id: 13, name: 'Cake', price: 120, category: 'dessert' },
    ];

    let cart = [];
    let currentFilter = 'all';
    let currentTable = null;

    function renderProducts() {
        const grid = document.getElementById('productGrid');
        grid.innerHTML = '';
        
        products.forEach(product => {
            if (currentFilter === 'all' || product.category === currentFilter) {
                const card = document.createElement('div');
                card.className = 'product-card';
                card.onclick = () => addToCart(product);
                card.innerHTML = `<div class="product-info"><span>${product.name}</span><span>$${product.price}</span></div>`;
                grid.appendChild(card);
            }
        });
    }

    function filterCategory(category, element) {
        currentFilter = category;
        
        // Update active button
        document.querySelectorAll('.cat-item').forEach(btn => btn.classList.remove('active'));
        element.classList.add('active');
        
        renderProducts();
    }

    function searchProducts() {
        const searchTerm = document.getElementById('searchBar').value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        
        cards.forEach(card => {
            const productName = card.querySelector('.product-info span').textContent.toLowerCase();
            if (productName.includes(searchTerm)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function addToCart(product) {
        // Check if product already in cart
        const existing = cart.find(item => item.id === product.id);
        
        if (existing) {
            existing.quantity++;
        } else {
            cart.push({ ...product, quantity: 1 });
        }
        
        updateOrderDisplay();
    }

    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        updateOrderDisplay();
    }

    function updateOrderDisplay() {
        const orderList = document.getElementById('orderList');
        orderList.innerHTML = '';
        
        let total = 0;
        let qty = 0;
        
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            qty += item.quantity;
            
            const orderItem = document.createElement('div');
            orderItem.className = 'order-item';
            orderItem.innerHTML = `
                <div class="item-details">
                    <div>${item.quantity} x ${item.name}</div>
                    <div style="font-size: 11px; color: #aaa;">$${item.price} each</div>
                </div>
                <div style="text-align: right;">
                    <div>$${itemTotal}</div>
                    <button class="item-remove" onclick="removeFromCart(${item.id})">Remove</button>
                </div>
            `;
            orderList.appendChild(orderItem);
        });
        
        document.getElementById('totalPrice').textContent = total;
        document.getElementById('qtyDisplay').textContent = `Qty: ${qty}`;
    }

    function sendOrder() {
        if (cart.length === 0) {
            alert('Please select items first');
            return;
        }
        
        if (!currentTable) {
            alert('Please select a table');
            return;
        }

        // Generate ticket number
        const ticketNumber = Math.floor(2200 + Math.random() * 9800);
        
        // Prepare order for kitchen display
        const order = {
            ticketNumber: ticketNumber,
            table: currentTable,
            items: cart.map(item => ({
                name: item.name,
                quantity: item.quantity,
                category: item.category,
                price: item.price
            })),
            status: 'pending',
            timestamp: new Date().toISOString()
        };
        
        // Get existing orders from localStorage
        const kitchenOrders = JSON.parse(localStorage.getItem('kitchenOrders')) || [];
        kitchenOrders.push(order);
        localStorage.setItem('kitchenOrders', JSON.stringify(kitchenOrders));
        
        // Clear cart
        cart = [];
        updateOrderDisplay();
        
        alert(`Order #${ticketNumber} sent to kitchen!`);
    }

    function processPayment() {
        if (cart.length === 0) {
            alert('Please select items first');
            return;
        }
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        alert('Processing payment: $' + total);
    }

    function showView(viewName) {
        // Hide all views
        document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
        document.querySelectorAll('.menu-btn').forEach(b => b.classList.remove('active'));

        // Show selected view
        document.getElementById('view-' + viewName).classList.add('active');
        document.getElementById('btn-' + viewName).classList.add('active');
    }

    function openTable(num) {
        currentTable = num;
        showView('register'); // Redirect to Register View
        renderProducts(); // Load products
    }

    // Initial render
    renderProducts();
</script>

</body>
</html>
