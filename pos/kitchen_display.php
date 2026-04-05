<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Kitchen Display System</title>
    <style>
        :root {
            --bg-dark: #121212;
            --card-bg: #1e1e1e;
            --header-bg: #252525;
            --primary-blue: #4da3ff;
            --text: #e0e0e0;
            --border: #333;
            --accent-orange: #ff9800;
        }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #000;
            color: var(--text);
            margin: 0;
            display: flex;
            height: 100vh;
        }

        /* --- Sidebar Filter Section --- */
        .sidebar {
            width: 250px;
            background: var(--card-bg);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 15px;
            color: var(--primary-blue);
            font-weight: bold;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            cursor: pointer;
        }

        .filter-group {
            padding: 10px 0;
        }

        .filter-title {
            background: #2a2a2a;
            padding: 8px 15px;
            font-size: 14px;
            color: #888;
            font-weight: bold;
        }

        .filter-item {
            padding: 10px 20px;
            cursor: pointer;
            transition: 0.2s;
            user-select: none;
        }

        .filter-item:hover { background: #333; }
        .filter-item.active { background: rgba(77, 163, 255, 0.2); color: var(--primary-blue); }

        /* --- Main Content Section --- */
        .main-display {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        /* Top Toolbar */
        .toolbar {
            background: var(--header-bg);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid var(--border);
        }

        .status-tab {
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            gap: 8px;
            align-items: center;
            transition: 0.2s;
        }

        .status-tab span {
            background: #444;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
        }

        .status-tab.active { 
            background: #333; 
            border-bottom: 2px solid var(--primary-blue);
            color: var(--primary-blue);
        }

        .search-box {
            margin-left: auto;
            background: #111;
            border: 1px solid var(--border);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            width: 200px;
        }

        /* --- Tickets Grid --- */
        .tickets-container {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            overflow-y: auto;
        }

        .ticket-card {
            background: var(--card-bg);
            border: 2px solid var(--primary-blue);
            border-radius: 4px;
            padding: 15px;
            transition: 0.3s;
            cursor: pointer;
            position: relative;
        }

        .ticket-card:hover { 
            border-color: #888;
            box-shadow: 0 0 15px rgba(77, 163, 255, 0.3);
        }

        .ticket-number {
            font-size: 20px;
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
            color: var(--primary-blue);
            background: #2a2a2a;
            padding: 8px;
            border-radius: 4px;
        }

        .table-badge {
            font-size: 12px;
            text-align: center;
            color: #888;
            margin-bottom: 10px;
        }

        .order-item {
            padding: 8px 0;
            border-bottom: 1px solid #2a2a2a;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            transition: 0.2s;
            user-select: none;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item.prepared {
            text-decoration: line-through;
            color: #666;
            opacity: 0.5;
        }

        .ticket-card.completed {
            border-color: var(--accent-orange);
            opacity: 0.4;
        }

        .empty-message {
            grid-column: 1 / -1;
            text-align: center;
            color: #666;
            padding: 40px;
            font-size: 18px;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header" onclick="clearAllFilters()">
            Clear Filter <span style="cursor:pointer">✕</span>
        </div>
        
        <div class="filter-group" id="productFilterGroup">
            <div class="filter-title">Products</div>
            <!-- Products will load here -->
        </div>
        
        <div class="filter-group" id="categoryFilterGroup">
            <div class="filter-title">Categories</div>
            <!-- Categories will load here -->
        </div>
    </div>

    <div class="main-display">
        <div class="toolbar">
            <div class="status-tab active" onclick="filterByStatus('all', this)">All <span id="countAll">0</span></div>
            <div class="status-tab" onclick="filterByStatus('pending', this)">To Cook <span id="countPending">0</span></div>
            <div class="status-tab" onclick="filterByStatus('preparing', this)">Preparing <span id="countPreparing">0</span></div>
            <div class="status-tab" onclick="filterByStatus('completed', this)">Completed <span id="countCompleted">0</span></div>

            <input type="text" class="search-box" id="searchBox" placeholder="Search ticket...">
        </div>

        <div class="tickets-container" id="kdsGrid">
            <div class="empty-message">No orders yet...</div>
        </div>
    </div>

    <script>
        // Products and Categories Data
        const products = [
            { id: 1, name: 'Burger', category: 'quick-bites' },
            { id: 2, name: 'Pizza', category: 'quick-bites' },
            { id: 3, name: 'Maggie', category: 'quick-bites' },
            { id: 4, name: 'Fries', category: 'quick-bites' },
            { id: 5, name: 'Sandwich', category: 'quick-bites' },
            { id: 6, name: 'Coffee', category: 'drinks' },
            { id: 7, name: 'Tea', category: 'drinks' },
            { id: 8, name: 'Diet Coke', category: 'drinks' },
            { id: 9, name: 'Fanta', category: 'drinks' },
            { id: 10, name: 'Milkshake', category: 'drinks' },
            { id: 11, name: 'Ice Cream', category: 'dessert' },
            { id: 12, name: 'Pudding', category: 'dessert' },
            { id: 13, name: 'Cake', category: 'dessert' },
        ];

        const categories = ['quick-bites', 'drinks', 'dessert'];
        
        let orders = JSON.parse(localStorage.getItem('kitchenOrders')) || [];
        let activeFilters = { products: [], categories: [] };
        let currentStatusFilter = 'all';

        // ===== Initialize =====
        function init() {
            loadFilters();
            renderOrders();
            updateCounts();
            
            // Refresh orders every 2 seconds
            setInterval(() => {
                orders = JSON.parse(localStorage.getItem('kitchenOrders')) || [];
                renderOrders();
                updateCounts();
            }, 2000);
        }

        // ===== Load Filters =====
        function loadFilters() {
            // Load Products
            const productGroup = document.getElementById('productFilterGroup');
            products.forEach(product => {
                const item = document.createElement('div');
                item.className = 'filter-item';
                item.textContent = product.name;
                item.onclick = () => toggleProductFilter(product.id, product.name, item);
                productGroup.appendChild(item);
            });

            // Load Categories
            const categoryGroup = document.getElementById('categoryFilterGroup');
            categories.forEach(cat => {
                const item = document.createElement('div');
                item.className = 'filter-item';
                item.textContent = cat.charAt(0).toUpperCase() + cat.slice(1).replace('-', ' ');
                item.dataset.category = cat;
                item.onclick = () => toggleCategoryFilter(cat, item);
                categoryGroup.appendChild(item);
            });
        }

        // ===== Filter Functions =====
        function toggleProductFilter(productId, productName, element) {
            const index = activeFilters.products.findIndex(p => p.id === productId);
            if (index > -1) {
                activeFilters.products.splice(index, 1);
                element.classList.remove('active');
            } else {
                activeFilters.products.push({ id: productId, name: productName });
                element.classList.add('active');
            }
            renderOrders();
        }

        function toggleCategoryFilter(category, element) {
            const index = activeFilters.categories.indexOf(category);
            if (index > -1) {
                activeFilters.categories.splice(index, 1);
                element.classList.remove('active');
            } else {
                activeFilters.categories.push(category);
                element.classList.add('active');
            }
            renderOrders();
        }

        function filterByStatus(status, element) {
            document.querySelectorAll('.status-tab').forEach(tab => tab.classList.remove('active'));
            element.classList.add('active');
            currentStatusFilter = status;
            renderOrders();
        }

        function clearAllFilters() {
            activeFilters = { products: [], categories: [] };
            currentStatusFilter = 'all';
            document.querySelectorAll('.filter-item').forEach(item => item.classList.remove('active'));
            document.querySelectorAll('.status-tab').forEach((tab, i) => {
                if (i === 0) tab.classList.add('active');
                else tab.classList.remove('active');
            });
            renderOrders();
        }

        // ===== Render Orders =====
        function renderOrders() {
            const grid = document.getElementById('kdsGrid');
            grid.innerHTML = '';

            let filteredOrders = orders.filter(order => {
                // Status filter
                if (currentStatusFilter !== 'all' && order.status !== currentStatusFilter) {
                    return false;
                }

                // Product filter
                if (activeFilters.products.length > 0) {
                    const hasProduct = order.items.some(item => 
                        activeFilters.products.some(p => p.name === item.name)
                    );
                    if (!hasProduct) return false;
                }

                // Category filter
                if (activeFilters.categories.length > 0) {
                    const hasCategory = order.items.some(item => 
                        activeFilters.categories.includes(item.category)
                    );
                    if (!hasCategory) return false;
                }

                return true;
            });

            if (filteredOrders.length === 0) {
                grid.innerHTML = '<div class="empty-message">No orders to display</div>';
                return;
            }

            filteredOrders.forEach((order, index) => {
                const card = document.createElement('div');
                card.className = 'ticket-card' + (order.status === 'completed' ? ' completed' : '');
                card.innerHTML = `
                    <div class="ticket-number">#${order.ticketNumber}</div>
                    <div class="table-badge">Table ${order.table}</div>
                    ${order.items.map(item => `
                        <div class="order-item" onclick="toggleStrike(event, this)">
                            <span>${item.quantity} x ${item.name}</span>
                        </div>
                    `).join('')}
                    <button onclick="markCompleted(${index})" style="width: 100%; margin-top: 10px; padding: 8px; background: var(--accent-orange); color: black; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                        Completed
                    </button>
                `;
                grid.appendChild(card);
            });
        }

        function toggleStrike(event, element) {
            event.stopPropagation();
            element.classList.toggle('prepared');
        }

        function markCompleted(index) {
            const filteredOrders = orders.filter(order => {
                if (currentStatusFilter !== 'all' && order.status !== currentStatusFilter) return false;
                if (activeFilters.products.length > 0) {
                    const hasProduct = order.items.some(item => 
                        activeFilters.products.some(p => p.name === item.name)
                    );
                    if (!hasProduct) return false;
                }
                if (activeFilters.categories.length > 0) {
                    const hasCategory = order.items.some(item => 
                        activeFilters.categories.includes(item.category)
                    );
                    if (!hasCategory) return false;
                }
                return true;
            });

            const originalIndex = orders.indexOf(filteredOrders[index]);
            orders[originalIndex].status = 'completed';
            localStorage.setItem('kitchenOrders', JSON.stringify(orders));
            renderOrders();
            updateCounts();
        }

        function updateCounts() {
            const counts = {
                all: orders.length,
                pending: orders.filter(o => o.status === 'pending').length,
                preparing: orders.filter(o => o.status === 'preparing').length,
                completed: orders.filter(o => o.status === 'completed').length
            };

            document.getElementById('countAll').textContent = counts.all;
            document.getElementById('countPending').textContent = counts.pending;
            document.getElementById('countPreparing').textContent = counts.preparing;
            document.getElementById('countCompleted').textContent = counts.completed;
        }

        // Initialize on page load
        init();
    </script>
</body>
</html>
