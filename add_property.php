<?php
session_start();
require_once 'config/db.php';

// Optional: Redirect unauthenticated users to login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_property'])) {
    $title         = trim($_POST['title'] ?? '');
    $location      = trim($_POST['location'] ?? '');
    $price         = trim($_POST['price'] ?? '');
    $type          = $_POST['type'] ?? 'PG';
    $beds          = (int)($_POST['beds_available'] ?? 1);
    $description   = trim($_POST['description'] ?? '');
    $map_embed_url = trim($_POST['map_embed_url'] ?? '');
    
    // Process checked amenities array into a clean comma-separated string
    $amenities_arr = $_POST['amenities'] ?? [];
    $amenities     = implode(', ', array_map('htmlspecialchars', $amenities_arr));

    // Image Upload Handling
    $image_path = 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=800&q=80'; // Default fallback
    
    if (isset($_FILES['property_image']) && $_FILES['property_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['property_image']['tmp_name'];
        $fileName    = $_FILES['property_image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            // Create uploads directory if it doesn't exist
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            $newFileName = uniqid('room_', true) . '.' . $fileExtension;
            $uploadFileDir = 'uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_path = $dest_path;
            }
        } else {
            $error = "Invalid file format. Only JPG, PNG, and WEBP image formats are supported.";
        }
    }

    // Insert property into database if validation passes
    if (empty($error) && !empty($title) && !empty($location) && !empty($price)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO properties (title, location, price, type, beds_available, amenities, description, image_url, map_embed_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $location, $price, $type, $beds, $amenities, $description, $image_path, $map_embed_url]);
            $message = "Property listing published successfully!";
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else if (empty($error)) {
        $error = "Please fill out all mandatory fields marked with (*).";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DormKey - Post New Accommodation</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="background: #F8FAFC; color: #1E293B; margin: 0; padding-bottom: 60px; font-family: 'Plus Jakarta Sans', sans-serif;">

  <!-- NAVBAR -->
  <header class="navbar">
    <div class="nav-container">
      <a href="index.php" class="logo">Dorm<span>Key</span></a>
      <nav class="nav-links">
        <a href="index.php" class="nav-item">HOME</a>
        <a href="add_property.php" class="nav-item active">ADD LISTING</a>
        <a href="help.php" class="nav-item">HELP</a>
        <?php if (isset($_SESSION['user_id'])): ?>
          <span class="nav-item" style="color: #059669; font-weight: 700;">
            <i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Student'); ?>
          </span>
        <?php else: ?>
          <a href="auth.php" class="btn-auth">LOGIN / SIGN UP</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- FORM CONTAINER -->
  <main class="main-container" style="max-width: 720px; margin: 40px auto; padding: 0 20px;">
    
    <div style="background: #ffffff; padding: 32px; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
      
      <div style="border-bottom: 1px solid #E2E8F0; padding-bottom: 16px; margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0 0 6px 0;">Post Property Listing</h1>
        <p style="font-size: 14px; color: #64748B; margin: 0;">Add your PG, hostel, or flat accommodation to the DormKey network.</p>
      </div>

      <!-- Success Notification -->
      <?php if (!empty($message)): ?>
        <div style="background:#D1FAE5; color:#065F46; border:1px solid #6EE7B7; padding:12px 16px; border-radius:6px; margin-bottom:20px; font-size:14px; font-weight:700; display:flex; align-items:center; gap:8px;">
          <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <!-- Error Notification -->
      <?php if (!empty($error)): ?>
        <div style="background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; padding:12px 16px; border-radius:6px; margin-bottom:20px; font-size:14px; font-weight:700; display:flex; align-items:center; gap:8px;">
          <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        
        <!-- Title -->
        <div style="margin-bottom: 18px;">
          <label style="display:block; font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 6px;">Property Name / Title *</label>
          <input type="text" name="title" placeholder="e.g. Sunrise Executive Boys PG" required style="width:100%; padding: 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; font-size: 14px; outline: none;">
        </div>

        <!-- 2 Column Row: Rent & Type -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;">
          <div>
            <label style="display:block; font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 6px;">Monthly Rent (₹) *</label>
            <input type="number" name="price" placeholder="e.g. 7500" required style="width:100%; padding: 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; font-size: 14px; outline: none;">
          </div>
          <div>
            <label style="display:block; font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 6px;">Accommodation Type</label>
            <select name="type" style="width:100%; padding: 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; font-size: 14px; outline: none; background: #fff;">
              <option value="PG">PG (Paying Guest)</option>
              <option value="Hostel">Hostel</option>
              <option value="Flat">Flat / Apartment</option>
            </select>
          </div>
        </div>

        <!-- Location & Available Beds Row -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 18px;">
          <div>
            <label style="display:block; font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 6px;">Address / Location *</label>
            <input type="text" name="location" placeholder="e.g. Siripuram Junction, Visakhapatnam" required style="width:100%; padding: 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; font-size: 14px; outline: none;">
          </div>
          <div>
            <label style="display:block; font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 6px;">Available Beds</label>
            <input type="number" name="beds_available" value="3" min="1" required style="width:100%; padding: 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; font-size: 14px; outline: none;">
          </div>
        </div>

        <!-- Google Maps Embed Link -->
        <div style="margin-bottom: 18px;">
          <label style="display:block; font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 6px;">Google Maps Embed Link (Optional)</label>
          <input type="url" name="map_embed_url" placeholder="https://www.google.com/maps/embed?pb=..." style="width:100%; padding: 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; font-size: 14px; outline: none;">
          <small style="color: #64748B; font-size: 11px; margin-top: 4px; display: block;">Copy the HTML embed `src` URL from Google Maps to display an interactive map preview on the details page.</small>
        </div>

        <!-- Amenities Selection (Matches index.php Quick Filters) -->
        <div style="margin-bottom: 18px;">
          <label style="display:block; font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 8px;">Available Amenities</label>
          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; font-size: 13px; color: #475569; background: #F8FAFC; padding: 14px; border-radius: 8px; border: 1px solid #E2E8F0;">
            <label style="cursor: pointer;"><input type="checkbox" name="amenities[]" value="Wi-Fi"> High-Speed Wi-Fi</label>
            <label style="cursor: pointer;"><input type="checkbox" name="amenities[]" value="Air Conditioned"> AC Rooms</label>
            <label style="cursor: pointer;"><input type="checkbox" name="amenities[]" value="Mess Food"> Mess / Food Included</label>
            <label style="cursor: pointer;"><input type="checkbox" name="amenities[]" value="Washing Machine"> Washing Machine</label>
            <label style="cursor: pointer;"><input type="checkbox" name="amenities[]" value="Security"> 24/7 Security CCTV</label>
            <label style="cursor: pointer;"><input type="checkbox" name="amenities[]" value="Daily Cleaning"> Daily Cleaning</label>
          </div>
        </div>

        <!-- Image Upload -->
        <div style="margin-bottom: 18px;">
          <label style="display:block; font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 6px;">Property Photo</label>
          <input type="file" name="property_image" accept="image/*" style="width:100%; padding: 8px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; font-size: 13px; background: #fff;">
        </div>

        <!-- Description -->
        <div style="margin-bottom: 24px;">
          <label style="display:block; font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 6px;">Property Description</label>
          <textarea name="description" rows="4" placeholder="Mention rules, nearby landmark distances, food details, or curfew timings..." style="width:100%; padding: 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; font-size: 14px; font-family: inherit; outline: none;"></textarea>
        </div>

        <!-- Submit Button -->
        <button type="submit" name="add_property" style="width:100%; padding: 14px; background: #059669; color: #ffffff; font-weight: 800; border: none; border-radius: 6px; cursor: pointer; font-size: 15px; transition: background 0.2s ease;">
          <i class="fa-solid fa-plus-circle"></i> Publish Property Listing
        </button>

      </form>

    </div>
  </main>

</body>
</html>