<?php
require_once __DIR__ . '/config/database.php';

$company = setting('company_name', 'Earn Health');
$logoUrl = company_logo_url();
$favUrl = company_favicon_url();
$tagline = setting('company_tagline', '100% Pure Natural & Ayurvedic Wellness Store');

$phone = setting('contact_phone', '+91 98765 43210');
$whatsappRaw = (string) setting('contact_whatsapp', '919876543210');
$whatsapp = preg_replace('/\D+/', '', $whatsappRaw) ?: '919876543210';
$email = setting('contact_email', setting('support_email', 'support@earnhealth.com'));
$address = setting('contact_address', 'Corporate Park, Health & Wellness Hub, India');

// Fetch active categories
$categories = [];
try {
    $categories = $pdo->query("SELECT id, name, description FROM product_categories WHERE status = 'active' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {}

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($company) ?> — Pure Ayurvedic Health, Nutrition &amp; Wellness Store</title>
    <meta name="description" content="Shop 100% pure Ayurvedic formulations, herbal extracts, and premium wellness supplements online at <?= e($company) ?>. GMP Certified, Fast Delivery.">
    <?php if ($favUrl): ?><link rel="icon" href="<?= e($favUrl) ?>"><?php endif; ?>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- E-Commerce Styles -->
    <link rel="stylesheet" href="assets/css/ecommerce.css?v=<?= (int) @filemtime(__DIR__ . '/assets/css/ecommerce.css') ?>">
</head>
<body class="ec-body">

    <!-- Top Announcement Bar -->
    <div class="ec-topbar">
        <div class="ec-shell ec-topbar-inner">
            <div class="ec-topbar-msg">
                <span class="badge-flash">FREE DELIVERY</span>
                <span>Free shipping across India on orders above ₹999 | 100% Authentic Ayurvedic Formulas</span>
            </div>
            <div class="ec-topbar-links">
                <span>📞 Support: <?= e($phone) ?></span>
                <a href="https://wa.me/<?= e($whatsapp) ?>" target="_blank" rel="noopener">💬 WhatsApp</a>
                <a href="franchise/login.php">🏢 Franchise Portal</a>
            </div>
        </div>
    </div>

    <!-- Main Sticky Header -->
    <header class="ec-header" id="ecHeader">
        <div class="ec-shell">
            <div class="ec-header-main">
                <!-- Brand Logo -->
                <a href="index.php" class="ec-logo-wrap">
                    <?php if ($logoUrl): ?>
                        <img src="<?= e($logoUrl) ?>" alt="<?= e($company) ?>" class="ec-logo-img">
                    <?php else: ?>
                        <div>
                            <span class="ec-logo-text"><?= e($company) ?></span>
                            <span class="ec-logo-tag">Wellness Store</span>
                        </div>
                    <?php endif; ?>
                </a>

                <!-- Live Search Bar -->
                <div class="ec-search-bar">
                    <input type="text" id="ecProductSearch" placeholder="Search Ayurvedic herbs, vitamins, supplements..." autocomplete="off">
                    <button type="button" aria-label="Search">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </button>
                </div>

                <!-- Header Actions -->
                <div class="ec-actions">
                    <a href="franchise/login.php" class="ec-btn-portal" title="Franchise Partner Login">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        <span>Franchise Login</span>
                    </a>
                    
                    <button type="button" class="ec-btn-cart" data-open-cart aria-label="View Shopping Cart">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                        <span>Cart</span>
                        <span class="ec-cart-count" id="cartBadge">0</span>
                    </button>
            </div>
        </div>

        <!-- Categories & Navigation Bar Strip -->
        <nav class="ec-nav-strip" aria-label="Categories Navigation">
            <div class="ec-shell">
                <div class="ec-nav-bar">
                    <div class="ec-nav-left">
                        <button type="button" class="ec-nav-link is-active" data-strip-cat="all">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            <span>All Products</span>
                        </button>

                        <?php foreach ($categories as $cat): 
                            $cid = (int) $cat['id'];
                            $cname = $cat['name'];
                        ?>
                        <button type="button" class="ec-nav-link" data-strip-cat="<?= $cid ?>">
                            <span><?= e($cname) ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="ec-nav-right">
                        <a href="#catalog" class="ec-nav-link link-deals" data-strip-deals="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                            <span>Hot Deals</span>
                            <span class="ec-nav-deal-tag">33% OFF</span>
                        </a>

                        <a href="#franchiseBanner" class="ec-nav-link link-franchise">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <span>Franchise Program</span>
                        </a>

                        <a href="#reviews" class="ec-nav-link link-reviews">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <span>Reviews</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

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
    <section class="ec-section" id="whyUs" style="background:#fff;border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
        <div class="ec-shell">
            <div style="text-align:center;max-width:680px;margin:0 auto 3rem">
                <span class="ec-section-kicker">Our Quality Promise</span>
                <h2 class="ec-section-title">Why Health Conscious Families Choose <?= e($company) ?></h2>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:2rem">
                <div style="text-align:center;padding:1.5rem">
                    <div style="width:56px;height:56px;background:var(--primary-light);color:var(--primary);border-radius:50%;display:grid;place-items:center;margin:0 auto 1.25rem;font-size:1.5rem">🌿</div>
                    <h4 style="font-family:var(--font-heading);font-size:1.1rem;margin-bottom:0.5rem">Wild-Crafted Herbs</h4>
                    <p style="font-size:0.88rem;color:var(--slate)">Sourced responsibly from certified organic herbal farms and Himalayan valleys.</p>
                </div>

                <div style="text-align:center;padding:1.5rem">
                    <div style="width:56px;height:56px;background:var(--primary-light);color:var(--primary);border-radius:50%;display:grid;place-items:center;margin:0 auto 1.25rem;font-size:1.5rem">🔬</div>
                    <h4 style="font-family:var(--font-heading);font-size:1.1rem;margin-bottom:0.5rem">Batch Lab Tested</h4>
                    <p style="font-size:0.88rem;color:var(--slate)">Every batch is screened for heavy metals, pesticides, and verified active potency.</p>
                </div>

                <div style="text-align:center;padding:1.5rem">
                    <div style="width:56px;height:56px;background:var(--primary-light);color:var(--primary);border-radius:50%;display:grid;place-items:center;margin:0 auto 1.25rem;font-size:1.5rem">⚡</div>
                    <h4 style="font-family:var(--font-heading);font-size:1.1rem;margin-bottom:0.5rem">High Bioavailability</h4>
                    <p style="font-size:0.88rem;color:var(--slate)">Engineered with natural bio-enhancers like Piperine for maximum cellular absorption.</p>
                </div>

                <div style="text-align:center;padding:1.5rem">
                    <div style="width:56px;height:56px;background:var(--primary-light);color:var(--primary);border-radius:50%;display:grid;place-items:center;margin:0 auto 1.25rem;font-size:1.5rem">🤝</div>
                    <h4 style="font-family:var(--font-heading);font-size:1.1rem;margin-bottom:0.5rem">Direct Consumer Support</h4>
                    <p style="font-size:0.88rem;color:var(--slate)">Dedicated wellness consultations and WhatsApp order tracking assistance.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Customer Reviews / Testimonials -->
    <section class="ec-section" id="reviews">
        <div class="ec-shell">
            <div style="text-align:center;max-width:680px;margin:0 auto 2.5rem">
                <span class="ec-section-kicker">Verified Buyers</span>
                <h2 class="ec-section-title">Real Results from Real Customers</h2>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:1.5rem">
                <div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:1.75rem;box-shadow:var(--shadow-sm)">
                    <div style="color:var(--amber);margin-bottom:0.75rem;font-size:1.1rem">★★★★★</div>
                    <p style="font-size:0.92rem;color:var(--dark);line-height:1.6;margin-bottom:1rem">
                        "The Shilajit Resin has dramatically improved my daily energy levels. Genuine texture, easy to dissolve in warm milk, and I noticed the difference within 10 days."
                    </p>
                    <div style="display:flex;align-items:center;gap:0.75rem">
                        <div style="width:40px;height:40px;border-radius:50%;background:#e0f2fe;color:#0284c7;display:grid;place-items:center;font-weight:800">RP</div>
                        <div>
                            <strong style="display:block;font-size:0.9rem">Rajesh Patel</strong>
                            <small style="color:var(--muted)">Verified Buyer • Ahmedabad</small>
                        </div>
                    </div>
                </div>

                <div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:1.75rem;box-shadow:var(--shadow-sm)">
                    <div style="color:var(--amber);margin-bottom:0.75rem;font-size:1.1rem">★★★★★</div>
                    <p style="font-size:0.92rem;color:var(--dark);line-height:1.6;margin-bottom:1rem">
                        "Ashwagandha Gold is a staple for me now. Great for restful sleep and mental calmness after stressful work days. Delivery was fast and properly packaged."
                    </p>
                    <div style="display:flex;align-items:center;gap:0.75rem">
                        <div style="width:40px;height:40px;border-radius:50%;background:#ecfdf5;color:#059669;display:grid;place-items:center;font-weight:800">SM</div>
                        <div>
                            <strong style="display:block;font-size:0.9rem">Sneha Mishra</strong>
                            <small style="color:var(--muted)">Verified Buyer • Pune</small>
                        </div>
                    </div>
                </div>

                <div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:1.75rem;box-shadow:var(--shadow-sm)">
                    <div style="color:var(--amber);margin-bottom:0.75rem;font-size:1.1rem">★★★★★</div>
                    <p style="font-size:0.92rem;color:var(--dark);line-height:1.6;margin-bottom:1rem">
                        "We run an Earn Health franchise center in Indore. Products sell themselves because people see authentic results. Support from corporate team is top notch."
                    </p>
                    <div style="display:flex;align-items:center;gap:0.75rem">
                        <div style="width:40px;height:40px;border-radius:50%;background:#fef3c7;color:#d97706;display:grid;place-items:center;font-weight:800">VK</div>
                        <div>
                            <strong style="display:block;font-size:0.9rem">Vikram Kulkarni</strong>
                            <small style="color:var(--muted)">Franchise Owner • Indore</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Slide-Out Shopping Cart Drawer -->
    <div class="ec-cart-drawer" id="ecCartDrawer">
        <div class="ec-cart-backdrop" data-close-cart></div>
        <div class="ec-cart-panel">
            <div class="ec-cart-head">
                <h3>Shopping Cart (<span class="ec-cart-count">0</span>)</h3>
                <button type="button" class="ec-cart-close" data-close-cart aria-label="Close cart">×</button>
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

    <!-- Modern E-Commerce Footer -->
    <footer class="ec-footer">
        <div class="ec-shell">
            <div class="ec-footer-grid">
                <div class="ec-footer-brand">
                    <h4><?= e($company) ?></h4>
                    <p style="margin-bottom:1.25rem;line-height:1.6">
                        Pioneering accessible, standardized Ayurvedic nutrition and premium herbal wellness formulations crafted with purity and integrity.
                    </p>
                    <p style="font-size:0.85rem">📍 <?= e($address) ?></p>
                    <p style="font-size:0.85rem">✉️ <?= e($email) ?></p>
                    <p style="font-size:0.85rem">📞 <?= e($phone) ?></p>
                </div>

                <div class="ec-footer-col">
                    <h5>Categories</h5>
                    <ul>
                        <?php foreach (array_slice($categories, 0, 5) as $cat): ?>
                        <li><a href="#catalog"><?= e($cat['name']) ?></a></li>
                        <?php endforeach; ?>
                        <li><a href="#catalog">Best Sellers</a></li>
                    </ul>
                </div>

                <div class="ec-footer-col">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="#catalog">Shop All</a></li>
                        <li><a href="#franchiseBanner">Franchise Business</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="franchise/login.php">Franchise Login</a></li>
                        <li><a href="admin/login.php">Admin Panel</a></li>
                    </ul>
                </div>

                <div class="ec-footer-col">
                    <h5>Franchise Enquiry</h5>
                    <p style="margin-bottom:1rem;font-size:0.85rem">
                        Interested in starting an Earn Health wellness center? Connect directly with our business team.
                    </p>
                    <a href="https://wa.me/<?= e($whatsapp) ?>?text=<?= urlencode('Hello, I am interested in opening an Earn Health franchise in my city.') ?>" 
                       target="_blank" rel="noopener" class="btn-ec-primary" style="padding:0.65rem 1.25rem;font-size:0.88rem;display:inline-flex">
                        Chat on WhatsApp →
                    </a>
                </div>
            </div>

            <div class="ec-footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= e($company) ?>. All rights reserved. 100% Ayurvedic &amp; GMP Certified.</p>
                <div style="display:flex;gap:1.5rem;align-items:center">
                    <span>UPI &bull; NetBanking &bull; Cards Accepted</span>
                    <a href="admin/login.php" style="color:#64748b">Admin</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Interactive Scripts -->
    <script src="assets/js/ecommerce.js?v=<?= (int) @filemtime(__DIR__ . '/assets/js/ecommerce.js') ?>"></script>
</body>
</html>
