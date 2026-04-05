<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odoo POS Terminal</title>
    <style>
        :root {
            --bg-dark: #0a0e27;
            --card-bg: #1a1f3a;
            --header-bg: #10152b;
            --primary-purple: #7c3aed;
            --secondary-purple: #a855f7;
            --accent-pink: #ec4899;
            --text: #f5f5f7;
            --text-light: #b0b0b0;
            --border: #2d3748;
            --success: #10b981;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 100%);
            color: var(--text);
            margin: 0;
            display: flex;
            justify-content: center;
            padding: 20px;
            min-height: 100vh;
        }

        .pos-container {
            width: 1200px;
            background: linear-gradient(135deg, #0f1730 0%, #1a2847 100%);
            border: 1px solid var(--border);
            border-radius: 16px;
            min-height: 600px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
            overflow: hidden;
        }

        /* --- Top Menu --- */
        .top-menu {
            display: flex;
            background: linear-gradient(90deg, var(--header-bg) 0%, var(--primary-purple) 100%);
            padding: 15px 20px;
            gap: 10px;
            border-bottom: 2px solid var(--primary-purple);
        }

        .menu-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid transparent;
            color: var(--text);
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .menu-btn:hover {
            background: rgba(212, 212, 216, 0.15);
            transform: translateY(-2px);
        }

        .menu-btn.active {
            background: linear-gradient(135deg, var(--primary-purple), var(--secondary-purple));
            border-color: var(--accent-pink);
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4);
        }

        /* --- View Management --- */
        .view { display: none; padding: 30px; }
        .view.active { display: block; animation: fadeIn 0.3s ease; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- SCREEN 1: FLOOR VIEW --- */
        .floor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
            padding: 10px;
        }

        .table-card {
            width: 150px;
            height: 150px;
            border: 3px solid var(--primary-purple);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--text);
            cursor: pointer;
            transition: all 0.3s ease;
            background-position: center;
            background-size: cover;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.2);
        }

        .table-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
            transition: all 0.3s ease;
        }

        .table-card > * {
            position: relative;
            z-index: 2;
            font-weight: bold;
            background: rgba(0, 0, 0, 0.7);
            padding: 5px 10px;
            border-radius: 4px;
        }

        .table-card:hover { 
            transform: scale(1.08) translateY(-5px);
            box-shadow: 0 15px 40px rgba(124, 58, 237, 0.4);
            border-color: var(--accent-pink);
        }

        .table-card:hover::before {
            background: rgba(0, 0, 0, 0.3);
        }

        /* --- SCREEN 2: REGISTER VIEW --- */
        .register-layout {
            display: flex;
            gap: 20px;
            height: calc(100vh - 200px);
        }

        /* Left Side: Categories & Products */
        .product-section { 
            flex: 2;
            display: flex;
            flex-direction: column;
        }
        
        .category-bar { 
            display: flex; 
            gap: 12px; 
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .cat-item { 
            padding: 10px 18px; 
            cursor: pointer; 
            border-radius: 8px; 
            font-weight: 600;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-light);
            font-size: 14px;
        }
        
        .cat-item:hover {
            background: rgba(124, 58, 237, 0.2);
            border-color: var(--primary-purple);
        }
        
        .cat-item.active {
            border-color: var(--accent-pink);
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.3), rgba(168, 85, 247, 0.3));
            color: var(--text);
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }

        .search-bar { 
            width: 100%; 
            padding: 12px 16px; 
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid var(--border);
            color: var(--text);
            margin-bottom: 20px; 
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-bar:focus {
            outline: none;
            border-color: var(--primary-purple);
            background: rgba(124, 58, 237, 0.1);
            box-shadow: 0 0 15px rgba(124, 58, 237, 0.3);
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            flex: 1;
            overflow-y: auto;
            padding-right: 10px;
        }

        .product-grid::-webkit-scrollbar {
            width: 8px;
        }

        .product-grid::-webkit-scrollbar-track {
            background: transparent;
        }

        .product-grid::-webkit-scrollbar-thumb {
            background: rgba(124, 58, 237, 0.4);
            border-radius: 4px;
        }

        .product-grid::-webkit-scrollbar-thumb:hover {
            background: rgba(124, 58, 237, 0.6);
        }

        .product-card {
            background: linear-gradient(135deg, rgba(26, 31, 58, 0.8), rgba(30, 36, 66, 0.8));
            height: 180px;
            position: relative;
            border: 2px solid var(--border);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .product-card:hover {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.2), rgba(168, 85, 247, 0.2));
            transform: translateY(-8px);
            border-color: var(--primary-purple);
            box-shadow: 0 12px 30px rgba(124, 58, 237, 0.3);
        }

        .product-card.hidden {
            display: none;
        }

        .product-info {
            position: absolute;
            bottom: 0; 
            width: 100%;
            background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.9) 100%);
            display: flex;
            justify-content: space-between;
            padding: 8px 10px;
            font-size: 13px;
            box-sizing: border-box;
            flex-wrap: wrap;
        }

        .product-info span:first-child {
            font-weight: 700;
            color: var(--text);
            flex: 1;
        }

        .product-info span:last-child {
            color: var(--accent-pink);
            font-weight: 700;
        }

        /* Right Side: Order Summary */
        .order-sidebar {
            flex: 1;
            background: linear-gradient(135deg, rgba(26, 31, 58, 0.6), rgba(30, 36, 66, 0.6));
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        #orderList {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        #orderList::-webkit-scrollbar {
            width: 6px;
        }

        #orderList::-webkit-scrollbar-track {
            background: transparent;
        }

        #orderList::-webkit-scrollbar-thumb {
            background: rgba(124, 58, 237, 0.4);
            border-radius: 3px;
        }

        .order-item {
            background: rgba(124, 58, 237, 0.1);
            border-left: 4px solid var(--primary-purple);
            padding: 12px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
            border: 1px solid rgba(124, 58, 237, 0.2);
        }

        .order-item:hover {
            background: rgba(124, 58, 237, 0.15);
            border-color: rgba(124, 58, 237, 0.4);
        }

        .item-details {
            font-size: 13px;
            flex: 1;
        }

        .item-details div:first-child {
            font-weight: 600;
            color: var(--text);
            margin-bottom: 3px;
        }

        .item-details div:last-child {
            color: var(--text-light);
            font-size: 12px;
        }

        .item-remove {
            background: rgba(236, 72, 153, 0.2);
            color: var(--accent-pink);
            border: 1px solid var(--accent-pink);
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s ease;
            font-weight: 600;
        }

        .item-remove:hover {
            background: rgba(236, 72, 153, 0.4);
        }

        .total-section { 
            font-size: 18px; 
            display: flex; 
            justify-content: space-between;
            font-weight: 700;
            padding: 15px;
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.2), rgba(168, 85, 247, 0.2));
            border-radius: 8px;
            border: 1px solid rgba(124, 58, 237, 0.3);
            color: var(--accent-pink);
        }

        .action-btns { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 10px;
        }

        .send-btn { 
            background: linear-gradient(135deg, var(--primary-purple), var(--secondary-purple));
            color: var(--text);
            border: none; 
            padding: 15px; 
            font-weight: 700;
            border-radius: 8px; 
            cursor: pointer; 
            transition: all 0.3s ease;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }

        .send-btn:hover:not(:disabled) { 
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.5);
            background: linear-gradient(135deg, var(--secondary-purple), #c084fc);
        }

        .send-btn:disabled { 
            background: #555;
            color: #999; 
            cursor: not-allowed; 
            opacity: 0.5;
            box-shadow: none;
        }

        .pay-btn { 
            background: linear-gradient(135deg, #ff7f50, #ff6347);
            color: white; 
            border: none; 
            padding: 15px; 
            font-weight: 700;
            border-radius: 8px; 
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(255, 99, 71, 0.3);
        }

        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 99, 71, 0.5);
            background: linear-gradient(135deg, #ff6347, #ff4500);
        }

        h3 {
            margin: 0 0 20px 0;
            color: var(--text);
            font-size: 26px;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(124, 58, 237, 0.2);
        }

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
            <div class="table-card" onclick="openTable(1)" style="background-image: url('https://images.unsplash.com/photo-1552566626-52f8b0db4e04?w=200&h=200&fit=crop');"><span style="font-weight: bold; background: rgba(0,0,0,0.7); padding: 5px 10px; border-radius: 4px;">Table 1</span></div>
            <div class="table-card" onclick="openTable(2)" style="background-image: url('https://images.unsplash.com/photo-1552566626-52f8b0db4e04?w=200&h=200&fit=crop');"><span style="font-weight: bold; background: rgba(0,0,0,0.7); padding: 5px 10px; border-radius: 4px;">Table 2</span></div>
            <div class="table-card" onclick="openTable(3)" style="background-image: url('https://images.unsplash.com/photo-1552566626-52f8b0db4e04?w=200&h=200&fit=crop');"><span style="font-weight: bold; background: rgba(0,0,0,0.7); padding: 5px 10px; border-radius: 4px;">Table 3</span></div>
            <div class="table-card" onclick="openTable(4)" style="background-image: url('https://images.unsplash.com/photo-1552566626-52f8b0db4e04?w=200&h=200&fit=crop');"><span style="font-weight: bold; background: rgba(0,0,0,0.7); padding: 5px 10px; border-radius: 4px;">Table 4</span></div>
            <div class="table-card" onclick="openTable(5)" style="background-image: url('https://images.unsplash.com/photo-1552566626-52f8b0db4e04?w=200&h=200&fit=crop');"><span style="font-weight: bold; background: rgba(0,0,0,0.7); padding: 5px 10px; border-radius: 4px;">Table 5</span></div>
            <div class="table-card" onclick="openTable(6)" style="background-image: url('https://images.unsplash.com/photo-1552566626-52f8b0db4e04?w=200&h=200&fit=crop');"><span style="font-weight: bold; background: rgba(0,0,0,0.7); padding: 5px 10px; border-radius: 4px;">Table 6</span></div>
            <div class="table-card" onclick="openTable(7)" style="background-image: url('https://images.unsplash.com/photo-1552566626-52f8b0db4e04?w=200&h=200&fit=crop');"><span style="font-weight: bold; background: rgba(0,0,0,0.7); padding: 5px 10px; border-radius: 4px;">Table 7</span></div>
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
                    <button class="send-btn" id="sendBtn" onclick="sendOrder()" disabled>Send<br><small id="qtyDisplay">Qty: 0</small></button>
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
        { id: 1, name: 'Burger', price: 15, category: 'quick-bites', image: 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop' },
        { id: 2, name: 'Pizza', price: 250, category: 'quick-bites', image: 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=400&h=300&fit=crop' },
        { id: 3, name: 'Maggie', price: 70, category: 'quick-bites', image: 'data:image/jpeg;base64,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' },
        { id: 4, name: 'Fries', price: 120, category: 'quick-bites', image: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBwgHBgkIBwgKCgkLDRYPDQwMDRsUFRAWIB0iIiAdHx8kKDQsJCYxJx8fLT0tMTU3Ojo6Iys/RD84QzQ5OjcBCgoKDQwNGg8PGjclHyU3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3N//AABEIAJQAlAMBIgACEQEDEQH/xAAbAAABBQEBAAAAAAAAAAAAAAAFAAIDBAYBB//EADcQAAEDAwIEAwUIAgIDAAAAAAECAwQABRESIQYTMUEiUWEUMnGBkRUjQlKhscHhM9FighZDU//EABkBAAIDAQAAAAAAAAAAAAAAAAECAAMEBf/EACgRAAICAgIBBAIBBQAAAAAAAAABAgMREiExBBMiQVEUMmEjUnGRof/aAAwDAQACEQMRAD8AyOKWKeE0/RXMOoQ06n8unBo0CDEnBzVht40wMmpEsHNK8BRMhedzRa3XH2YgkZxQpDRqXlGlGNazxWhAwWz9atN8YN/kP1rDlo+tIN0dmganoSOL2iP8Z+tV5fEyHk4CSD6msUAR3Nd+dRzZNUGTNBe5mRnNE2eIQ2nBSSfjWUruaXYODTPcRauiapu3wke7QSujFRyJguSLip38ND3PGc1JtS2oZIQ6aVT5FKpkIEBp4NVw5XeZVxWWRUgqqHRTg9QwEuoqVOKHpfq1CS9NkojRUFx1fRIpXwFFtFSZFcmWu6wUFcqE8lsdVgagPmKoiUPOlTT6GLqqZmq3tGe9c5486mCFrNLNNZjy38cmJIcz+Rsn9qY+l2O4W5Da2nB1StODS8ZIS6qWarhyn6yRqAJGcZA2ogJs0s1X5tSJXUZCSlXNQrmaATtKuE1yoAz+KVFoVlkrUDIivBHwoi9Y47jf3asEDoK0bIVJmYrorQR+FJUmKXm5MfXvpaJOT86G3C0T7aAZkVxCD0cxlP1pI3VyeqfIXCS7RSoxwi6GuIIh1AAkg7+YND4C2W5rCpLfMZDiStGfeFepQZNr0JWxCjNYHgIQMj51n8u+NccNdj1wlLlBdKtbRT+Eggg9DXlEazTJrsr2BrnJYWU4B3O+2K9SjOoWjbzpWe02myuOSmS6HHd1ILhIJ88Vz/Cs0zyNatfg85tPCl4uMoMCI7HSPfdfQUpR/ut3beF7RZRzN5stI2cdA0pPoKJTryXPCDpR6VQZkqlPBtkFe++O1Nf523tr/wBixom1tPhF4Lyg61HbtmvOeM3mHbt9yoFSEaV+npW04hs9xfiIbgPIZBVlxxbuk48qG2vgiEmVzLhKW+2f/WnbJ75PlS+LBVSU7HhhlKOHqBeCuHFXucXJLavYWf8AIT0Ueyf916Y5DiCPyER20xwMBsJAGKnYaiQYiYsFlDMdA2CaC3qTLVFeRbdJdSNtXTNaPJtWdUZ4bWSyeecVRoMS6Kbt5GjGVoHRJ9KFoNEbbYLpd5L33Zb5Zy889sAf5NUpsdcKY9GWoKLasagMZrVXhJRzllzOg0s1Ek07NPgA8mlTNVcqYIevtttrJTyxj4VSncNRJqVFA5Th/GnY0JkcRtRXcc0EelSQ+LGpc1qNGQXHHDgDy9apXHLJ6c+0Pt/CYgh0tTFre6+M7fSuKdQ8lyJLaSQfC40odavzHJGC/FSVKHVB2z8KyM++KfuCOYwW3UnSo9/nWG+Cm948M1UqUva+gde+EH4yXZdvUlyKPwE+NI/mtJwpYpKoEVTyS1pGVle4Iorak8+MXHtmCN/WpXZj0hCmY3gZQMACjK/1K0rEDEoSagD5o9nkqDL+tI8hgUNenuKWUtkqVVe5TFB8tKynfBzUMp1JigNglaTsQevpWFptm+uvGNgxam3JTq1OJ5qW+u4CQe1QLdftcvmrRpQrI2IomxAdj21tnTgpGpwjoVHrWeuCNcoJVuQNxnpTuCQkGpyf0X3bs3IeZIJUEJOodqM2y4Jd8CSSBWIdlGM8WHWg2OqR01D+avWa4oRKVuQCO9LKtp7Fk6IOGEbO7SZDMTUwnPY47Vy2p5MQFRBcVucnuaZFmCUynSN+hFDnFuRJi46jhKvGFeY8qkm/3RijBtOHyX/a3EctlxBQCvfyNZLjWBi7svNDHtCcHy1D+q2SwmXESsdRhQPrQLihh2bAaEVJXIQ4nQkdcnb+a0+NNwsWWI45XAHi8LuPN61OfICoX7A6ydkrIrTwWbzY4inZjCJCcZ5bC8qSPngVnHeP2lrWn2ZSck7EfpXX3i17RK/Hssft5RT+y1/lXXKtR+JmHkFagAc9K5Q3Lvwrfozb7hEdKh86JcKQblOnodtgSFsnJWs4G/ahKIsyXblSGIrrjDey1pGQK2vBcmHEtuI8hJSVfeKX4CpWBsfhVF0tUOm9eOwu5dpkZ3kzkaHkjp1Sr1zVuI2zPUmU+0z4fccVjPrXLtcYX2cr2ltp44w2lOxz8ayLF6VAkI0LG59xYyKwtNS4eQwrc4NpYZtbgzLEcCMxmOkZGncn5dazkmStD/s48BTvsDkH50atfF0V0pDzeF+h2oo7drZJaOooXkY5enxfPNCUIPnYrjZbU9ZQyYO4xpUmMZYSo4OCruofCp+Do32hM5jwyzGwT5FXb/daJyWw40pmNGDCQMJIxj9KtRIiIEAIISl1R1vEAAlWOny2FCuUWuPgssvlpq1hsV0lIjtKJwkJGTk1581duZPW64fCpX6UZ4nlqkOCK0c91Y7CszPs9xjs+3Mx1uR0++U48I88eVPXFWN5LaYquHJtFW2JdoQGonB1JX+JB9Kz9yhv29YQ+NJ6tr7K+FT8I3pMZ7Lh1NqSDitdcrpHuNvcbUw2pvSdsZPSlesFiT5Fc7a54SyjOcN3MofCVEAHwn0rS3KEbjHa5ZCXE4UFHpjvXnkBqSbkzHjIK1urAA8j5/CvUG8R4jbCl6lBPiX5mg4YX8MTyGozTj2VIkZcNvl6lOJO+SBiqzriYVxZfUohhR6gdKnuNwSw3hQ0oH4ietRNcqdbUg+LB+YNU/wvgSO37S+Qi7dkyFFSUHl9NR/1Xl9/4TvNxuE65MMR0M6yUILgSpQHfH91tXZbcNlSSrKxtp86zU6bcUtSXmHV+AFXLScA1povlGf+S6mpw/Tg88Dqsb9fhSrikrWorIJKjknB3NKuzqjaps9Q4ZvrbENmI22kRgkBkA9e+57mob1Hh8tx2KwmPrVqUhPuqP7A1lbBNYcszSUAJ0eFQz0V50b+0QmIqO8wglSdnFJ3rlWxmptGWuMeJx+Tj6VCG0sZKEnqTmqcnlutDWDkdD3FPhynUJVES2t9tzYNpBKgfStrwzw6zb2vtC9gB3GplhQ9z1Pr+1CEH3/0a3yI1LJn4HDMwWwzXEFttPiAXspQ8/T4VKy+02Mu5AHXxUS4mv6pf3SFaWU9Eg9fjWGlXDmvckK0gncnypJ1q2Xt6BVOyUcz4NdbppkytbYwy0cj1NFL1deRGUonUs7AVmYMyNHjIbacT4d8Z60IuN4ccuTBSrwtrBz2pIU5k4roWUFKWSaNcXY11MqW2FoUoEoWNvQGt07xExcIxZQgMtlG4T0NQokW28xke3RwpQ21p2JFY3ih6LY7ihqGpQYcb1ICjkp3wRVqfqPWDwxcQnL3rooBCoc11tvZAWSnHlVhy+So76IjbWSsgA5znPlVG3TE3B91wg6tQwD1NbWDw+1EbTLlJC5enABH+MH+atsSi/6i5LJ2pIKcKtNwwpxwJ9pd7nqB5UVmvAk4A1dMg0DDyWAdXX17UKm3V/29koURHQr6n1rG8y9pnjVvLYscZx3/ALOjTEKIDbuHE+h6H6/vXeF7skAIcUNCvCa0U1hFzsbzWBl1BA9D2P1rzO2rebSStKk+LB9D5VbptWvtF1T3g4s9El2tuW6lzmacjfHehxZjxJARJILIO/qPWn2i4c6Jy1r8QBwSakcgMyE4cfBUem/U1RGSU02D3JNMmWzw5kaW4+Mdk0qGiAkbY6V2uv8Amr6Mv47/ALmArZwk3aZPM9pRIZOxaLZTlXbO5GB1oi9b406WEypAYSB4l98eQHenzXXm20tvAJUDqO/pQN+4hDxWrJxjArnb22WbM2VRxDCN5Gm2+0RORaGAgYyp1W61+pNArvfgokKdKlK2AJzQH7TS82Tq+WarxeVIuTKnM6ELClGm1nN5s6FhRGGX2y7eWXYwHN3UpOo46CsbIfS1MSVDsR9a9I4qu8Z+H7PGbTk++5jrjsKycXhly4whN5ai2snCirAODg/sa1eO4Qz9E2k48glqZrdATtmibcQuJJCScDJxQaZCVbZqmySUjBQT3Fam0zUKjpQfcUQVDzp7/YlKA0G5dnbTKf1GOMJWg9CetDeK4b705l1agUcrTnyOckfrRC4IdRd3HobK1M4G6d6rXCWHQkOAgoOrB61TX7bFOK7C1twwnwNYURVLuEkYVj7pKu3rWwcmttx1BboUe4Fee/bB0aQ+oJx3NdXcZ82KUWmM6+gHDjycfQZ60s67bZZZTKEflhC93IqaWWXMBCsn1oOq6Kdwkq6mhk2RMisKTJhPshQIytsgGqtlQ5LkpJOG07k+vlWmHiqNbbGViTSR6zZbyhFqQhSVKXnGDQ24MtNl57w8t4nKR2Jqpa3mW1YcPhA2q0qSw4oAHYfm6ZrmPOS2MVFtoGwVraWUoJV6+Qo1EfdQ4lzSrbuRUbNxSwrStSFIPXGKvi6Q9BBV4T2G+k0s47cglZnjBaUpTqipDZweuOmaVT29aX4iHG0kIPTbGaVLqxNjCPxrnHeeLrv3jqsr5iOpoc/bZbxOVt59D/VerOIbcTpcQlSfJQrMX20ezZkRyrlHqkH3a6/Gc4M8bH0YV21TUnKDk+aSKY01Piu6yhSs9QBR15S0baz86oOvvA7FJqxcrA27KUqdJWCksOfStNwZf3ofD5t7rSCUOrKdQ3wo5/k1nlzHh1SP1qJU1efdIPcg0J1Zg4x4Bsm/cEOLpBlRkuDGtDg2A7EH+qzke4vx/ChskdvSr5lhQIWlRB896jLzPdGPiKeqGkNZLJJz5zEJWS6qU6SpR1Z71uIc+O+2UvNtnHTUgHNeZhTAVqTsrzFXW7o637jlZPI8TZ5gWRsTWJBfjSxRZsZc2AwhqS0klaUDAcSOu3nQvh66ogQ2mG3dH5t9ia4brIUlY5pwsYNUW+W2sLSkZByMjNXQrm6vTseRNlGe0Q1xRcnpcFLKEuO5IJ0pJoBCW5DbwGlp37oNFk3yalOkPgDsAgU37ZmE/wCbP/UU9cFCGgsptyyWIb8lxrMeI86s9AlBq6LRfJi06IimRjcurCf061Qbvs9IwJCwPQCpBdJb5wt11zPZSzj6UnpxXOB/Vn0Ev/GpzeDImx2j3Gc/vRzhy18tSg9ypODsvRun5Hb9KD2mPLlOoSCGwry6mt3GYTFZS02MYG57k1musS4wDL+yxgJASAMAYFKotRpViyTBGF5704pS4kpWMoOxHnVRKiKl5oQnUs4A7102Z0Y3iWCq2ua07sL90+XpWVekJJ3Ax6UZ464uS+2qBCSlSAfGs7/SsIm4BZ8RwfI1qpqk1lgndFcBpTyc9VCmFwf/AEoamRnv9K7z/UfSr/TYqsTL+onuDTT8BVLmfD5UuafX5Gpow7ot49KQ+FVg6fNVPD//ACNDVjKSLI+FdqsHj+Y08PH0pGhlJFhNSJT6VWDhqVDnrSNDJlptsnpRaCxuCpQA/WhLG+N/1rRWePrUCegGdqz2PCLYrJrOHGY7KSsrHNOw1HcCjasdqyCsp771wTX2vccUPnWCcXJ5DqasjelWaTfZKRgkH5UqT05EwwstRQnIx86w3GV4mIQphtwIQeunY12lXZglkxvo89kkqzk5qipIpUq6EOjHb2N1KT0UalQ8vbeuUqtwUpsnS4onenBZrlKkaQ6bHhR8zTgtXnSpVXIti2ODivOnpcVSpVW0XJsmQok1O2TSpVVMugX4m6unStXZ3FFoetcpViv6NVQUVuKquClSrMOQGlSpUQH/2Q==' },
        { id: 5, name: 'Sandwich', price: 150, category: 'quick-bites', image: 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=400&h=300&fit=crop' },
        { id: 6, name: 'Coffee', price: 50, category: 'drinks', image: 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=400&h=300&fit=crop' },
        { id: 7, name: 'Tea', price: 35, category: 'drinks', image: 'data:image/jpeg;base64,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' },
        { id: 8, name: 'Diet Coke', price: 70, category: 'drinks', image: 'data:image/jpeg;base64,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' },
        { id: 9, name: 'Fanta', price: 60, category: 'drinks', image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKmumuYQy9DChtja6g8j8dFT7RFXN_EM8Vfw&s' },
        { id: 10, name: 'Milkshake', price: 140, category: 'drinks', image: 'data:image/jpeg;base64,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' },
        { id: 11, name: 'Ice Cream', price: 80, category: 'dessert', image: 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=400&h=300&fit=crop' },
        { id: 12, name: 'Pudding', price: 90, category: 'dessert', image: 'data:image/jpeg;base64,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' },
        { id: 13, name: 'Cake', price: 120, category: 'dessert', image: 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&h=300&fit=crop' },
    ];

    let cart = [];
    let currentFilter = 'all';
    let currentTable = null;
    let paymentConfirmed = false;

    function renderProducts() {
        const grid = document.getElementById('productGrid');
        grid.innerHTML = '';
        
        products.forEach(product => {
            if (currentFilter === 'all' || product.category === currentFilter) {
                const card = document.createElement('div');
                card.className = 'product-card';
                card.onclick = () => addToCart(product);
                card.innerHTML = `
                    <img src="${product.image}" alt="${product.name}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 12px 12px 0 0; flex-shrink: 0;">
                    <div class="product-info"><span>${product.name}</span><span>$${product.price}</span></div>
                `;
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
        if (!paymentConfirmed) {
            alert('Please complete payment first');
            return;
        }
        
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
        
        // Disable Send button after order is sent
        paymentConfirmed = false;
        document.getElementById('sendBtn').disabled = true;
        
        alert(`Order #${ticketNumber} sent to kitchen!`);
    }

    function processPayment() {
        if (cart.length === 0) {
            alert('Please select items first');
            return;
        }

        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        // Store cart and payment info in sessionStorage
        sessionStorage.setItem('paymentAmount', total.toFixed(2));
        sessionStorage.setItem('cartItems', JSON.stringify(cart));
        sessionStorage.setItem('currentTable', currentTable);
        sessionStorage.setItem('userName', 'Admin');
        
        // Redirect to payment page
        window.location.href = 'payment.php?amount=' + total.toFixed(2);
    }

    // Check if payment was completed
    function checkPaymentCompletion() {
        if (new URLSearchParams(window.location.search).get('paymentComplete') === 'true') {
            // Restore cart and table from sessionStorage
            const savedCart = sessionStorage.getItem('cartItems');
            const savedTable = sessionStorage.getItem('currentTable');
            
            if (savedCart) {
                cart = JSON.parse(savedCart);
            }
            if (savedTable) {
                currentTable = parseInt(savedTable);
            }
            
            paymentConfirmed = true;
            document.getElementById('sendBtn').disabled = false;
            alert('✓ Payment Confirmed! Now click Send button to send order to kitchen.');
            
            // Show register view directly
            showView('register');
            updateOrderDisplay();
            
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
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
    
    // Check payment completion first
    checkPaymentCompletion();
    
    // Check if we need to restore from sessionStorage for multi-order
    const restoredCart = sessionStorage.getItem('cartItems');
    const restoredTable = sessionStorage.getItem('currentTable');
    if (restoredCart && !new URLSearchParams(window.location.search).get('paymentComplete')) {
        sessionStorage.removeItem('cartItems');
        sessionStorage.removeItem('currentTable');
    }
</script>

</body>
</html>
