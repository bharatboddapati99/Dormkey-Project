<?php
session_start();
require_once 'config/db.php';

$property_id = $_GET['id'] ?? null;

if (!$property_id) {
    header("Location: index.php");
    exit();
}

// 1. Fetch property details
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$property_id]);
$property = $stmt->fetch();

if (!$property) {
    die("Property listing not found.");
}

// Fallback Image
$image_url = !empty($property['image_url']) ? $property['image_url'] : 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=800&q=80';

// Regional or Specific Google Map Embed Link
$map_embed_url = $property['map_embed_url'] ?? '';
if (empty($map_embed_url)) {
    $search_location = rawurlencode($property['location'] . ', Visakhapatnam');
    $map_embed_url = "https://maps.google.com/maps?q={$search_location}&t=&z=14&ie=UTF8&iwloc=&output=embed";
}

// 2. Handle Slot Booking Submission
$booking_msg = '';
$booking_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_slot'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: auth.php");
        exit();
    }
    
    $beds_available = (int)($property['beds_available'] ?? 1);
    if ($beds_available > 0) {
        try {
            $updateStmt = $pdo->prepare("UPDATE properties SET beds_available = beds_available - 1 WHERE id = ?");
            $updateStmt->execute([$property_id]);
            
            // Refresh property data
            $stmt->execute([$property_id]);
            $property = $stmt->fetch();
            
            $booking_msg = "Slot booked successfully! The property manager will contact you shortly.";
        } catch (PDOException $e) {
            $booking_error = "Booking failed. Please try again.";
        }
    } else {
        $booking_error = "Sorry, no beds are available for this property.";
    }
}

// 3. Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: auth.php");
        exit();
    }
    
    $rating = (int)($_POST['rating'] ?? 5);
    $comment = trim($_POST['comment'] ?? '');
    $user_name = $_SESSION['user_name'] ?? $_SESSION['name'] ?? 'Student';
    
    if (!empty($comment)) {
        try {
            $revStmt = $pdo->prepare("INSERT INTO reviews (property_id, user_id, user_name, rating, comment) VALUES (?, ?, ?, ?, ?)");
            $revStmt->execute([$property_id, $_SESSION['user_id'], $user_name, $rating, $comment]);
        } catch (PDOException $e) {
            // Silently handle or log error
        }
    }
}

// 4. Fetch Reviews & Average Rating
$reviewsStmt = $pdo->prepare("SELECT * FROM reviews WHERE property_id = ? ORDER BY created_at DESC");
$reviewsStmt->execute([$property_id]);
$reviews = $reviewsStmt->fetchAll();

$avgStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE property_id = ?");
$avgStmt->execute([$property_id]);
$ratingData = $avgStmt->fetch();
$avgRating = round($ratingData['avg_rating'] ?? 0, 1);
$reviewCount = $ratingData['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($property['title']); ?> - DormKey</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="background: #F8FAFC; padding-bottom: 60px; font-family: 'Plus Jakarta Sans', sans-serif;">

  <!-- NAVBAR -->
  <header class="navbar">
    <div class="nav-container">
      <a href="index.php" class="logo">Dorm<span>Key</span></a>
      <nav class="nav-links">
        <a href="index.php" class="nav-item">HOME</a>
        <a href="add_property.php" class="nav-item">ADD LISTING</a>
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

  <main class="main-container" style="max-width: 1050px; margin: 30px auto; padding: 0 20px;">
    
    <!-- Title Section -->
    <div style="margin-bottom: 20px;">
      <span style="background: #E0E7FF; color: #4338CA; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 20px;">
        <?= htmlspecialchars($property['type'] ?? 'PG'); ?>
      </span>
      <h1 style="font-size: 28px; font-weight: 800; color: #1E293B; margin: 8px 0 4px 0;">
        <?= htmlspecialchars($property['title']); ?>
      </h1>
      <p style="color: #64748B; font-size: 14px;">
        <i class="fa-solid fa-location-dot" style="color: #EF4444;"></i> <?= htmlspecialchars($property['location']); ?>
      </p>
    </div>

    <!-- Booking Alerts -->
    <?php if (!empty($booking_msg)): ?>
      <div style="background:#D1FAE5; color:#065F46; border:1px solid #6EE7B7; padding:14px; border-radius:8px; margin-bottom:20px; font-weight:700;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($booking_msg); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($booking_error)): ?>
      <div style="background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; padding:14px; border-radius:8px; margin-bottom:20px; font-weight:700;">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($booking_error); ?>
      </div>
    <?php endif; ?>

    <!-- Main Grid: Image & Booking Card -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 30px;">
      
      <div style="border-radius: 12px; overflow: hidden; height: 380px; background: #CBD5E1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <img src="<?= htmlspecialchars($image_url); ?>" alt="<?= htmlspecialchars($property['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
      </div>

      <div style="background: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); height: fit-content;">
        <div style="margin-bottom: 16px;">
          <span style="font-size: 13px; color: #64748B;">Monthly Rent</span>
          <h2 style="font-size: 32px; font-weight: 800; color: #059669; margin: 0;">₹<?= number_format($property['price']); ?><span style="font-size: 14px; font-weight: 500; color: #64748B;">/mo</span></h2>
        </div>

        <div style="background: #ECFDF5; border: 1px solid #A7F3D0; padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center;">
          <span style="font-size: 13px; color: #047857; font-weight: 800;">
            <i class="fa-solid fa-bed"></i> <?= (int)($property['beds_available'] ?? 1); ?> Beds Available
          </span>
        </div>

        <form method="POST" style="margin-bottom: 16px;">
          <?php if ((int)($property['beds_available'] ?? 0) > 0): ?>
            <button type="submit" name="book_slot" style="width: 100%; padding: 12px; background: #059669; color: #ffffff; font-weight: 800; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; margin-bottom: 10px;">
              <i class="fa-solid fa-calendar-check"></i> Book Slot Now
            </button>
          <?php else: ?>
            <button type="button" disabled style="width: 100%; padding: 12px; background: #94A3B8; color: #ffffff; font-weight: 800; border: none; border-radius: 6px; cursor: not-allowed; font-size: 14px; margin-bottom: 10px;">
              Fully Booked
            </button>
          <?php endif; ?>
        </form>

        <div style="display: flex; flex-direction: column; gap: 8px;">
          <a href="tel:919876543210" style="text-decoration: none;">
            <button style="width: 100%; padding: 10px; background: #1E293B; color: #ffffff; font-weight: 700; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">
              <i class="fa-solid fa-phone"></i> Call Property Manager
            </button>
          </a>

          <a href="https://wa.me/918688095188?text=Hi,%20I%20am%20interested%20in%20booking%20a%20slot%20at%20<?= urlencode($property['title']); ?>" target="_blank" style="text-decoration: none;">
            <button style="width: 100%; padding: 10px; background: #25D366; color: #ffffff; font-weight: 700; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">
              <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
            </button>
          </a>
        </div>
      </div>

    </div>

    <!-- Amenities & Maps Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
      
      <div style="background: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #E2E8F0;">
        <h3 style="font-size: 18px; font-weight: 800; color: #1E293B; margin-top: 0; margin-bottom: 16px;">Amenities Included</h3>
        
        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px;">
          <?php 
            $amenities = array_filter(explode(',', $property['amenities'] ?? 'Wi-Fi, Security, Cleaning'));
            foreach ($amenities as $item):
          ?>
            <span style="background: #F1F5F9; color: #334155; font-size: 13px; font-weight: 600; padding: 6px 12px; border-radius: 20px;">
              <i class="fa-solid fa-check" style="color: #059669; margin-right: 4px;"></i> <?= htmlspecialchars(trim($item)); ?>
            </span>
          <?php endforeach; ?>
        </div>

        <h3 style="font-size: 18px; font-weight: 800; color: #1E293B; margin-bottom: 10px;">About Accommodations</h3>
        <p style="font-size: 14px; color: #475569; line-height: 1.6;">
          <?= nl2br(htmlspecialchars($property['description'] ?? 'Verified student PG offering clean accommodation, daily meals, water supply, and easy connectivity to Andhra University campuses.')); ?>
        </p>
      </div>

      <div style="background: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #E2E8F0;">
        <h3 style="font-size: 18px; font-weight: 800; color: #1E293B; margin-top: 0; margin-bottom: 16px;">
          <i class="fa-solid fa-map-location-dot" style="color: #2563EB;"></i> Location & Surroundings
        </h3>
        
        <div style="border-radius: 8px; overflow: hidden; height: 260px; border: 1px solid #CBD5E1; margin-bottom: 12px;">
          <iframe src="<?= htmlspecialchars($map_embed_url); ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>

        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($property['location'] . ' Visakhapatnam'); ?>" target="_blank" style="color: #2563EB; font-weight: 700; text-decoration: none; font-size: 13px; display: inline-block;">
          Open Location in Google Maps App <i class="fa-solid fa-arrow-up-right-from-square"></i>
        </a>
      </div>

    </div>

    <!-- REVIEWS & RATINGS SECTION -->
    <div style="background: #ffffff; padding: 24px; border-radius: 12px; border: 1px solid #E2E8F0;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="font-size: 20px; font-weight: 800; color: #1E293B; margin: 0;">
          <i class="fa-solid fa-star" style="color: #F59E0B;"></i> Student Reviews & Ratings
        </h3>
        <div style="text-align: right;">
          <span style="font-size: 24px; font-weight: 800; color: #1E293B;"><?= $avgRating; ?></span>
          <span style="font-size: 14px; color: #64748B;">/ 5.0 (<?= $reviewCount; ?> reviews)</span>
        </div>
      </div>

      <!-- Review Form -->
      <?php if (isset($_SESSION['user_id'])): ?>
        <form method="POST" style="background: #F8FAFC; padding: 16px; border-radius: 8px; margin-bottom: 24px; border: 1px solid #E2E8F0;">
          <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #1E293B;">Leave a Review</h4>
          <div style="display: flex; gap: 12px; margin-bottom: 10px; align-items: center;">
            <label style="font-size: 13px; font-weight: 600;">Rating:</label>
            <select name="rating" style="padding: 6px 10px; border-radius: 4px; border: 1px solid #CBD5E1; font-size: 13px;">
              <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
              <option value="4">⭐⭐⭐⭐ (4/5)</option>
              <option value="3">⭐⭐⭐ (3/5)</option>
              <option value="2">⭐⭐ (2/5)</option>
              <option value="1">⭐ (1/5)</option>
            </select>
          </div>
          <textarea name="comment" rows="3" placeholder="Share your experience regarding mess food, Wi-Fi speed, or cleanliness..." required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; margin-bottom: 10px; font-family: inherit; font-size: 13px;"></textarea>
          <button type="submit" name="submit_review" style="padding: 8px 16px; background: #059669; color: #ffffff; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 13px;">
            Submit Review
          </button>
        </form>
      <?php else: ?>
        <p style="font-size: 13px; color: #64748B; background: #F1F5F9; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
          Please <a href="auth.php" style="color: #2563EB; font-weight: 700;">login</a> to leave a student review.
        </p>
      <?php endif; ?>

      <!-- Reviews Feed -->
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <?php if (!empty($reviews)): ?>
          <?php foreach ($reviews as $rev): ?>
            <div style="border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                <strong style="font-size: 14px; color: #1E293B;"><?= htmlspecialchars($rev['user_name']); ?></strong>
                <span style="color: #F59E0B; font-size: 12px;"><?= str_repeat('★', $rev['rating']); ?><?= str_repeat('☆', 5 - $rev['rating']); ?></span>
              </div>
              <p style="font-size: 13px; color: #475569; margin: 0 0 4px 0;"><?= htmlspecialchars($rev['comment']); ?></p>
              <small style="font-size: 11px; color: #94A3B8;"><?= date('M d, Y', strtotime($rev['created_at'])); ?></small>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="font-size: 13px; color: #94A3B8; text-align: center; margin: 20px 0;">No reviews yet. Be the first student to review this property!</p>
        <?php endif; ?>
      </div>
    </div>

  </main>

</body>
</html>