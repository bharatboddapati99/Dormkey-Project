// ==========================================
// 1. DATABASE (Sample PG Listings)
// ==========================================
const pgData = [
  {
    id: 1,
    name: "Blue Nest PG",
    city: "Visakhapatnam",
    college: "Andhra University",
    gender: "Male",
    featured: true,
    rating: 4.7,
    reviewsCount: 128,
    distance: "650m from AU",
    amenities: ["WiFi", "Food", "AC", "Laundry", "Attached Bathroom", "Power Backup"],
    price: 6500,
    availability: "Few Rooms Left",
    availabilityClass: "few-left",
    image: "https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?w=500",
    description: "Modern PG located within walking distance from Andhra University main gate."
  },
  {
    id: 2,
    name: "Sunrise PG",
    city: "Visakhapatnam",
    college: "Andhra University",
    gender: "Female",
    featured: false,
    rating: 4.5,
    reviewsCount: 86,
    distance: "650m from AU",
    amenities: ["WiFi", "Food", "Laundry", "CCTV", "Attached Bathroom"],
    price: 5800,
    availability: "Available",
    availabilityClass: "available",
    image: "https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=500",
    description: "A secure and cozy women's PG with 24/7 security and attached bathrooms."
  },
  {
    id: 3,
    name: "Campus Corner",
    city: "Visakhapatnam",
    college: "GITAM",
    gender: "Male",
    featured: false,
    rating: 4.6,
    reviewsCount: 92,
    distance: "700m from GITAM",
    amenities: ["WiFi", "Food", "AC", "Power Backup", "CCTV"],
    price: 7000,
    availability: "Available",
    availabilityClass: "available",
    image: "https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=500",
    description: "Premium student living with air conditioning and full power backup."
  },
  {
    id: 4,
    name: "Student Hub",
    city: "Visakhapatnam",
    college: "Vignan",
    gender: "Any",
    featured: false,
    rating: 4.3,
    reviewsCount: 75,
    distance: "1.2 km from Vignan",
    amenities: ["WiFi", "Food", "CCTV", "Laundry"],
    price: 4900,
    availability: "Few Rooms Left",
    availabilityClass: "few-left",
    image: "https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=500",
    description: "Budget-friendly option for students with quiet study areas and reliable Wi-Fi."
  },
  {
    id: 5,
    name: "Green House PG",
    city: "Hyderabad",
    college: "All",
    gender: "Female",
    featured: false,
    rating: 4.6,
    reviewsCount: 110,
    distance: "500m from College",
    amenities: ["WiFi", "Food", "AC", "Attached Bathroom", "Power Backup"],
    price: 6200,
    availability: "Available",
    availabilityClass: "available",
    image: "https://images.unsplash.com/photo-1598928506311-c55ded91a20c?w=500",
    description: "Eco-friendly, well-ventilated environment with single and double sharing options."
  },
  {
    id: 6,
    name: "Comfort Living",
    city: "Bengaluru",
    college: "All",
    gender: "Male",
    featured: false,
    rating: 4.4,
    reviewsCount: 68,
    distance: "900m from City Center",
    amenities: ["WiFi", "Laundry", "AC", "CCTV", "Attached Bathroom"],
    price: 11800,
    availability: "Available",
    availabilityClass: "available",
    image: "https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=500",
    description: "Fully furnished executive PG equipped with smart security systems."
  }
];

// ==========================================
// 2. APP STATE
// ==========================================
let wishlist = [];
let activeAmenities = [];
let selectedRating = 5; // Default rating selection in review form

// Dynamic Reviews Array for the Slider
const reviewsList = [
  {
    author: "Bharath",
    course: "B.Tech CSE Student",
    text: "Found a super clean PG just 500m from AU within 10 minutes. No brokerage and the room photos were 100% real!",
    rating: 5
  },
  {
    author: "Priya Sharma",
    course: "M.Sc Student, Andhra University",
    text: "Very helpful platform for outstation students! Filtered by female PG with food included and booked on day one.",
    rating: 5
  },
  {
    author: "Rahul Varma",
    course: "B.Tech Mechanical",
    text: "Great experience! The price ranges are accurate and no hidden broker charges.",
    rating: 4
  }
];

let currentReviewIndex = 0;

// ==========================================
// 3. INITIALIZATION & EVENT LISTENERS
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
  // DOM Elements - Search & Filters
  const searchForm = document.getElementById("searchForm");
  const citySelect = document.getElementById("citySelect");
  const collegeSelect = document.getElementById("collegeSelect");
  const genderSelect = document.getElementById("genderSelect");
  const budgetRange = document.getElementById("budgetRange");
  const budgetVal = document.getElementById("budget-val");
  const resetFiltersBtn = document.getElementById("resetFiltersBtn");
  const clearFiltersBtn = document.getElementById("clearFiltersBtn");

  // DOM Elements - Modals
  const detailsModal = document.getElementById("detailsModal");
  const closeModalBtn = document.getElementById("closeModalBtn");
  const wishlistNavBtn = document.getElementById("wishlistNavBtn");
  const wishlistModal = document.getElementById("wishlistModal");
  const closeWishlistBtn = document.getElementById("closeWishlistBtn");

  // DOM Elements - Review System
  const openReviewModalBtn = document.getElementById("openReviewModalBtn");
  const closeReviewModalBtn = document.getElementById("closeReviewModalBtn");
  const reviewModal = document.getElementById("reviewModal");
  const reviewForm = document.getElementById("reviewForm");
  const stars = document.querySelectorAll("#starRatingSelect i");
  const prevReviewBtn = document.getElementById("prevReviewBtn");
  const nextReviewBtn = document.getElementById("nextReviewBtn");

  // Initial Render of Listings & First Review
  if (document.getElementById("pgGrid")) {
    filterPGs();
  }
  displayReview(currentReviewIndex);

  // ---- FILTER EVENTS ----
  if (budgetRange && budgetVal) {
    budgetRange.addEventListener("input", (e) => {
      budgetVal.innerText = `₹${parseInt(e.target.value).toLocaleString('en-IN')}`;
      filterPGs();
    });
  }

  if (searchForm) {
    searchForm.addEventListener("submit", (e) => {
      e.preventDefault();
      filterPGs();
    });
  }

  if (citySelect) citySelect.addEventListener("change", filterPGs);
  if (collegeSelect) collegeSelect.addEventListener("change", filterPGs);
  if (genderSelect) genderSelect.addEventListener("change", filterPGs);

  // Amenity Quick-Filter Pills
  document.querySelectorAll("#amenityPills .pill[data-amenity]").forEach(pill => {
    pill.addEventListener("click", (e) => {
      e.preventDefault();
      const amenity = pill.getAttribute("data-amenity").trim();
      
      if (activeAmenities.includes(amenity)) {
        activeAmenities = activeAmenities.filter(a => a !== amenity);
        pill.classList.remove("active");
      } else {
        activeAmenities.push(amenity);
        pill.classList.add("active");
      }
      filterPGs();
    });
  });

  if (resetFiltersBtn) resetFiltersBtn.addEventListener("click", resetAllFilters);
  if (clearFiltersBtn) clearFiltersBtn.addEventListener("click", resetAllFilters);

  // ---- DETAILS MODAL EVENTS ----
  if (closeModalBtn) closeModalBtn.addEventListener("click", closeModal);
  if (detailsModal) {
    detailsModal.addEventListener("click", (e) => {
      if (e.target === detailsModal) closeModal();
    });
  }

  // ---- WISHLIST MODAL EVENTS ----
  if (wishlistNavBtn) wishlistNavBtn.addEventListener("click", openWishlistModal);
  if (closeWishlistBtn) closeWishlistBtn.addEventListener("click", () => wishlistModal.classList.add("hidden"));
  if (wishlistModal) {
    wishlistModal.addEventListener("click", (e) => {
      if (e.target === wishlistModal) wishlistModal.classList.add("hidden");
    });
  }

  // ---- REVIEW SLIDER NAVIGATION ----
  if (prevReviewBtn) {
    prevReviewBtn.addEventListener("click", () => {
      currentReviewIndex = (currentReviewIndex - 1 + reviewsList.length) % reviewsList.length;
      displayReview(currentReviewIndex);
    });
  }

  if (nextReviewBtn) {
    nextReviewBtn.addEventListener("click", () => {
      currentReviewIndex = (currentReviewIndex + 1) % reviewsList.length;
      displayReview(currentReviewIndex);
    });
  }

  // ---- REVIEW MODAL EVENTS ----
  if (openReviewModalBtn && reviewModal) {
    openReviewModalBtn.addEventListener("click", (e) => {
      e.preventDefault();
      reviewModal.classList.remove("hidden");
      document.body.style.overflow = "hidden";
    });
  }

  if (closeReviewModalBtn && reviewModal) {
    closeReviewModalBtn.addEventListener("click", () => {
      reviewModal.classList.add("hidden");
      document.body.style.overflow = "auto";
    });
  }

  // Interactive Star Rating Picker inside Modal
  if (stars) {
    stars.forEach(star => {
      star.addEventListener("click", () => {
        selectedRating = parseInt(star.getAttribute("data-rating"));
        stars.forEach((s, idx) => {
          if (idx < selectedRating) {
            s.classList.add("active");
            s.classList.replace("fa-regular", "fa-solid");
          } else {
            s.classList.remove("active");
            s.classList.replace("fa-solid", "fa-regular");
          }
        });
      });
    });
  }

  // Submit Review Form Logic
  if (reviewForm) {
    reviewForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const nameVal = document.getElementById("reviewerName").value.trim();
      const courseVal = document.getElementById("reviewerCourse").value.trim();
      const textVal = document.getElementById("reviewerText").value.trim();

      // Add new review to top of reviewsList array
      const newReview = {
        author: nameVal,
        course: courseVal,
        text: textVal,
        rating: selectedRating
      };

      reviewsList.unshift(newReview);
      currentReviewIndex = 0; // Display the new review immediately
      displayReview(currentReviewIndex);

      // Close modal & reset form state
      reviewModal.classList.add("hidden");
      document.body.style.overflow = "auto";
      reviewForm.reset();

      stars.forEach(s => {
        s.classList.add("active");
        s.classList.replace("fa-regular", "fa-solid");
      });
      selectedRating = 5;

      alert("Thank you! Your review has been added to the slider.");
    });
  }
});

// ==========================================
// 4. REVIEW SLIDER RENDER FUNCTION
// ==========================================
function displayReview(index) {
  const rev = reviewsList[index];
  if (!rev) return;

  const textElem = document.getElementById("reviewText");
  const authorElem = document.getElementById("reviewAuthor");
  const courseElem = document.getElementById("reviewCourse");
  const starsElem = document.getElementById("reviewStars");

  if (textElem) textElem.textContent = `"${rev.text}"`;
  if (authorElem) authorElem.textContent = rev.author;
  if (courseElem) courseElem.textContent = rev.course;

  if (starsElem) {
    let starsHtml = "";
    for (let i = 0; i < 5; i++) {
      if (i < rev.rating) {
        starsHtml += `<i class="fa-solid fa-star" style="color: var(--accent-gold);"></i> `;
      } else {
        starsHtml += `<i class="fa-regular fa-star" style="color: #D1D5DB;"></i> `;
      }
    }
    starsElem.innerHTML = starsHtml;
  }
}

// ==========================================
// 5. FILTERING & RENDERING LOGIC
// ==========================================
function filterPGs() {
  const pgGrid = document.getElementById("pgGrid");
  if (!pgGrid) return;

  const citySelect = document.getElementById("citySelect");
  const collegeSelect = document.getElementById("collegeSelect");
  const genderSelect = document.getElementById("genderSelect");
  const budgetRange = document.getElementById("budgetRange");

  const selectedCity = citySelect ? citySelect.value : "All";
  const selectedCollege = collegeSelect ? collegeSelect.value : "All";
  const selectedGender = genderSelect ? genderSelect.value : "Any";
  const maxPrice = budgetRange ? parseInt(budgetRange.value) : 15000;

  const filtered = pgData.filter(pg => {
    const matchesCity = (selectedCity === "All" || pg.city === selectedCity);
    const matchesCollege = (selectedCollege === "All" || pg.college === selectedCollege || pg.college === "All");
    const matchesGender = (selectedGender === "Any" || pg.gender === "Any" || pg.gender === selectedGender);
    const matchesBudget = pg.price <= maxPrice;
    
    const matchesAmenities = activeAmenities.every(selectedAmenity => 
      pg.amenities.some(pgAmenity => pgAmenity.toLowerCase() === selectedAmenity.toLowerCase())
    );

    return matchesCity && matchesCollege && matchesGender && matchesBudget && matchesAmenities;
  });

  renderPGs(filtered);
}

function renderPGs(items) {
  const pgGrid = document.getElementById("pgGrid");
  const noResults = document.getElementById("noResults");
  const listingsSubtitle = document.getElementById("listingsSubtitle");

  if (!pgGrid) return;
  pgGrid.innerHTML = "";

  if (items.length === 0) {
    pgGrid.style.display = "none";
    if (noResults) noResults.classList.remove("hidden");
    if (listingsSubtitle) listingsSubtitle.innerText = "0 PGs found";
    return;
  }

  pgGrid.style.display = "grid";
  if (noResults) noResults.classList.add("hidden");
  if (listingsSubtitle) listingsSubtitle.innerText = `Showing ${items.length} verified PG${items.length > 1 ? 's' : ''}`;

  items.forEach(item => {
    const isLiked = wishlist.includes(item.id);
    const card = document.createElement("div");
    card.className = "pg-card";
    card.innerHTML = `
      <div class="card-img-wrapper">
        <img src="${item.image}" alt="${item.name}">
        ${item.featured ? '<span class="badge-featured">FEATURED</span>' : ''}
        <button type="button" class="btn-wishlist" onclick="toggleWishlist(${item.id})">
          <i class="${isLiked ? 'fa-solid' : 'fa-regular'} fa-heart" style="${isLiked ? 'color:#EF4444;' : ''}"></i>
        </button>
      </div>
      <div class="card-content">
        <div class="card-header">
          <h3>${item.name}</h3>
          <span class="rating-badge"><i class="fa-solid fa-star"></i> ${item.rating}</span>
        </div>
        <p class="distance"><i class="fa-solid fa-location-dot"></i> ${item.distance}</p>
        <div class="amenities">
          ${item.amenities.slice(0, 3).map(a => `<span><i class="fa-solid fa-check"></i> ${a}</span>`).join('')}
        </div>
        <div class="price-row">
          <span class="price">₹${item.price.toLocaleString('en-IN')}</span>
          <span class="period">/month</span>
        </div>
        <div class="availability ${item.availabilityClass}">${item.availability}</div>
        <button type="button" class="btn-details" onclick="openModal(${item.id})">View Details</button>
      </div>
    `;
    pgGrid.appendChild(card);
  });
}

function resetAllFilters() {
  const citySelect = document.getElementById("citySelect");
  const collegeSelect = document.getElementById("collegeSelect");
  const genderSelect = document.getElementById("genderSelect");
  const budgetRange = document.getElementById("budgetRange");
  const budgetVal = document.getElementById("budget-val");

  if (citySelect) citySelect.value = "All";
  if (collegeSelect) collegeSelect.value = "All";
  if (genderSelect) genderSelect.value = "Any";
  if (budgetRange) budgetRange.value = 15000;
  if (budgetVal) budgetVal.innerText = "₹15,000";
  
  activeAmenities = [];
  document.querySelectorAll("#amenityPills .pill").forEach(p => p.classList.remove("active"));
  
  filterPGs();
}

// ==========================================
// 6. MODAL & WISHLIST GLOBAL HANDLERS
// ==========================================
window.openModal = function(id) {
  const detailsModal = document.getElementById("detailsModal");
  const modalContent = document.getElementById("modalContent");
  
  const pg = pgData.find(item => item.id === id);
  if (!pg || !detailsModal || !modalContent) return;

  modalContent.innerHTML = `
    <img src="${pg.image}" class="modal-banner-img" alt="${pg.name}">
    <div class="modal-body">
      <div class="modal-header-row">
        <h2>${pg.name}</h2>
        <span class="rating-badge"><i class="fa-solid fa-star"></i> ${pg.rating} (${pg.reviewsCount} reviews)</span>
      </div>
      <div class="modal-meta">
        <span><i class="fa-solid fa-location-dot"></i> ${pg.distance}</span>
        <span><i class="fa-solid fa-user-group"></i> ${pg.gender} PG</span>
        <span><i class="fa-solid fa-building"></i> ${pg.city}</span>
      </div>
      <div class="modal-section">
        <h4>About Property</h4>
        <p style="font-size:13px; color:var(--text-muted);">${pg.description}</p>
      </div>
      <div class="modal-section">
        <h4>Available Amenities</h4>
        <div class="modal-amenities-tags">
          ${pg.amenities.map(a => `<span class="modal-tag"><i class="fa-solid fa-check"></i> ${a}</span>`).join('')}
        </div>
      </div>
      <div class="modal-footer-row">
        <div class="modal-price">
          ₹${pg.price.toLocaleString('en-IN')} <span>/month</span>
        </div>
        <button type="button" class="btn-accent" onclick="alert('Booking request sent for ${pg.name}!')">Book Now</button>
      </div>
    </div>
  `;

  detailsModal.classList.remove("hidden");
  document.body.style.overflow = "hidden";
}

window.closeModal = function() {
  const detailsModal = document.getElementById("detailsModal");
  if (detailsModal) detailsModal.classList.add("hidden");
  document.body.style.overflow = "auto";
}

window.toggleWishlist = function(id) {
  if (wishlist.includes(id)) {
    wishlist = wishlist.filter(item => item !== id);
  } else {
    wishlist.push(id);
  }
  
  const wishlistCount = document.getElementById("wishlistCount");
  if (wishlistCount) {
    if (wishlist.length > 0) {
      wishlistCount.innerText = wishlist.length;
      wishlistCount.classList.remove("hidden");
    } else {
      wishlistCount.classList.add("hidden");
    }
  }
  
  filterPGs();
}

window.openWishlistModal = function() {
  const wishlistContainer = document.getElementById("wishlistContainer");
  const wishlistModal = document.getElementById("wishlistModal");
  if (!wishlistContainer || !wishlistModal) return;

  wishlistContainer.innerHTML = "";

  if (wishlist.length === 0) {
    wishlistContainer.innerHTML = `<p style="text-align:center; color:var(--text-muted); padding:20px;">No liked PGs yet!</p>`;
  } else {
    wishlist.forEach(id => {
      const pg = pgData.find(p => p.id === id);
      if (pg) {
        const itemHtml = `
          <div class="wishlist-item">
            <img src="${pg.image}" alt="${pg.name}">
            <div class="wishlist-info">
              <h4>${pg.name}</h4>
              <p>₹${pg.price.toLocaleString('en-IN')}/month</p>
            </div>
            <button type="button" class="btn-remove-wishlist" onclick="toggleWishlist(${pg.id}); openWishlistModal();">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        `;
        wishlistContainer.insertAdjacentHTML("beforeend", itemHtml);
      }
    });
  }

  wishlistModal.classList.remove("hidden");
}