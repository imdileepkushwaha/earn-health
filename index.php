<?php
require_once __DIR__ . '/config/database.php';

$activeNav = 'home';

// Fetch active products
$products = [];
try {
    $products = $pdo->query("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN product_categories c ON c.id = p.category_id
        WHERE p.status = 'active'
        ORDER BY p.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {}

$totalProducts = count($products);

require_once __DIR__ . '/includes/site_header.php';
?>

    <!-- Hero Showcase Banner -->
    <section class="ec-hero">
        <div class="ec-hero-glow-1"></div>
        <div class="ec-hero-glow-2"></div>
        <div class="ec-shell">
            <div class="ec-hero-grid">
                <div>
                    <span class="ec-hero-tag">🌱 100% Certified Organic &amp; Ayurvedic</span>
                    <h1>Natural Health &amp; Nutrition for a <span class="gradient-text">Vibrant Life</span></h1>
                    <p class="lead">
                        Clinically validated Ayurvedic formulations, superfoods, and vitality extracts crafted to restore balance, energy, and radiant health naturally.
                    </p>
                    <div class="ec-hero-btns">
                        <a href="#catalog" class="btn-ec-primary">
                            <span>Shop Best Sellers</span>
                            <span>↓</span>
                        </a>
                        <a href="franchise/login.php" class="btn-ec-secondary">
                            <span>Own a Franchise Store</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>

                <div class="ec-hero-card">
                    <img src="uploads/products/ashwagandha_gold.jpg" alt="Earn Health Ashwagandha Gold Extract" class="ec-hero-card-img" onerror="this.style.display='none'">
                    <span class="badge-flash" style="background:#f59e0b;color:#000;font-size:0.75rem;padding:0.2rem 0.6rem;border-radius:999px;font-weight:800">FEATURED FORMULA</span>
                    <h3 style="margin-top:0.75rem">Ashwagandha Gold Extract</h3>
                    <p>60 Veg Capsules • KSM-66 Certified • Stress &amp; Vitality</p>
                    <div style="font-size:1.4rem;font-weight:800;color:#34d399;margin-bottom:1rem">₹549 <span style="font-size:0.9rem;color:#94a3b8;text-decoration:line-through">₹799</span></div>
                    <button type="button" class="btn-ec-primary" style="width:100%;justify-content:center" onclick="addToCart(7, 'Earn Health Ashwagandha Gold Extract', 549, 'uploads/products/ashwagandha_gold.jpg', 799)">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Badges Bar -->
    <section class="ec-trust-bar">
        <div class="ec-shell">
            <div class="ec-trust-grid">
                <div class="ec-trust-item">
                    <div class="ec-trust-ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div class="ec-trust-copy">
                        <strong>100% Certified Pure</strong>
                        <span>Authentic herbal ingredients</span>
                    </div>
                </div>

                <div class="ec-trust-item">
                    <div class="ec-trust-ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    </div>
                    <div class="ec-trust-copy">
                        <strong>Pan-India Express Delivery</strong>
                        <span>Direct to your doorstep in 2-4 days</span>
                    </div>
                </div>

                <div class="ec-trust-item">
                    <div class="ec-trust-ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div class="ec-trust-copy">
                        <strong>Doctor &amp; Vaidya Formulated</strong>
                        <span>Safe, effective &amp; tested</span>
                    </div>
                </div>

                <div class="ec-trust-item">
                    <div class="ec-trust-ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    </div>
                    <div class="ec-trust-copy">
                        <strong>Secure Ordering</strong>
                        <span>UPI, Cards &amp; WhatsApp Assist</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Product Catalog Section -->
    <main class="ec-section" id="catalog">
        <div class="ec-shell">
            <div class="ec-section-head">
                <div>
                    <span class="ec-section-kicker">Our Health Formulations</span>
                    <h2 class="ec-section-title">Explore Trending Wellness Products</h2>
                </div>

                <!-- Interactive Category Filter Chips -->
                <div class="ec-cats-grid">
                    <button type="button" class="ec-cat-chip is-active" data-cat-id="all">All Products (<?= $totalProducts ?>)</button>
                    <?php foreach ($categories as $cat): ?>
                    <button type="button" class="ec-cat-chip" data-cat-id="<?= (int) $cat['id'] ?>"><?= e($cat['name']) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="ec-products-grid" id="productGrid">
                <?php if (!$products): ?>
                    <div style="grid-column:1/-1;text-align:center;padding:4rem 1rem;background:#fff;border-radius:16px;border:1px solid var(--border)">
                        <strong style="font-size:1.2rem;color:var(--dark)">Catalog is being refreshed!</strong>
                        <p style="color:var(--muted);margin-top:0.35rem">Check back shortly or visit our franchise center.</p>
                    </div>
                <?php else: foreach ($products as $p): 
                    $price = (float) $p['price'];
                    $mrp = (float) ($p['mrp'] ?: $price);
                    $discount = (int) ($p['discount_percent'] ?: ($mrp > $price ? round((($mrp - $price) / $mrp) * 100) : 0));
                    $img = !empty($p['thumbnail']) ? $p['thumbnail'] : 'uploads/branding/logo_20260928160803_68a005.png';
                    $badgeText = !empty($p['offer_flash_text']) ? $p['offer_flash_text'] : ($discount > 0 ? "{$discount}% OFF" : 'PURE');
                ?>
                <div class="ec-card" 
                     data-category-id="<?= (int) $p['category_id'] ?>" 
                     data-name="<?= e($p['name']) ?>" 
                     data-desc="<?= e($p['description']) ?>">
                    
                    <div class="ec-card-media">
                        <span class="ec-badge-corner"><?= e($badgeText) ?></span>
                        <?php if ((float) ($p['bv'] ?? 0) > 0): ?>
                        <span class="ec-bv-pill"><?= (float) $p['bv'] ?> BV</span>
                        <?php endif; ?>
                        
                        <img src="<?= e($img) ?>" alt="<?= e($p['name']) ?>" class="ec-card-img" onerror="this.src='uploads/branding/logo_20260928160803_68a005.png'">
                    </div>

                    <div class="ec-card-body">
                        <div class="ec-card-rating">
                            <span>★★★★★</span>
                            <span class="reviews">4.9 (90+ reviews)</span>
                        </div>

                        <h3 class="ec-card-title" title="<?= e($p['name']) ?>"><?= e($p['name']) ?></h3>
                        <p class="ec-card-desc"><?= e($p['description']) ?></p>

                        <div class="ec-card-prices">
                            <span class="ec-price-current"><?= currency($price) ?></span>
                            <?php if ($mrp > $price): ?>
                            <span class="ec-price-mrp"><?= currency($mrp) ?></span>
                            <span class="ec-price-save">Save <?= currency($mrp - $price) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="ec-card-foot">
                            <button type="button" class="btn-add-cart" onclick="addToCart(<?= (int) $p['id'] ?>, '<?= e(addslashes($p['name'])) ?>', <?= $price ?>, '<?= e($img) ?>', <?= $mrp ?>)">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                                <span>Add to Cart</span>
                            </button>
                            <button type="button" class="btn-quick-buy" onclick="addToCart(<?= (int) $p['id'] ?>, '<?= e(addslashes($p['name'])) ?>', <?= $price ?>, '<?= e($img) ?>', <?= $mrp ?>); document.getElementById('ecCartDrawer').classList.add('is-open');">
                                Buy Now
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </main>

    <!-- Franchise Partnership Banner -->
    <section class="ec-shell" id="franchiseBanner">
        <div class="ec-franchise-banner">
            <div class="ec-fr-grid">
                <div>
                    <span class="ec-hero-tag" style="background:rgba(52,211,153,0.18);color:#34d399">PARTNERSHIP OPPORTUNITY</span>
                    <h2>Start an <?= e($company) ?> Distribution Franchise in Your Area</h2>
                    <p>
                        Partner with India's fastest-growing Ayurvedic wellness network. Enjoy high retail margins, direct factory stock replenishment, and dedicated distributor support.
                    </p>
                    <div class="ec-fr-perks">
                        <div class="ec-fr-perk">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Multi-Tier Margins (Up to 15%)</span>
                        </div>
                        <div class="ec-fr-perk">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Exclusive Territory Rights</span>
                        </div>
                        <div class="ec-fr-perk">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Zero Royalty / Direct Stock</span>
                        </div>
                        <div class="ec-fr-perk">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Complete Digital Billing Terminal</span>
                        </div>
                    </div>
                    <div style="display:flex;gap:1rem;flex-wrap:wrap">
                        <a href="contact.php" class="btn-ec-primary">Apply for Franchise</a>
                        <a href="franchise/login.php" class="btn-ec-secondary">Franchise Terminal Login →</a>
                    </div>
                </div>

                <div style="text-align:center;background:rgba(255,255,255,0.06);padding:2rem;border-radius:16px;border:1px solid rgba(255,255,255,0.15)">
                    <div style="font-size:3rem;margin-bottom:0.5rem">🏆</div>
                    <strong style="display:block;font-size:1.3rem;margin-bottom:0.25rem">500+ Franchisees</strong>
                    <p style="font-size:0.88rem;color:#94a3b8">Active across Maharashtra, Gujarat, MP, UP &amp; Rajasthan</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="ec-section ec-why-section" id="whyUs">
        <div class="ec-shell">
            <div style="text-align:center;max-width:720px;margin:0 auto 3.5rem">
                <div class="ec-why-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    The Pure Ayurveda Standard
                </div>
                <h2 class="ec-section-title" style="margin-bottom:0.75rem">Why Health-Conscious Families Choose <?= e($company) ?></h2>
                <p style="color:var(--slate);font-size:1.02rem;line-height:1.6">Rooted in authentic Vedic traditions and validated by rigorous laboratory science. 100% pure herbal formulations crafted for enduring vitality.</p>
            </div>

            <div class="ec-why-grid">
                <!-- Card 1 -->
                <div class="ec-why-card">
                    <div class="ec-why-icon-box green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
                            <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
                        </svg>
                    </div>
                    <span class="ec-why-pill green">Himalayan Sourced</span>
                    <h3 class="ec-why-title">Wild-Crafted Herbs</h3>
                    <p class="ec-why-desc">Responsibly handpicked at peak seasonal potency from pristine Himalayan valleys and certified organic Vedic farms.</p>
                    <div class="ec-why-check">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        100% Organically Sourced
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="ec-why-card">
                    <div class="ec-why-icon-box blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 18h8"/>
                            <path d="M3 22h18"/>
                            <path d="M14 22a7 7 0 1 0 0-14h-1"/>
                            <path d="M9 14h2"/>
                            <path d="M9 12a2 2 0 0 1-2-2V6h6v4a2 2 0 0 1-2 2Z"/>
                            <path d="M12 6V3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3"/>
                        </svg>
                    </div>
                    <span class="ec-why-pill blue">NABL Accredited</span>
                    <h3 class="ec-why-title">Triple-Stage Lab Tested</h3>
                    <p class="ec-why-desc">Every batch undergoes rigorous multi-tier testing for heavy metals, pesticides, microbial safety, and active phyto-nutrients.</p>
                    <div class="ec-why-check">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        Zero Synthetic Fillers
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="ec-why-card">
                    <div class="ec-why-icon-box amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                    </div>
                    <span class="ec-why-pill amber">Fast Cellular Uptake</span>
                    <h3 class="ec-why-title">4X Bioavailability</h3>
                    <p class="ec-why-desc">Infused with natural bio-enhancers like organic Piperine and cold-pressed lipids for accelerated intestinal absorption and deeper nourish.</p>
                    <div class="ec-why-check">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        Maximum Bio-Potency
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="ec-why-card">
                    <div class="ec-why-icon-box purple">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"/>
                            <path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4"/>
                            <circle cx="20" cy="10" r="2"/>
                        </svg>
                    </div>
                    <span class="ec-why-pill purple">Expert Care</span>
                    <h3 class="ec-why-title">Doctor &amp; Vaidya Support</h3>
                    <p class="ec-why-desc">Complimentary access to certified Ayurvedic practitioners for personalized dosage plans, lifestyle tips, and diet guidance.</p>
                    <div class="ec-why-check">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        Free 1-on-1 Consultation
                    </div>
                </div>
            </div>

            <!-- Trust Certifications Bar -->
            <div class="ec-trust-cert-strip">
                <div class="ec-trust-cert-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>AYUSH Ministry Standard</span>
                </div>
                <div class="ec-trust-cert-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                    <span>GMP Certified Facility</span>
                </div>
                <div class="ec-trust-cert-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 14.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm1-5.5a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1z"/></svg>
                    <span>100% Vegetarian &amp; Pure</span>
                </div>
                <div class="ec-trust-cert-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>Heavy Metal Screened</span>
                </div>
                <div class="ec-trust-cert-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    <span>Made with Pride in India</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Customer Reviews / Testimonials -->
    <section class="ec-section ec-reviews-section" id="reviews">
        <div class="ec-shell">
            <div style="text-align:center;max-width:700px;margin:0 auto 2.5rem">
                <span class="ec-section-kicker">Verified Buyer Experiences</span>
                <h2 class="ec-section-title">Real Results from Real Wellness Journeys</h2>
                <p style="color:var(--slate);font-size:1.02rem;line-height:1.6">Authentic feedback from thousands of customers and certified franchise partners across India.</p>
            </div>

            <!-- Aggregate Score Card -->
            <div class="ec-reviews-summary-bar">
                <div class="ec-rating-overall">
                    <div class="ec-rating-big-num">4.9</div>
                    <div>
                        <div class="ec-rating-stars-wrap">
                            <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <div class="ec-rating-count-text">Based on <strong>2,450+ verified customer reviews</strong> across India</div>
                    </div>
                </div>
                <div class="ec-review-highlights">
                    <div class="ec-highlight-box">
                        <span class="ec-highlight-val">98.4%</span>
                        <span class="ec-highlight-lbl">Satisfaction Rate</span>
                    </div>
                    <div class="ec-highlight-box">
                        <span class="ec-highlight-val">10-14 Days</span>
                        <span class="ec-highlight-lbl">Avg. Visible Energy Boost</span>
                    </div>
                    <div class="ec-highlight-box">
                        <span class="ec-highlight-val">500+</span>
                        <span class="ec-highlight-lbl">Franchise Centers</span>
                    </div>
                </div>
            </div>

            <!-- Customer Review Cards -->
            <div class="ec-reviews-grid">
                <!-- Review 1 -->
                <div class="ec-review-card">
                    <div class="ec-review-header">
                        <div class="ec-review-avatar av-1">RP</div>
                        <div class="ec-review-user-info">
                            <div class="ec-review-name">
                                Rajesh Patel
                                <span class="ec-verified-pill">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    Verified Buyer
                                </span>
                            </div>
                            <span class="ec-review-loc">Ahmedabad, Gujarat</span>
                        </div>
                    </div>
                    <div class="ec-review-stars">
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <span class="ec-product-tag">🌿 Pure Himalayan Shilajit Resin (20g)</span>
                    <h4 class="ec-review-title">"Incredible surge in daily energy and endurance"</h4>
                    <p class="ec-review-text">
                        "I have tested multiple Shilajit brands before, but Earn Health's resin has a genuine earthy aroma and dissolves seamlessly in lukewarm milk. Within 10 days my chronic afternoon fatigue vanished completely. Truly authentic quality!"
                    </p>
                    <div class="ec-review-footer">
                        <span>Order #EH-89421</span>
                        <span class="ec-recommend-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                            Recommends this product
                        </span>
                    </div>
                </div>

                <!-- Review 2 -->
                <div class="ec-review-card">
                    <div class="ec-review-header">
                        <div class="ec-review-avatar av-2">SM</div>
                        <div class="ec-review-user-info">
                            <div class="ec-review-name">
                                Sneha Mishra
                                <span class="ec-verified-pill">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    Verified Buyer
                                </span>
                            </div>
                            <span class="ec-review-loc">Pune, Maharashtra</span>
                        </div>
                    </div>
                    <div class="ec-review-stars">
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <span class="ec-product-tag">🌿 Ashwagandha Gold Extract</span>
                    <h4 class="ec-review-title">"Deep, restful sleep without any morning grogginess"</h4>
                    <p class="ec-review-text">
                        "High work stress had severely disrupted my sleep cycle for months. Taking Ashwagandha Gold after dinner brought back calm, restful sleep within a week. I wake up recharged and sharp. Delivery was prompt in secure packaging."
                    </p>
                    <div class="ec-review-footer">
                        <span>Order #EH-78210</span>
                        <span class="ec-recommend-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                            Recommends this product
                        </span>
                    </div>
                </div>

                <!-- Review 3 -->
                <div class="ec-review-card">
                    <div class="ec-review-header">
                        <div class="ec-review-avatar av-3">VK</div>
                        <div class="ec-review-user-info">
                            <div class="ec-review-name">
                                Dr. Vikram Kulkarni
                                <span class="ec-verified-pill">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    Franchise Partner
                                </span>
                            </div>
                            <span class="ec-review-loc">Indore, Madhya Pradesh</span>
                        </div>
                    </div>
                    <div class="ec-review-stars">
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <span class="ec-product-tag">🌿 Curcumin 95% + Triphala Juice</span>
                    <h4 class="ec-review-title">"Outstanding bioavailability &amp; real wellness impact"</h4>
                    <p class="ec-review-text">
                        "We run an Earn Health franchise and consultation center in Indore. The organic formulations sell on repeat because patients experience tangible gut and joint relief. Corporate backend support and stock dispatch are world-class."
                    </p>
                    <div class="ec-review-footer">
                        <span>Franchise ID #FR-4029</span>
                        <span class="ec-recommend-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                            Recommends this product
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>

