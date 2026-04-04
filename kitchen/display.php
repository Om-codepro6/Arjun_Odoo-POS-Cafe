<?php
// ============================================
// KITCHEN DISPLAY (SCREEN 11 from prompt)
// Three columns: To Cook | Preparing | Completed
// Orders shown as cards that move between stages
// ============================================

include '../includes/auth_check.php';
include '../config/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display - Odoo Cafe</title>
    <link rel="stylesheet" href="../pos/pos_style.css">
    <style>
        /* ===== KITCHEN DISPLAY SPECIFIC STYLES ===== */
        
        .kitchen-layout {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            padding: 25px;
            min-height: calc(100vh - 60px);
            background: #f4f0eb;
        }

        /* Each column */
        .kitchen-column {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .kitchen-column-header {
            padding: 18px 20px;
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid;
        }

        .kitchen-column-header .count {
            background: rgba(0,0,0,0.08);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        /* Column colors */
        .col-tocook .kitchen-column-header {
            background: rgba(232, 134, 74, 0.08);
            border-color: #e8864a;
            color: #b35a1f;
        }

        .col-preparing .kitchen-column-header {
            background: rgba(245, 166, 35, 0.08);
            border-color: #f5a623;
            color: #8b6914;
        }

        .col-completed .kitchen-column-header {
            background: rgba(45, 106, 79, 0.08);
            border-color: #2d6a4f;
            color: #2d6a4f;
        }

        .kitchen-column-body {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Order Card in Kitchen */
        .kitchen-order-card {
            background: #fdf6ee;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .kitchen-order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            border-color: #e8864a;
        }

        .kitchen-order-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .kitchen-order-id {
            font-weight: 700;
            font-size: 0.95rem;
            color: #3b1f0b;
        }

        .kitchen-order-table {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 6px;
            background: #3b1f0b;
            color: white;
        }

        .kitchen-order-time {
            font-size: 0.78rem;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .kitchen-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 0;
            font-size: 0.88rem;
            color: #1a1a1a;
            cursor: pointer;
        }

        .kitchen-item:hover {
            color: #e8864a;
        }

        /* Done items get strike-through */
        .kitchen-item.done {
            text-decoration: line-through;
            color: #6b7280;
        }

        .kitchen-item-qty {
            font-weight: 700;
            color: #5c3317;
            min-width: 30px;
        }

        .kitchen-move-btn {
            display: block;
            width: 100%;
            padding: 8px;
            margin-top: 10px;
            border: none;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
        }

        .btn-to-preparing {
            background: #f5a623;
            color: white;
        }
        .btn-to-preparing:hover { background: #d4911e; }

        .btn-to-completed {
            background: #2d6a4f;
            color: white;
        }
        .btn-to-completed:hover { background: #245a42; }

        /* Responsive */
        @media (max-width: 900px) {
            .kitchen-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <?php include '../includes/header.php'; ?>

    <!-- Kitchen Display Layout: 3 Columns -->
    <div class="kitchen-layout">

        <!-- Column 1: To Cook -->
        <div class="kitchen-column col-tocook">
            <div class="kitchen-column-header">
                🔥 To Cook
                <span class="count" id="tocookCount">3</span>
            </div>
            <div class="kitchen-column-body" id="tocookCol">

                <!-- Order Card 1 -->
                <div class="kitchen-order-card" id="order-2205">
                    <div class="kitchen-order-top">
                        <span class="kitchen-order-id">#2205</span>
                        <span class="kitchen-order-table">Table 3</span>
                    </div>
                    <div class="kitchen-order-time">⏱ 2 min ago</div>
                    <div class="kitchen-item" onclick="toggleItem(this)">
                        <span class="kitchen-item-qty">1x</span> Burger
                    </div>
                    <div class="kitchen-item" onclick="toggleItem(this)">
                        <span class="kitchen-item-qty">2x</span> Fries
                    </div>
                    <div class="kitchen-item" onclick="toggleItem(this)">
                        <span class="kitchen-item-qty">1x</span> Coffee
                    </div>
                    <button class="kitchen-move-btn btn-to-preparing" onclick="moveCard('order-2205', 'preparingCol')">
                        ➜ Start Preparing
                    </button>
                </div>

                <!-- Order Card 2 -->
                <div class="kitchen-order-card" id="order-2204">
                    <div class="kitchen-order-top">
                        <span class="kitchen-order-id">#2204</span>
                        <span class="kitchen-order-table">Table 1</span>
                    </div>
                    <div class="kitchen-order-time">⏱ 5 min ago</div>
                    <div class="kitchen-item" onclick="toggleItem(this)">
                        <span class="kitchen-item-qty">1x</span> Pizza
                    </div>
                    <div class="kitchen-item" onclick="toggleItem(this)">
                        <span class="kitchen-item-qty">1x</span> Milkshake
                    </div>
                    <button class="kitchen-move-btn btn-to-preparing" onclick="moveCard('order-2204', 'preparingCol')">
                        ➜ Start Preparing
                    </button>
                </div>

                <!-- Order Card 3 -->
                <div class="kitchen-order-card" id="order-2203">
                    <div class="kitchen-order-top">
                        <span class="kitchen-order-id">#2203</span>
                        <span class="kitchen-order-table">Table 6</span>
                    </div>
                    <div class="kitchen-order-time">⏱ 8 min ago</div>
                    <div class="kitchen-item" onclick="toggleItem(this)">
                        <span class="kitchen-item-qty">2x</span> Sandwich
                    </div>
                    <div class="kitchen-item" onclick="toggleItem(this)">
                        <span class="kitchen-item-qty">1x</span> Water
                    </div>
                    <div class="kitchen-item" onclick="toggleItem(this)">
                        <span class="kitchen-item-qty">1x</span> Brownie
                    </div>
                    <button class="kitchen-move-btn btn-to-preparing" onclick="moveCard('order-2203', 'preparingCol')">
                        ➜ Start Preparing
                    </button>
                </div>

            </div>
        </div>

        <!-- Column 2: Preparing -->
        <div class="kitchen-column col-preparing">
            <div class="kitchen-column-header">
                👨‍🍳 Preparing
                <span class="count" id="preparingCount">1</span>
            </div>
            <div class="kitchen-column-body" id="preparingCol">

                <!-- Order Card already being prepared -->
                <div class="kitchen-order-card" id="order-2202">
                    <div class="kitchen-order-top">
                        <span class="kitchen-order-id">#2202</span>
                        <span class="kitchen-order-table">Table 2</span>
                    </div>
                    <div class="kitchen-order-time">⏱ 12 min ago</div>
                    <div class="kitchen-item done" onclick="toggleItem(this)">
                        <span class="kitchen-item-qty">1x</span> Maggi ✓
                    </div>
                    <div class="kitchen-item" onclick="toggleItem(this)">
                        <span class="kitchen-item-qty">1x</span> Fanta
                    </div>
                    <div class="kitchen-item" onclick="toggleItem(this)">
                        <span class="kitchen-item-qty">1x</span> Vada Pav
                    </div>
                    <button class="kitchen-move-btn btn-to-completed" onclick="moveCard('order-2202', 'completedCol')">
                        ✓ Mark Complete
                    </button>
                </div>

            </div>
        </div>

        <!-- Column 3: Completed -->
        <div class="kitchen-column col-completed">
            <div class="kitchen-column-header">
                ✅ Completed
                <span class="count" id="completedCount">1</span>
            </div>
            <div class="kitchen-column-body" id="completedCol">

                <!-- Completed Order -->
                <div class="kitchen-order-card" id="order-2201" style="opacity: 0.7;">
                    <div class="kitchen-order-top">
                        <span class="kitchen-order-id">#2201</span>
                        <span class="kitchen-order-table">Table 5</span>
                    </div>
                    <div class="kitchen-order-time">⏱ 25 min ago</div>
                    <div class="kitchen-item done">
                        <span class="kitchen-item-qty">2x</span> Coffee ✓
                    </div>
                    <div class="kitchen-item done">
                        <span class="kitchen-item-qty">1x</span> Cake ✓
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- JavaScript: Kitchen Logic -->
    <script>
        // Toggle item done/not-done (strike-through)
        function toggleItem(el) {
            el.classList.toggle('done');
        }

        // Move an order card from one column to another
        function moveCard(cardId, targetColId) {
            var card = document.getElementById(cardId);
            var targetCol = document.getElementById(targetColId);

            if (!card || !targetCol) return;

            // Remove the move button and add the next one
            var moveBtn = card.querySelector('.kitchen-move-btn');
            if (moveBtn) moveBtn.remove();

            // Add the appropriate button based on target
            if (targetColId === 'preparingCol') {
                var newBtn = document.createElement('button');
                newBtn.className = 'kitchen-move-btn btn-to-completed';
                newBtn.textContent = '✓ Mark Complete';
                newBtn.onclick = function() { moveCard(cardId, 'completedCol'); };
                card.appendChild(newBtn);
            } else if (targetColId === 'completedCol') {
                // Mark all items as done
                card.querySelectorAll('.kitchen-item').forEach(function(item) {
                    item.classList.add('done');
                });
                card.style.opacity = '0.7';
            }

            // Move the card
            targetCol.appendChild(card);

            // Update counts
            updateCounts();
        }

        // Update the counter badges
        function updateCounts() {
            document.getElementById('tocookCount').textContent = document.getElementById('tocookCol').children.length;
            document.getElementById('preparingCount').textContent = document.getElementById('preparingCol').children.length;
            document.getElementById('completedCount').textContent = document.getElementById('completedCol').children.length;
        }
    </script>

</body>
</html>
