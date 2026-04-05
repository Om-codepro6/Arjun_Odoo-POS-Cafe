<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>POS Settings</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="pos-settings">

  <!-- Header -->
  <div class="header">
    <a href="index.php" class="back-btn">⬅️ Back</a>
    <span class="title">Point of Sale</span>
    <span class="cafe">Odoo Cafe</span>
    <span class="new-btn" onclick="openPopup()">+ New</span>
  </div>

  <!-- Payment Method Section -->
  <div class="section">
    <h3>💳 Payment Method</h3>

    <div class="payment-grid">

      <!-- Left -->
      <div class="left">
        <label><input type="checkbox"> 💵 Cash</label>

        <label><input type="checkbox"> 📱 QR Payment (UPI)</label>

        <input type="text" placeholder="UPI ID (e.g: 123@ybl.com)">
      </div>

      <!-- Right -->
      <div class="right">
        <label><input type="checkbox"> 💳 Digital (Bank, Card)</label>
      </div>

    </div>
  </div>

</div>

<!-- Popup -->
<div class="popup" id="popup">
  <div class="popup-content">
    <h3>🆕 Create POS</h3>
    <input type="text" placeholder="Enter POS Name">

    <div class="popup-buttons">
      <button class="save">✅ Save</button>
      <button onclick="closePopup()">❌ Discard</button>
    </div>
  </div>
</div>

<script>
function openPopup() {
  const popup = document.getElementById("popup");
  popup.style.display = "flex";
  setTimeout(() => popup.style.opacity = "1", 10);
}

function closePopup() {
  const popup = document.getElementById("popup");
  popup.style.opacity = "0";
  setTimeout(() => popup.style.display = "none", 300);
}
</script>

</body>
</html>