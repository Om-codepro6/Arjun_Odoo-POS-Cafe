<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Category Manager</title>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <style>
        :root {
            --bg-dark: #121212;
            --card-bg: #1e1e1e;
            --header-bg: #252525;
            --primary-purple: #a24689;
            --border: #333;
            --text: #e0e0e0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text);
            display: flex;
            justify-content: center;
            padding-top: 50px;
        }

        .manager-container {
            width: 800px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            overflow: hidden;
        }

        /* Top Header */
        .top-nav {
            background: var(--header-bg);
            padding: 10px 20px;
            display: flex;
            gap: 20px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            color: #888;
        }

        .action-bar {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-new {
            background: var(--primary-purple);
            color: white;
            border: none;
            padding: 6px 18px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .category-label {
            color: #4da3ff;
            font-weight: bold;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            text-align: left;
            background: #2a2a2a;
            padding: 12px 20px;
            font-size: 13px;
            color: #bbb;
            border-bottom: 1px solid var(--border);
        }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
        }

        tbody tr:hover {
            background: #252525;
        }

        td {
            padding: 10px 20px;
        }

        .drag-handle {
            cursor: grab;
            color: #666;
            font-weight: bold;
            width: 30px;
        }

        .category-input {
            background: transparent;
            border: none;
            color: white;
            width: 100%;
            font-size: 16px;
            outline: none;
        }

        /* Color Picker Logic */
        .color-cell {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .color-dot {
            height: 18px;
            width: 18px;
            border-radius: 50%;
            border: 1px solid #fff;
            cursor: pointer;
            display: inline-block;
        }

        .palette-popup {
            display: none;
            position: absolute;
            top: 30px;
            left: 0;
            background: #333;
            padding: 8px;
            border-radius: 4px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            z-index: 10;
            gap: 8px;
        }

        .palette-popup.active {
            display: flex;
        }

        /* Delete Button */
        .btn-delete {
            background: white;
            border: none;
            padding: 5px;
            cursor: pointer;
            border-radius: 3px;
            display: flex;
            align-items: center;
        }

        .btn-delete:hover {
            background: #ffcccc;
        }

        /* Ghost class for Drag & Drop */
        .sortable-ghost {
            opacity: 0.4;
            background: var(--primary-purple);
        }
    </style>
</head>
<body>

<div class="manager-container">
    <div class="top-nav">
        <span>Orders</span>
        <span>Products</span>
        <span>Reporting</span>
    </div>

    <div class="action-bar">
        <button class="btn-new" onclick="addNewRow()">New</button>
        <span class="category-label">Category</span>
    </div>

    <table>
        <thead>
            <tr>
                <th width="50"></th>
                <th>Product Category</th>
                <th width="200">Color</th>
                <th width="50"></th>
            </tr>
        </thead>
        <tbody id="categoryList">
            <tr>
                <td class="drag-handle">::</td>
                <td><input type="text" class="category-input" value="Quick Bites"></td>
                <td class="color-cell">
                    <span class="color-dot" style="background-color: #ffffff;" onclick="togglePalette(this)"></span>
                    <div class="palette-popup">
                        <span class="color-dot" style="background-color: #2e7d32;" onclick="selectColor(this)"></span>
                        <span class="color-dot" style="background-color: #c62828;" onclick="selectColor(this)"></span>
                        <span class="color-dot" style="background-color: #6a1b9a;" onclick="selectColor(this)"></span>
                        <span class="color-dot" style="background-color: #ef6c00;" onclick="selectColor(this)"></span>
                        <span class="color-dot" style="background-color: #1565c0;" onclick="selectColor(this)"></span>
                    </div>
                </td>
                <td><button class="btn-delete" onclick="deleteRow(this)">🗑️</button></td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    // 1. Initialize Drag and Drop
    const el = document.getElementById('categoryList');
    Sortable.create(el, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost'
    });

    // 2. Function to Add New Row
    function addNewRow() {
        const tbody = document.getElementById('categoryList');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td class="drag-handle">::</td>
            <td><input type="text" class="category-input" placeholder="Enter Category Name"></td>
            <td class="color-cell">
                <span class="color-dot" style="background-color: #ffffff;" onclick="togglePalette(this)"></span>
                <div class="palette-popup">
                    <span class="color-dot" style="background-color: #2e7d32;" onclick="selectColor(this)"></span>
                    <span class="color-dot" style="background-color: #c62828;" onclick="selectColor(this)"></span>
                    <span class="color-dot" style="background-color: #6a1b9a;" onclick="selectColor(this)"></span>
                    <span class="color-dot" style="background-color: #ef6c00;" onclick="selectColor(this)"></span>
                    <span class="color-dot" style="background-color: #1565c0;" onclick="selectColor(this)"></span>
                </div>
            </td>
            <td><button class="btn-delete" onclick="deleteRow(this)">🗑️</button></td>
        `;
        tbody.appendChild(newRow);
        
        // Auto-focus the new input
        newRow.querySelector('input').focus();
    }

    // 3. Function to Delete Row
    function deleteRow(btn) {
        if(confirm("Are you sure you want to delete this category?")) {
            btn.closest('tr').remove();
        }
    }

    // 4. Toggle Color Palette
    function togglePalette(dot) {
        // Close all other open palettes first
        document.querySelectorAll('.palette-popup').forEach(p => p.classList.remove('active'));
        
        const palette = dot.nextElementSibling;
        palette.classList.toggle('active');
    }

    // 5. Select Color from Palette
    function selectColor(miniDot) {
        const selectedColor = miniDot.style.backgroundColor;
        const mainDot = miniDot.parentElement.previousElementSibling;
        
        mainDot.style.backgroundColor = selectedColor;
        miniDot.parentElement.classList.remove('active');
    }

    // Close palette when clicking outside
    window.onclick = function(event) {
        if (!event.target.matches('.color-dot')) {
            document.querySelectorAll('.palette-popup').forEach(p => p.classList.remove('active'));
        }
    }
</script>

</body>
</html>
