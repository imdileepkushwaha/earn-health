<?php
require_once __DIR__ . '/config/database.php';

$company = setting('company_name', 'Earn Health');
$logoUrl = company_logo_url();
$favUrl = company_favicon_url();
$tagline = setting('company_tagline', '100% Pure Natural & Ayurvedic Wellness Store');
$formEnabled = setting('contact_form_enabled', '1') === '1';

$phone = setting('contact_phone', '+91 98765 43210');
$whatsappRaw = (string) setting('contact_whatsapp', '919876543210');
$whatsapp = preg_replace('/\D+/', '', $whatsappRaw) ?: '919876543210';
$email = setting('contact_email', setting('support_email', 'earnhealth.ceo@gmail.com'));
$address = setting('contact_address', 'Mouja Alamganj , Kaptanpara Ward No 41  Ps Sadar');
$city = setting('contact_city', '');
$state = setting('contact_state', '');
$country = setting('contact_country', 'India');
$pincode = setting('contact_pincode', '');
$hours = setting('contact_hours', 'Monday – Saturday: 9:30 AM to 6:30 PM (IST)');
$mapUrl = setting('contact_map_url', '');

$fullAddress = trim(implode(', ', array_filter([
    $address,
    $city,
    $state,
    $pincode,
    $country,
])));

$social = array_filter([
    'WhatsApp' => $whatsapp ? 'https://wa.me/' . $whatsapp : '',
    'Facebook' => setting('contact_facebook'),
    'Instagram' => setting('contact_instagram'),
    'Twitter' => setting('contact_twitter'),
    'YouTube' => setting('contact_youtube'),
    'Telegram' => setting('contact_telegram'),
]);

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $formEnabled) {
    $name = trim((string) ($_POST['name'] ?? ''));
    $emailInput = trim((string) ($_POST['email'] ?? ''));
    $phoneInput = trim((string) ($_POST['phone'] ?? ''));
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($name === '') {
        $errors[] = 'Please enter your full name.';
    }
    if ($emailInput === '' || !filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($message === '' || strlen($message) < 8) {
        $errors[] = 'Message must be at least 8 characters long.';
    }

    if (!$errors) {
        try {
            $pdo->prepare('INSERT INTO contact_inquiries (name, email, phone, subject, message, status, ip_address) VALUES (?,?,?,?,?,?,?)')
                ->execute([
                    $name,
                    $emailInput,
                    $phoneInput !== '' ? $phoneInput : null,
                    $subject !== '' ? $subject : 'Website Inquiry',
                    $message,
                    'new',
                    $_SERVER['REMOTE_ADDR'] ?? null,
                ]);
            $success = true;
        } catch (Throwable $e) {
            $errors[] = 'Unable to send message right now. Please connect directly via WhatsApp or phone.';
        }
    }
}
$pageTitle = 'Contact Us — ' . setting('company_name', 'Earn Health') . ' | Support &amp; Franchise Enquiries';
$metaDescription = 'Reach the ' . setting('company_name', 'Earn Health') . ' customer support and franchise desk. Call, WhatsApp, email, or send us a direct message for Ayurvedic product assistance and business opportunities.';
$activeNav = 'contact';
$extraCss = ['assets/css/contact.css'];

require_once __DIR__ . '/includes/site_header.php';
?>

    <!-- Contact Page Hero Section -->
    <section class="ec-contact-hero">
        <div class="ec-shell">
            <div class="ec-contact-hero-inner">
                <div class="ec-contact-breadcrumb">
                    <a href="index.php">Home</a>
                    <span>/</span>
                    <span style="color:#a7f3d0">Contact Us</span>
                </div>
                <div class="ec-contact-kicker">
                    <span>🌿 Dedicated Customer &amp; Partner Desk</span>
                </div>
                <h1>Get in Touch with <?= e($company) ?></h1>
                <p class="ec-contact-lead">
                    Have questions about our authentic Ayurvedic formulations, tracking an order, or partnering as an authorized franchise center? Our customer support and executive business team are here to help.
                </p>
            </div>
        </div>
    </section>

    <!-- Main Contact Section -->
    <main class="ec-contact-section">
        <div class="ec-shell">
            <div class="ec-contact-grid">
                
                <!-- Left Column: Contact Information Cards -->
                <div class="ec-contact-left">
                    <div class="ec-contact-card">
                        <h2 class="ec-contact-card-title">Direct Reach &amp; Office</h2>
                        <p class="ec-contact-card-sub">Reach our customer support team directly via WhatsApp, Phone, or Email for rapid assistance.</p>
                        
                        <div class="ec-contact-rows">
                            <!-- WhatsApp Support -->
                            <?php if ($whatsapp): ?>
                            <a href="https://wa.me/<?= e($whatsapp) ?>?text=Hello%2C+I+have+an+inquiry+regarding+Earn+Health+products" target="_blank" rel="noopener" class="ec-contact-row">
                                <div class="ec-contact-row-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                    </svg>
                                </div>
                                <div class="ec-contact-row-content">
                                    <span class="ec-contact-row-label">Instant WhatsApp Chat</span>
                                    <span class="ec-contact-row-value">Chat on WhatsApp →</span>
                                    <div class="ec-contact-row-desc">Quick answers for orders, payments &amp; delivery status</div>
                                </div>
                            </a>
                            <?php endif; ?>

                            <!-- Phone Support -->
                            <?php if ($phone): ?>
                            <a href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>" class="ec-contact-row">
                                <div class="ec-contact-row-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                </div>
                                <div class="ec-contact-row-content">
                                    <span class="ec-contact-row-label">Call Support Desk</span>
                                    <span class="ec-contact-row-value"><?= e($phone) ?></span>
                                    <div class="ec-contact-row-desc"><?= e($hours) ?></div>
                                </div>
                            </a>
                            <?php endif; ?>

                            <!-- Email Support -->
                            <?php if ($email): ?>
                            <a href="mailto:<?= e($email) ?>" class="ec-contact-row">
                                <div class="ec-contact-row-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                </div>
                                <div class="ec-contact-row-content">
                                    <span class="ec-contact-row-label">Official Email</span>
                                    <span class="ec-contact-row-value"><?= e($email) ?></span>
                                    <div class="ec-contact-row-desc">Expect a prompt response within 24 hours</div>
                                </div>
                            </a>
                            <?php endif; ?>

                            <!-- Registered Office Address -->
                            <?php if ($fullAddress): ?>
                            <div class="ec-contact-row" style="background:#fff;border-color:var(--border)">
                                <div class="ec-contact-row-icon" style="background:#ecfdf5;color:#059669">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                </div>
                                <div class="ec-contact-row-content">
                                    <span class="ec-contact-row-label">Registered Office &amp; Hub</span>
                                    <span class="ec-contact-row-value"><?= e($fullAddress) ?></span>
                                    <?php if ($mapUrl): ?>
                                    <a href="<?= e($mapUrl) ?>" target="_blank" rel="noopener" class="ec-contact-map-btn">
                                        <span>View on Google Maps →</span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Social Channels -->
                        <?php if ($social): ?>
                        <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border)">
                            <span style="font-size:0.75rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;display:block;margin-bottom:0.6rem">Connect on Social</span>
                            <div class="ec-contact-social">
                                <?php foreach ($social as $name => $url): 
                                    if (!$url) continue;
                                ?>
                                <a href="<?= e($url) ?>" target="_blank" rel="noopener">
                                    <span><?= e($name) ?></span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Franchise Opportunity Promo Card -->
                    <div class="ec-franchise-promo">
                        <span class="ec-franchise-promo-tag">★ Franchise Opportunity</span>
                        <h4>Start an Earn Health Franchise Center</h4>
                        <p>Join India's fastest expanding network of certified Ayurvedic wellness distribution points. Enjoy high retail margins, zero stock risk, and dedicated marketing support.</p>
                        <a href="https://wa.me/<?= e($whatsapp) ?>?text=Hello%2C+I+want+to+apply+for+an+Earn+Health+Franchise+in+my+city." target="_blank" rel="noopener" class="ec-franchise-promo-btn">
                            <span>Inquire for Franchise →</span>
                        </a>
                    </div>
                </div>

                <!-- Right Column: Contact Inquiry Form -->
                <div class="ec-contact-form-card">
                    <div class="ec-contact-form-header">
                        <h2>Send us a Message</h2>
                        <p>Have an inquiry or bulk wellness order requirement? Leave your details below and our customer executive will get back to you promptly.</p>
                    </div>

                    <?php if (!$formEnabled): ?>
                        <div class="ec-contact-alert ec-contact-alert-ok">
                            <span>ℹ️ The online message form is currently undergoing scheduled maintenance. Please connect directly via WhatsApp (<strong>+91 <?= e($whatsapp) ?></strong>) or Phone.</span>
                        </div>
                    <?php elseif ($success): ?>
                        <div class="ec-contact-alert ec-contact-alert-ok">
                            <div style="font-size:1.4rem">✅</div>
                            <div>
                                <strong style="display:block;margin-bottom:0.25rem;font-size:1.05rem">Thank you! Your message has been sent successfully.</strong>
                                <span>Our customer support team has received your inquiry and will reach out to you within 24 business hours.</span>
                            </div>
                        </div>
                        <div style="text-align:center;padding:1.5rem 0">
                            <a href="contact.php" class="ec-btn-submit" style="display:inline-flex;width:auto;padding:0.75rem 1.75rem">Send Another Message</a>
                        </div>
                    <?php else: ?>
                        <?php if ($errors): ?>
                        <div class="ec-contact-alert ec-contact-alert-err">
                            <div style="font-size:1.3rem">⚠️</div>
                            <div>
                                <strong style="display:block;margin-bottom:0.25rem">Please fix the following:</strong>
                                <ul style="margin:0;padding-left:1.2rem">
                                    <?php foreach ($errors as $err): ?>
                                    <li><?= e($err) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>

                        <form method="post" action="contact.php">
                            <div class="ec-form-grid">
                                <!-- Name -->
                                <div class="ec-form-group">
                                    <label class="ec-form-label" for="contactName">Your Full Name <span class="req">*</span></label>
                                    <input type="text" id="contactName" name="name" class="ec-form-input" placeholder="e.g. Ramesh Sharma" value="<?= e($_POST['name'] ?? '') ?>" required>
                                </div>

                                <!-- Email -->
                                <div class="ec-form-group">
                                    <label class="ec-form-label" for="contactEmail">Email Address <span class="req">*</span></label>
                                    <input type="email" id="contactEmail" name="email" class="ec-form-input" placeholder="e.g. ramesh@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                                </div>

                                <!-- Phone -->
                                <div class="ec-form-group">
                                    <label class="ec-form-label" for="contactPhone">Phone / WhatsApp Number</label>
                                    <input type="tel" id="contactPhone" name="phone" class="ec-form-input" placeholder="e.g. +91 98765 43210" value="<?= e($_POST['phone'] ?? '') ?>">
                                </div>

                                <!-- Subject -->
                                <div class="ec-form-group">
                                    <label class="ec-form-label" for="contactSubject">Inquiry Topic</label>
                                    <select id="contactSubject" name="subject" class="ec-form-select">
                                        <option value="Product & Dosage Inquiry" <?= (($_POST['subject'] ?? '') === 'Product & Dosage Inquiry') ? 'selected' : '' ?>>Product &amp; Dosage Inquiry</option>
                                        <option value="Franchise Center Application" <?= (($_POST['subject'] ?? '') === 'Franchise Center Application') ? 'selected' : '' ?>>Franchise Center Application</option>
                                        <option value="Order Status & Delivery" <?= (($_POST['subject'] ?? '') === 'Order Status & Delivery') ? 'selected' : '' ?>>Order Status &amp; Delivery</option>
                                        <option value="Bulk / Wholesale Distribution" <?= (($_POST['subject'] ?? '') === 'Bulk / Wholesale Distribution') ? 'selected' : '' ?>>Bulk / Wholesale Distribution</option>
                                        <option value="Feedback / Other" <?= (($_POST['subject'] ?? '') === 'Feedback / Other') ? 'selected' : '' ?>>Feedback / Other</option>
                                    </select>
                                </div>

                                <!-- Message -->
                                <div class="ec-form-group span-2">
                                    <label class="ec-form-label" for="contactMessage">Your Message <span class="req">*</span></label>
                                    <textarea id="contactMessage" name="message" class="ec-form-textarea" rows="5" placeholder="Please describe your query or requirement in detail..." required><?= e($_POST['message'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <button type="submit" class="ec-btn-submit">
                                <span>Send Message</span>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <!-- Trust Badges Strip -->
    <section class="ec-contact-features">
        <div class="ec-shell">
            <div class="ec-contact-features-grid">
                <div class="ec-contact-feat-item">
                    <div class="ec-contact-feat-icon">🌿</div>
                    <div class="ec-contact-feat-text">
                        <strong>100% Ayurvedic</strong>
                        <span>Pure organic herbal extracts</span>
                    </div>
                </div>
                <div class="ec-contact-feat-item">
                    <div class="ec-contact-feat-icon">⚡</div>
                    <div class="ec-contact-feat-text">
                        <strong>Fast Delivery</strong>
                        <span>Secure PAN-India shipping</span>
                    </div>
                </div>
                <div class="ec-contact-feat-item">
                    <div class="ec-contact-feat-icon">🏢</div>
                    <div class="ec-contact-feat-text">
                        <strong>Franchise Network</strong>
                        <span>Authorized wellness hubs</span>
                    </div>
                </div>
                <div class="ec-contact-feat-item">
                    <div class="ec-contact-feat-icon">🛡️</div>
                    <div class="ec-contact-feat-text">
                        <strong>GMP Certified</strong>
                        <span>Standardized lab testing</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>

