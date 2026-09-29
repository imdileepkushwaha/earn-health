<?php
/**
 * Earn Health — Common Website Footer Component
 * Can be reused across index.php, contact.php, and any new website pages.
 */
?>
    <!-- Slide-Out Shopping Cart Drawer -->
    <div class="ec-cart-drawer" id="ecCartDrawer">
        <div class="ec-cart-backdrop" data-close-cart></div>
        <div class="ec-cart-panel">
            <div class="ec-cart-head">
                <div class="ec-cart-head-left">
                    <div class="ec-cart-head-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                    </div>
                    <div class="ec-cart-head-title-wrap">
                        <div class="ec-cart-head-title-row">
                            <h3>Shopping Cart</h3>
                            <span class="ec-cart-head-badge"><span class="ec-cart-count">0</span></span>
                        </div>
                        <span class="ec-cart-head-sub">Review your selected items</span>
                    </div>
                </div>
                <button type="button" class="ec-cart-close" data-close-cart aria-label="Close cart">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="ec-cart-items" id="cartItemsList">
                <!-- Rendered dynamically via ecommerce.js -->
            </div>

            <div class="ec-cart-foot">
                <div class="ec-cart-subtotal">
                    <span>Estimated Total</span>
                    <strong id="cartSubtotal">₹0.00</strong>
                </div>
                <button type="button" class="btn-ec-checkout" id="btnWhatsappCheckout" data-phone="<?= e($whatsapp) ?>">
                    <span>💬 Order via WhatsApp / Checkout</span>
                </button>
                <div style="text-align:center;margin-top:0.65rem">
                    <small style="color:var(--muted)">Instant delivery assistance &amp; UPI payment support</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern E-Commerce Footer (Luxury Ayurvedic Theme) -->
    <footer class="ec-footer">
        <!-- VIP Wellness Club Newsletter Ribbon -->
        <div class="ec-footer-newsletter">
            <div class="ec-shell ec-footer-nl-inner">
                <div class="ec-footer-nl-info">
                    <span class="ec-footer-nl-badge">🌿 VIP Wellness Club</span>
                    <h3 class="ec-footer-nl-title">Get 10% Off Your First Order</h3>
                    <p class="ec-footer-nl-desc">Subscribe for authentic Ayurvedic health tips, seasonal discounts, and early access to new premium herbal formulations.</p>
                </div>
                <form class="ec-footer-nl-form" onsubmit="event.preventDefault(); const em=this.querySelector('input').value; if(em){ showToast('Thank you for subscribing to Earn Health VIP Club!'); this.reset(); } return false;">
                    <input type="email" class="ec-footer-nl-input" placeholder="Enter your email address..." required>
                    <button type="submit" class="ec-footer-nl-btn">Subscribe →</button>
                </form>
            </div>
        </div>

        <div class="ec-shell">
            <div class="ec-footer-grid">
                <!-- Brand presentation -->
                <div class="ec-footer-brand">
                    <a href="index.php" class="ec-footer-brand-logo-wrap" aria-label="<?= e($company) ?>">
                        <?php if ($logoUrl): ?>
                            <img src="<?= e($logoUrl) ?>" alt="<?= e($company) ?>">
                        <?php else: ?>
                            <strong style="color:var(--dark);font-size:1.1rem"><?= e($company) ?></strong>
                        <?php endif; ?>
                    </a>
                    <h4><?= e($company) ?></h4>
                    <p class="ec-footer-brand-desc">
                        Pioneering accessible, standardized Ayurvedic nutrition and premium herbal wellness formulations crafted with purity and GMP-certified integrity.
                    </p>
                    
                    <div class="ec-footer-contacts">
                        <div class="ec-footer-contact-item">
                            <span class="ec-footer-contact-icon">📍</span>
                            <span><?= e($address) ?></span>
                        </div>
                        <div class="ec-footer-contact-item">
                            <span class="ec-footer-contact-icon">📞</span>
                            <a href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>"><?= e($phone) ?></a>
                        </div>
                        <div class="ec-footer-contact-item">
                            <span class="ec-footer-contact-icon">✉️</span>
                            <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
                        </div>
                    </div>

                    <div class="ec-footer-socials">
                        <a href="https://wa.me/<?= e($whatsapp) ?>" target="_blank" rel="noopener" class="ec-footer-social-btn" title="WhatsApp" aria-label="WhatsApp">💬</a>
                        <a href="contact.php" class="ec-footer-social-btn" title="Contact Us" aria-label="Contact">✉️</a>
                        <a href="franchise/login.php" class="ec-footer-social-btn" title="Franchise Portal" aria-label="Franchise">🏢</a>
                    </div>
                </div>

                <!-- Column 2: Health Categories -->
                <div class="ec-footer-col">
                    <h5>Health Categories</h5>
                    <ul>
                        <?php foreach (array_slice($categories, 0, 5) as $cat): ?>
                        <li><a href="index.php#catalog"><span><?= e($cat['name']) ?></span></a></li>
                        <?php endforeach; ?>
                        <li><a href="index.php#catalog"><span>Immunity &amp; Herbs</span></a></li>
                        <li><a href="index.php#catalog"><span>Daily Nutrition</span></a></li>
                        <li><a href="index.php#catalog"><span>Best Sellers</span> <span class="ec-footer-tag-mini">HOT</span></a></li>
                    </ul>
                </div>

                <!-- Column 3: Customer & Portals -->
                <div class="ec-footer-col">
                    <h5>Quick Navigation</h5>
                    <ul>
                        <li><a href="index.php"><span>Store Home</span></a></li>
                        <li><a href="index.php#catalog"><span>All Products</span></a></li>
                        <li><a href="index.php#franchiseBanner"><span>Franchise Program</span> <span class="ec-footer-tag-mini" style="background:rgba(16,185,129,0.2);color:#34d399">TOP</span></a></li>
                        <li><a href="contact.php"><span>Contact &amp; Support Desk</span></a></li>
                        <li><a href="franchise/login.php"><span>Franchise Login Portal</span></a></li>
                        <li><a href="franchise/forgot-password.php"><span>Franchise Password Help</span></a></li>
                    </ul>
                </div>

                <!-- Column 4: Partner & Business Box -->
                <div class="ec-footer-col">
                    <div class="ec-footer-partner-card">
                        <span class="ec-footer-partner-tag">★ FRANCHISE BUSINESS</span>
                        <h6>Start an Earn Health Center</h6>
                        <p>Authorized wellness distribution points across India. High retail margins, marketing support, and zero royalty fees.</p>
                        <a href="https://wa.me/<?= e($whatsapp) ?>?text=<?= urlencode('Hello, I am interested in opening an Earn Health franchise in my city.') ?>" 
                           target="_blank" rel="noopener" class="ec-footer-cta-btn">
                            <span>Apply for Franchise →</span>
                        </a>
                        <a href="https://wa.me/<?= e($whatsapp) ?>" target="_blank" rel="noopener" class="ec-footer-wa-quick">
                            <span>💬 Direct WhatsApp Order Desk</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Trust Badges & Accepted Payments Strip -->
            <div class="ec-footer-trust-strip">
                <div class="ec-footer-trust-items">
                    <div class="ec-footer-trust-pill">
                        <span class="icon">🌿</span>
                        <span>100% Ayurvedic &amp; Natural</span>
                    </div>
                    <div class="ec-footer-trust-pill">
                        <span class="icon">⚡</span>
                        <span>Fast Express Dispatch</span>
                    </div>
                    <div class="ec-footer-trust-pill">
                        <span class="icon">🛡️</span>
                        <span>GMP Certified Quality</span>
                    </div>
                    <div class="ec-footer-trust-pill">
                        <span class="icon">🔒</span>
                        <span>256-Bit SSL Encrypted</span>
                    </div>
                </div>

                <div class="ec-footer-payments">
                    <span style="font-size:0.8rem;color:#cbd5e1;font-weight:700">Accepted:</span>
                    <span class="ec-payment-badge">UPI</span>
                    <span class="ec-payment-badge">GPay</span>
                    <span class="ec-payment-badge">PhonePe</span>
                    <span class="ec-payment-badge">Paytm</span>
                    <span class="ec-payment-badge">Cards</span>
                    <span class="ec-payment-badge">COD</span>
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="ec-footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= e($company) ?>. All rights reserved. Crafted with Ayurvedic purity for a healthier Bharat.</p>
                <div style="display:flex;gap:1.25rem;align-items:center">
                    <a href="contact.php" style="font-size:0.82rem">Need Help?</a>
                    <a href="franchise/login.php" style="font-size:0.82rem">Franchise</a>
                    <!-- <a href="admin/login.php" class="ec-admin-link">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span>Admin</span>
                    </a> -->
                </div>
            </div>
        </div>
    </footer>

    <!-- Interactive E-Commerce Scripts -->
    <script src="assets/js/ecommerce.js?v=<?= (int) @filemtime(__DIR__ . '/../assets/js/ecommerce.js') ?>"></script>

    <?php if (!empty($extraJs)): ?>
        <?php foreach ((array) $extraJs as $jsFile): ?>
            <script src="<?= e($jsFile) ?>?v=<?= (int) @filemtime(__DIR__ . '/../' . ltrim($jsFile, '/')) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
