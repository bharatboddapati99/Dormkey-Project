<?php
session_start();
require_once 'config/db.php';

// Handle Search, Type, Price, & Amenity Filtering Queries
$search             = trim($_GET['search'] ?? '');
$type_filter        = $_GET['type'] ?? '';
$max_price          = $_GET['max_price'] ?? '';
$selected_amenities = $_GET['amenities'] ?? [];

$query = "SELECT * FROM properties WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($type_filter)) {
    $query .= " AND type = ?";
    $params[] = $type_filter;
}

if (!empty($max_price)) {
    $query .= " AND price <= ?";
    $params[] = $max_price;
}

// Filter by each selected amenity
if (!empty($selected_amenities) && is_array($selected_amenities)) {
    foreach ($selected_amenities as $amenity) {
        $query .= " AND amenities LIKE ?";
        $params[] = "%" . trim($amenity) . "%";
    }
}

$query .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();
} catch (PDOException $e) {
    $properties = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DormKey - Verified Student Accommodations & PGs</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="background-color: #F8FAFC; color: #1E293B; margin: 0; padding-bottom: 60px; font-family: 'Plus Jakarta Sans', sans-serif;">

  <!-- NAVBAR -->
  <header class="navbar">
    <div class="nav-container">
      <a href="index.php" class="logo">Dorm<span>Key</span></a>
      <nav class="nav-links">
        <a href="index.php" class="nav-item active">HOME</a>
        <a href="add_property.php" class="nav-item">ADD LISTING</a>
        <a href="help.php" class="nav-item">HELP</a>

        <?php if (isset($_SESSION['user_id'])): ?>
          <!-- LOGGED IN USER -->
          <span class="nav-item" style="color: #059669; font-weight: 700;">
            <i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Student'); ?>
          </span>
          <!-- LOGOUT BUTTON (Triggers Confirmation Modal) -->
          <button type="button" id="openLogoutBtn" class="nav-item" style="background: none; border: none; cursor: pointer; font: inherit; color: #EF4444; font-weight: 700;">
            <i class="fa-solid fa-right-from-bracket"></i> LOGOUT
          </button>
        <?php else: ?>
          <!-- GUEST USER -->
          <a href="auth.php" class="btn-auth">LOGIN / SIGN UP</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- HERO SECTION -->
  <section style="background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%); color: #ffffff; padding: 50px 20px; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
      <span style="background: rgba(16, 185, 129, 0.2); color: #34D399; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">
        Zero Brokerage • Verified Student PGs
      </span>
      <h1 style="font-size: 36px; font-weight: 800; margin: 18px 0 10px 0; line-height: 1.2;">
        Find Your Perfect Room Near Campus
      </h1>
      <p style="font-size: 15px; color: #94A3B8; margin-bottom: 0;">
        Explore student accommodations with verified amenities, direct manager contacts, and map views.
      </p>
    </div>
  </section>

  <!-- FILTER & QUICK AMENITY BAR -->
  <section style="background: #ffffff; border-bottom: 1px solid #E2E8F0; padding: 20px 0; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.05);">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
      
      <form method="GET" action="index.php">
        
        <!-- Search & Filter Controls -->
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 12px; align-items: center; margin-bottom: 16px;">
          
          <!-- Search input -->
          <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search area (e.g. Siripuram, MVP Colony)..." style="padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 14px; outline: none;">

          <!-- Type dropdown -->
          <select name="type" style="padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 14px; outline: none; background: #fff;">
            <option value="">All Accommodation Types</option>
            <option value="PG" <?= $type_filter === 'PG' ? 'selected' : ''; ?>>PG (Paying Guest)</option>
            <option value="Hostel" <?= $type_filter === 'Hostel' ? 'selected' : ''; ?>>Hostel</option>
            <option value="Flat" <?= $type_filter === 'Flat' ? 'selected' : ''; ?>>Flat / Apartment</option>
          </select>

          <!-- Max Price input -->
          <input type="number" name="max_price" value="<?= htmlspecialchars($max_price); ?>" placeholder="Max Rent (e.g. 8000)" style="padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 14px; outline: none;">

          <!-- Search & Reset Buttons -->
          <div style="display: flex; gap: 8px;">
            <button type="submit" style="padding: 10px 18px; background: #059669; color: #ffffff; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 13px; white-space: nowrap;">
              <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
            
            <?php if (!empty($search) || !empty($type_filter) || !empty($max_price) || !empty($selected_amenities)): ?>
              <a href="index.php" style="padding: 10px 14px; background: #F1F5F9; color: #475569; text-decoration: none; border-radius: 6px; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center;">
                Reset
              </a>
            <?php endif; ?>
          </div>

        </div>

        <!-- Quick Amenity Filters -->
        <div>
          <span style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 8px;">
            <i class="fa-solid fa-sliders" style="color: #059669;"></i> Quick Amenity Filters:
          </span>
          
          <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <?php
              $amenity_options = [
                'Wi-Fi' => 'wifi',
                'Air Conditioned' => 'snowflake',
                'Mess Food' => 'utensils',
                'Washing Machine' => 'shirt',
                'Security' => 'shield-halved',
                'Daily Cleaning' => 'broom'
              ];

              foreach ($amenity_options as $label => $icon):
                $isChecked = in_array($label, $selected_amenities);
            ?>
              <label style="cursor: pointer; user-select: none;">
                <input type="checkbox" name="amenities[]" value="<?= $label; ?>" <?= $isChecked ? 'checked' : ''; ?> onchange="this.form.submit()" style="display: none;">
                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; border: 1px solid <?= $isChecked ? '#059669' : '#CBD5E1'; ?>; background: <?= $isChecked ? '#ECFDF5' : '#F8FAFC'; ?>; color: <?= $isChecked ? '#047857' : '#475569'; ?>; transition: all 0.2s ease;">
                  <i class="fa-solid fa-<?= $icon; ?>"></i> <?= $label; ?>
                  <?php if ($isChecked): ?>
                    <i class="fa-solid fa-circle-check" style="font-size: 10px; color: #059669;"></i>
                  <?php endif; ?>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

      </form>

    </div>
  </section>

  <!-- MAIN LISTINGS CONTAINER -->
  <main class="main-container" style="max-width: 1200px; margin: 36px auto; padding: 0 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
      <div>
        <h2 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0;">Featured Accommodations</h2>
        <p style="font-size: 14px; color: #64748B; margin: 4px 0 0 0;">
          Showing <?= count($properties); ?> verified listings
        </p>
      </div>
      <a href="add_property.php" style="text-decoration: none;">
        <button style="padding: 10px 18px; background: #059669; color: #ffffff; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 13px;">
          <i class="fa-solid fa-plus-circle"></i> Post Property
        </button>
      </a>
    </div>

    <!-- PROPERTY GRID -->
    <?php if (!empty($properties)): ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
        <?php foreach ($properties as $room): ?>
          <div style="background: #ffffff; border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: transform 0.2s ease;">
            
            <!-- Image Container -->
            <div style="height: 200px; overflow: hidden; position: relative; background: #CBD5E1;">
              <?php $image_src = !empty($room['image_url']) ? $room['image_url'] : 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=800&q=80'; ?>
              <img src="<?= htmlspecialchars($image_src); ?>" alt="<?= htmlspecialchars($room['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
              <span style="position: absolute; top: 12px; left: 12px; background: rgba(15, 23, 42, 0.85); color: #ffffff; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; backdrop-filter: blur(4px);">
                <?= htmlspecialchars($room['type'] ?? 'PG'); ?>
              </span>
            </div>

            <!-- Content Area -->
            <div style="padding: 20px;">
              <h3 style="font-size: 18px; font-weight: 800; color: #1E293B; margin: 0 0 6px 0; line-height: 1.3;">
                <?= htmlspecialchars($room['title']); ?>
              </h3>
              
              <p style="font-size: 13px; color: #64748B; margin: 0 0 14px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <i class="fa-solid fa-location-dot" style="color: #EF4444; margin-right: 4px;"></i> 
                <?= htmlspecialchars($room['location']); ?>
              </p>

              <!-- Amenities Preview Tags -->
              <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px;">
                <?php 
                  $amenitiesList = array_filter(explode(',', $room['amenities'] ?? ''));
                  $previewCount = 0;
                  foreach ($amenitiesList as $amenity):
                    if ($previewCount >= 2) break;
                ?>
                  <span style="font-size: 11px; background: #F1F5F9; color: #475569; padding: 3px 8px; border-radius: 4px; font-weight: 600;">
                    <i class="fa-solid fa-check" style="color: #059669; font-size: 10px;"></i> <?= htmlspecialchars(trim($amenity)); ?>
                  </span>
                <?php 
                    $previewCount++;
                  endforeach; 
                ?>
              </div>

              <!-- Price & CTA Button -->
              <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #F1F5F9; padding-top: 14px;">
                <div>
                  <span style="font-size: 22px; font-weight: 800; color: #059669;">₹<?= number_format($room['price']); ?></span>
                  <span style="font-size: 12px; color: #64748B; font-weight: 500;">/mo</span>
                </div>
                <a href="property_details.php?id=<?= $room['id']; ?>" style="padding: 9px 16px; background: #1E293B; color: #ffffff; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 700;">
                  View Details
                </a>
              </div>

            </div>

          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <!-- Empty State -->
      <div style="background: #ffffff; border: 1px dashed #CBD5E1; border-radius: 12px; padding: 60px 20px; text-align: center;">
        <i class="fa-solid fa-house-circle-exclamation" style="font-size: 48px; color: #94A3B8; margin-bottom: 16px;"></i>
        <h3 style="font-size: 18px; font-weight: 800; color: #1E293B; margin-bottom: 6px;">No Properties Match Your Search</h3>
        <p style="font-size: 14px; color: #64748B; margin-bottom: 20px;">Try clearing your filters or add a new property listing.</p>
        <a href="index.php" style="padding: 10px 20px; background: #1E293B; color: #ffffff; text-decoration: none; font-weight: 700; border-radius: 6px; font-size: 14px; margin-right: 8px;">
          Reset Filters
        </a>
        <a href="add_property.php" style="padding: 10px 20px; background: #059669; color: #ffffff; text-decoration: none; font-weight: 700; border-radius: 6px; font-size: 14px;">
          Add Property Listing
        </a>
      </div>
    <?php endif; ?>

  </main>

  <!-- LOGOUT CONFIRMATION MODAL -->
  <div class="modal-overlay hidden" id="logoutModal" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 9999;">
    <div class="modal-card" style="max-width: 380px; width: 90%; text-align: center; padding: 24px; border-radius: 12px; background: #ffffff; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
      
      <div style="font-size: 44px; color: #EF4444; margin-bottom: 12px;">
        <i class="fa-solid fa-circle-question"></i>
      </div>

      <h3 style="font-size: 20px; font-weight: 800; color: #1E293B; margin-bottom: 8px;">Logout Confirmation</h3>
      <p style="font-size: 14px; color: #64748B; margin-bottom: 24px;">Are you sure you want to log out of your DormKey account?</p>

      <div style="display: flex; gap: 12px; justify-content: center;">
        <!-- Cancel Button -->
        <button type="button" id="cancelLogoutBtn" style="flex: 1; padding: 10px 16px; border: 1px solid #CBD5E1; background: #F8FAFC; color: #334155; font-weight: 700; border-radius: 6px; cursor: pointer;">
          Cancel
        </button>

        <!-- Confirm Logout Button -->
        <a href="logout.php" style="flex: 1; text-decoration: none;">
          <button type="button" style="width: 100%; padding: 10px 16px; border: none; background: #EF4444; color: #FFFFFF; font-weight: 700; border-radius: 6px; cursor: pointer;">
            Yes, Logout
          </button>
        </a>
      </div>

    </div>
  </div>

  <!-- JAVASCRIPT FOR MODAL TOGGLE -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const logoutModal = document.getElementById("logoutModal");
      const openLogoutBtn = document.getElementById("openLogoutBtn");
      const cancelLogoutBtn = document.getElementById("cancelLogoutBtn");

      if (openLogoutBtn && logoutModal) {
        openLogoutBtn.addEventListener("click", () => {
          logoutModal.classList.remove("hidden");
        });
      }

      if (cancelLogoutBtn && logoutModal) {
        cancelLogoutBtn.addEventListener("click", () => {
          logoutModal.classList.add("hidden");
        });
      }

      if (logoutModal) {
        logoutModal.addEventListener("click", (e) => {
          if (e.target === logoutModal) {
            logoutModal.classList.add("hidden");
          }
        });
      }
    });
  </script>

</body>
</html>