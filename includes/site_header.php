<?php
/**
 * Earn Health — Common Website Header Component
 * Can be reused across index.php, contact.php, and any new website pages.
 */
require_once __DIR__ . '/../config/database.php';

// Branding & Company Settings
$company = $company ?? setting('company_name', 'Earn Health');
$logoUrl = $logoUrl ?? company_logo_url();
$favUrl = $favUrl ?? company_favicon_url();
$tagline = $tagline ?? setting('company_tagline', '100% Pure Natural & Ayurvedic Wellness Store');

$phone = $phone ?? setting('contact_phone', '+91 98765 43210');
$whatsappRaw = (string) setting('contact_whatsapp', '919876543210');
$whatsapp = $whatsapp ?? (preg_replace('/\D+/', '', $whatsappRaw) ?: '919876543210');
$email = $email ?? setting('contact_email', setting('support_email', 'earnhealth.ceo@gmail.com'));
$address = $address ?? setting('contact_address', 'Mouja Alamganj , Kaptanpara Ward No 41  Ps Sadar');

// Fetch active product categories if not already provided
if (!isset($categories) || !is_array($categories)) {
    $categories = [];
    try {
        $categories = $pdo->query("SELECT id, name, description FROM product_categories WHERE status = 'active' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {}
}

// Page Metadata defaults
$pageTitle = $pageTitle ?? ($company . ' — Pure Ayurvedic Health, Nutrition &amp; Wellness Store');
$metaDescription = $metaDescription ?? ('Shop 100% pure Ayurvedic formulations, herbal extracts, and premium wellness supplements online at ' . $company . '. GMP Certified, Fast Delivery.');
$activeNav = $activeNav ?? 'home';
$isHome = ($activeNav === 'home' || basename($_SERVER['PHP_SELF']) === 'index.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <?php if ($favUrl): ?><link rel="icon" href="<?= e($favUrl) ?>"><?php endif; ?>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Global E-Commerce Styles -->
    <link rel="stylesheet" href="assets/css/ecommerce.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/ecommerce.css') ?>">

    <!-- Extra Page-Specific CSS -->
    <?php if (!empty($extraCss)): ?>
        <?php foreach ((array) $extraCss as $cssFile): ?>
            <link rel="stylesheet" href="<?= e($cssFile) ?>?v=<?= (int) @filemtime(__DIR__ . '/../' . ltrim($cssFile, '/')) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="ec-body">

    <!-- Top Announcement Bar (Luxury Ayurvedic Theme) -->
    <div class="ec-topbar">
        <div class="ec-shell ec-topbar-inner">
            <div class="ec-topbar-left">
                <span class="ec-topbar-pill">
                    <span class="ec-pulse-dot" aria-hidden="true"></span>
                    <span>100% Ayurvedic &amp; GMP Certified</span>
                </span>
                <span class="ec-topbar-divider" aria-hidden="true"></span>
                <div class="ec-topbar-offer">
                    <span>🚚 <strong>Free Express Delivery</strong> on orders above ₹999</span>
                    <span class="ec-topbar-tag">Code: <strong>AYUR10</strong></span>
                </div>
            </div>

            <div class="ec-topbar-right">
                <a href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>" class="ec-topbar-link" title="Call Customer Support">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    <span><?= e($phone) ?></span>
                </a>

                <span class="ec-topbar-divider" aria-hidden="true"></span>

                <a href="https://wa.me/<?= e($whatsapp) ?>?text=Hello%20Earn%20Health" target="_blank" rel="noopener" class="ec-topbar-link" title="Chat on WhatsApp">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                    </svg>
                    <span>WhatsApp</span>
                </a>

                <a href="franchise/login.php" class="ec-topbar-btn" title="Franchise Portal Login">
                    <span>🏢 Franchise Portal</span>
                    <span class="ec-topbar-arrow">→</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Mobile Slide-Out Navigation Drawer -->
    <div class="ec-mobile-drawer" id="ecMobileDrawer" aria-hidden="true">
        <div class="ec-drawer-backdrop" id="ecDrawerBackdrop"></div>
        <div class="ec-drawer-panel" id="ecDrawerPanel">
            <div class="ec-drawer-head">
                <a href="index.php" class="ec-drawer-logo" aria-label="<?= e($company) ?>">
                    <?php if ($logoUrl): ?>
                        <img src="<?= e($logoUrl) ?>" alt="<?= e($company) ?>" class="ec-drawer-logo-img">
                    <?php else: ?>
                        <div class="ec-drawer-logo-wrap">
                            <span class="ec-drawer-logo-text"><?= e($company) ?></span>
                            <span class="ec-drawer-tagline">Ayurvedic Wellness Store</span>
                        </div>
                    <?php endif; ?>
                </a>
                <button type="button" class="ec-drawer-close" id="ecDrawerClose" aria-label="Close navigation menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="ec-drawer-body">
                <!-- Franchise Quick Partner Card -->
                <div class="ec-drawer-franchise-box">
                    <div class="ec-drawer-fr-tag">★ FRANCHISE BUSINESS</div>
                    <h6>Partner with <?= e($company) ?></h6>
                    <p>Open an authorized wellness store in your city. High profit margins &amp; complete brand backing.</p>
                    <div class="ec-drawer-fr-btns">
                        <a href="franchise/login.php" class="ec-drawer-fr-btn-login">Franchise Login →</a>
                        <a href="https://wa.me/<?= e($whatsapp) ?>?text=<?= urlencode('Hello, I am interested in opening an Earn Health franchise in my area.') ?>" target="_blank" rel="noopener" class="ec-drawer-fr-btn-wa">Apply on WhatsApp</a>
                    </div>
                </div>

                <!-- Primary Store Links -->
                <div class="ec-drawer-section">
                    <div class="ec-drawer-heading">Store Navigation</div>
                    <nav class="ec-drawer-nav">
                        <a href="index.php" class="ec-drawer-nav-item <?= ($activeNav === 'home') ? 'is-active' : '' ?>">
                            <span class="ec-drawer-item-icon">🏠</span>
                            <span>Store Home</span>
                        </a>
                        <a href="index.php#catalog" class="ec-drawer-nav-item" onclick="document.getElementById('ecMobileDrawer')?.classList.remove('is-open'); document.body.classList.remove('ec-drawer-open');">
                            <span class="ec-drawer-item-icon">🌿</span>
                            <span>All Products Catalog</span>
                            <span class="ec-drawer-count"><?= !empty($categories) ? count($categories) . ' Categories' : 'Store' ?></span>
                        </a>
                        <a href="index.php#catalog" class="ec-drawer-nav-item deals-item" onclick="document.getElementById('ecMobileDrawer')?.classList.remove('is-open'); document.body.classList.remove('ec-drawer-open'); document.querySelector('[data-strip-deals]')?.click();">
                            <span class="ec-drawer-item-icon">🔥</span>
                            <span>Hot Deals &amp; Discounts</span>
                            <span class="ec-drawer-badge-hot">33% OFF</span>
                        </a>
                        <a href="index.php#whyUs" class="ec-drawer-nav-item" onclick="document.getElementById('ecMobileDrawer')?.classList.remove('is-open'); document.body.classList.remove('ec-drawer-open');">
                            <span class="ec-drawer-item-icon">🛡️</span>
                            <span>Why Choose Us</span>
                        </a>
                        <a href="index.php#franchiseBanner" class="ec-drawer-nav-item" onclick="document.getElementById('ecMobileDrawer')?.classList.remove('is-open'); document.body.classList.remove('ec-drawer-open');">
                            <span class="ec-drawer-item-icon">🏢</span>
                            <span>Franchise Business Model</span>
                        </a>
                        <a href="index.php#reviews" class="ec-drawer-nav-item" onclick="document.getElementById('ecMobileDrawer')?.classList.remove('is-open'); document.body.classList.remove('ec-drawer-open');">
                            <span class="ec-drawer-item-icon">⭐</span>
                            <span>Customer Reviews</span>
                        </a>
                        <a href="contact.php" class="ec-drawer-nav-item <?= ($activeNav === 'contact') ? 'is-active' : '' ?>">
                            <span class="ec-drawer-item-icon">✉️</span>
                            <span>Contact &amp; Support Desk</span>
                        </a>
                    </nav>
                </div>

                <!-- Product Categories Quick Selector -->
                <?php if (!empty($categories)): ?>
                <div class="ec-drawer-section">
                    <div class="ec-drawer-heading">Shop By Category</div>
                    <div class="ec-drawer-cat-chips">
                        <button type="button" class="ec-drawer-cat-chip is-all" onclick="document.getElementById('ecMobileDrawer')?.classList.remove('is-open'); document.body.classList.remove('ec-drawer-open'); document.querySelector('[data-strip-cat=\'all\']')?.click() || (window.location.href='index.php#catalog');">
                            <span>All Items</span>
                        </button>
                        <?php foreach ($categories as $cat): ?>
                        <button type="button" class="ec-drawer-cat-chip" onclick="document.getElementById('ecMobileDrawer')?.classList.remove('is-open'); document.body.classList.remove('ec-drawer-open'); document.querySelector('[data-strip-cat=\'<?= (int) $cat['id'] ?>\']')?.click() || (window.location.href='index.php#catalog');">
                            <span><?= e($cat['name']) ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Direct Customer Support Box -->
                <div class="ec-drawer-section">
                    <div class="ec-drawer-heading">Customer Support</div>
                    <div class="ec-drawer-contact-list">
                        <a href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>" class="ec-drawer-contact-row">
                            <span class="icon">📞</span>
                            <div>
                                <span class="lbl">Customer Care</span>
                                <strong><?= e($phone) ?></strong>
                            </div>
                        </a>
                        <a href="https://wa.me/<?= e($whatsapp) ?>?text=Hello%20Earn%20Health" target="_blank" rel="noopener" class="ec-drawer-contact-row">
                            <span class="icon">💬</span>
                            <div>
                                <span class="lbl">WhatsApp Helpdesk</span>
                                <strong>Instant Order &amp; Product Chat</strong>
                            </div>
                        </a>
                        <a href="mailto:<?= e($email) ?>" class="ec-drawer-contact-row">
                            <span class="icon">✉️</span>
                            <div>
                                <span class="lbl">Email Support</span>
                                <strong><?= e($email) ?></strong>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="ec-drawer-foot">
                <a href="franchise/login.php" class="ec-drawer-login-btn">
                    <span>🏢 Franchise Partner Login</span>
                    <span>→</span>
                </a>
                <p class="ec-drawer-copyright">&copy; <?= date('Y') ?> <?= e($company) ?> • 100% Ayurvedic Wellness</p>
            </div>
        </div>
    </div>

    <!-- Mobile Categories Bottom Sheet Modal (Independent from header stacking context) -->
    <div class="ec-catsheet-modal" id="ecMobileCatSheet" aria-hidden="true">
        <div class="ec-catsheet-backdrop" id="ecCatSheetBackdrop"></div>
        <div class="ec-catsheet-panel" id="ecCatSheetPanel">
            <div class="ec-catsheet-handle"></div>
            <div class="ec-catsheet-head">
                <div class="ec-catsheet-head-left">
                    <span class="ec-catsheet-title">🌿 Browse Categories</span>
                    <span class="ec-catsheet-count"><?= count($categories) ?> Types</span>
                </div>
                <button type="button" class="ec-catsheet-close" id="ecCatSheetClose" aria-label="Close categories">✕</button>
            </div>

            <div class="ec-catsheet-list">
                <a href="index.php#catalog" class="ec-catsheet-link is-all <?= ($activeNav === 'home') ? 'is-active' : '' ?>" <?= $isHome ? 'data-strip-cat="all"' : '' ?>>
                    <div class="ec-catsheet-icon">🌿</div>
                    <div class="ec-catsheet-info">
                        <strong>All Products</strong>
                        <span>Explore complete herbal wellness store</span>
                    </div>
                    <span class="ec-catsheet-arrow">→</span>
                </a>

                <?php foreach ($categories as $cat): 
                    $cid = (int) $cat['id'];
                    $cname = $cat['name'];
                    $cdesc = $cat['description'] ?? '100% natural Ayurvedic formulations';
                ?>
                <a href="index.php#catalog" class="ec-catsheet-link" <?= $isHome ? "data-strip-cat=\"$cid\"" : '' ?>>
                    <div class="ec-catsheet-icon">🌱</div>
                    <div class="ec-catsheet-info">
                        <strong><?= e($cname) ?></strong>
                        <?php if ($cdesc): ?>
                            <span><?= e(mb_strimwidth($cdesc, 0, 42, '...')) ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="ec-catsheet-arrow">→</span>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="ec-catsheet-foot">
                <a href="index.php#catalog" class="ec-catsheet-deal-btn" <?= $isHome ? 'data-strip-deals="true"' : '' ?>>
                    <span>🔥 View Hot Deals &amp; Discounts</span>
                    <span class="ec-nav-deal-tag">33% OFF</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Sticky Header -->
    <header class="ec-header" id="ecHeader">
        <div class="ec-shell">
            <div class="ec-header-main">
                <!-- Left: Hamburger Toggle Button (mobile) + Brand Logo -->
                <div class="ec-header-left">
                    <button type="button" class="ec-mobile-menu-btn" id="ecMobileMenuToggle" aria-label="Open Navigation Menu">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>

                    <a href="index.php" class="ec-logo-wrap" aria-label="<?= e($company) ?> Home">
                        <?php if ($logoUrl): ?>
                            <img src="<?= e($logoUrl) ?>" alt="<?= e($company) ?>" class="ec-logo-img">
                        <?php else: ?>
                            <div>
                                <span class="ec-logo-text"><?= e($company) ?></span>
                                <span class="ec-logo-tag">Wellness Store</span>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Search Bar -->
                <form action="index.php" method="get" class="ec-search-bar" <?= $isHome ? 'onsubmit="event.preventDefault(); document.getElementById(\'catalog\')?.scrollIntoView({behavior:\'smooth\'});"' : '' ?>>
                    <input type="text" name="q" id="ecProductSearch" placeholder="Search Ayurvedic herbs, vitamins, supplements..." autocomplete="off">
                    <button type="submit" aria-label="Search">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </button>
                </form>

                <!-- Header Actions -->
                <div class="ec-actions">
                    <a href="<?= $isHome ? '#catalog' : 'index.php#catalog' ?>" class="ec-btn-offer" data-strip-deals="true" id="ecHeaderOfferBtn" title="Special Hot Deals - Flat 33% OFF">
                        <span class="ec-offer-icon">🔥</span>
                        <span class="ec-offer-text">Hot Deals</span>
                        <span class="ec-offer-badge">33% OFF</span>
                    </a>
                    
                    <button type="button" class="ec-btn-cart" data-open-cart aria-label="View Shopping Cart">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                        <span class="ec-action-text">Cart</span>
                        <span class="ec-cart-count" id="cartBadge">0</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Categories & Navigation Bar Strip -->
        <nav class="ec-nav-strip" aria-label="Categories Navigation">
            <div class="ec-shell">
                <div class="ec-nav-bar">
                    <!-- Categories Menu with Submenu Dropdown (Pinned on left) -->
                    <div class="ec-nav-item-dropdown" id="ecCatDropdown">
                        <button type="button" class="ec-nav-cat-btn" id="ecCatDropdownBtn" aria-haspopup="true" aria-expanded="false" title="Browse all categories">
                            <svg class="ec-icon-grid" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                                <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                            </svg>
                            <span class="ec-cat-btn-text">Categories</span>
                            <svg class="ec-icon-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>

                        <!-- Desktop Submenu Dropdown Panel -->
                        <div class="ec-cat-submenu" id="ecCatSubmenu" role="menu">
                            <div class="ec-cat-submenu-head">
                                <span class="ec-submenu-title">🌿 Health Categories</span>
                                <span class="ec-submenu-count"><?= count($categories) ?> Types</span>
                            </div>
                            <div class="ec-cat-submenu-list">
                                <a href="index.php#catalog" class="ec-submenu-link is-all <?= ($activeNav === 'home') ? 'is-active' : '' ?>" <?= $isHome ? 'data-strip-cat="all"' : '' ?>>
                                    <div class="ec-submenu-icon">🌿</div>
                                    <div class="ec-submenu-info">
                                        <strong>All Products</strong>
                                        <span>Explore complete herbal wellness store</span>
                                    </div>
                                    <span class="ec-submenu-arrow">→</span>
                                </a>

                                <?php foreach ($categories as $cat): 
                                    $cid = (int) $cat['id'];
                                    $cname = $cat['name'];
                                    $cdesc = $cat['description'] ?? '100% natural Ayurvedic formulations';
                                ?>
                                <a href="index.php#catalog" class="ec-submenu-link" <?= $isHome ? "data-strip-cat=\"$cid\"" : '' ?>>
                                    <div class="ec-submenu-icon">🌱</div>
                                    <div class="ec-submenu-info">
                                        <strong><?= e($cname) ?></strong>
                                        <?php if ($cdesc): ?>
                                            <span><?= e(mb_strimwidth($cdesc, 0, 42, '...')) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="ec-submenu-arrow">→</span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <div class="ec-cat-submenu-foot">
                                <a href="index.php#catalog" class="ec-submenu-foot-link" <?= $isHome ? 'data-strip-deals="true"' : '' ?>>
                                    <span>🔥 View Hot Deals &amp; Discounts</span>
                                    <span class="ec-nav-deal-tag">33% OFF</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Clean Main Navigation Links (No category clutter!) -->
                    <div class="ec-nav-links">
                        <?php if ($isHome): ?>
                            <button type="button" class="ec-nav-link is-active" data-strip-cat="all">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                <span>All Products</span>
                            </button>

                            <a href="#whyUs" class="ec-nav-link">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                <span>Why Us</span>
                            </a>

                            <a href="#reviews" class="ec-nav-link link-reviews">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <span>Reviews</span>
                            </a>

                            <a href="contact.php" class="ec-nav-link">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                <span>Contact Us</span>
                            </a>
                        <?php else: ?>
                            <a href="index.php" class="ec-nav-link <?= ($activeNav === 'home') ? 'is-active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                <span>Home</span>
                            </a>

                            <a href="index.php#catalog" class="ec-nav-link <?= ($activeNav === 'catalog') ? 'is-active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                <span>All Products</span>
                            </a>

                            <a href="index.php#whyUs" class="ec-nav-link">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                <span>Why Us</span>
                            </a>

                            <a href="index.php#reviews" class="ec-nav-link link-reviews">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <span>Reviews</span>
                            </a>

                            <a href="contact.php" class="ec-nav-link <?= ($activeNav === 'contact') ? 'is-active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                <span>Contact Us</span>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Right Side: Franchise Navigation & Portal Pill -->
                    <div class="ec-nav-right">
                        <a href="index.php#franchiseBanner" class="ec-nav-link link-franchise <?= ($activeNav === 'franchise') ? 'is-active' : '' ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <span>Franchise Program</span>
                        </a>

                        <a href="franchise/login.php" class="ec-nav-pill" title="Franchise Partner Portal Login">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <span>Franchise Portal</span>
                            <span class="ec-nav-pill-arrow">→</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
