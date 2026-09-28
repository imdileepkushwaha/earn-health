/**
 * Earn Health E-Commerce Interactive Script
 * - Category & Search filtering
 * - Cart state management with localStorage
 * - Cart slide drawer
 * - WhatsApp direct ordering
 */

(function () {
    'use strict';

    // State
    const CART_KEY = 'earnhealth_cart_v1';
    let cart = [];

    try {
        cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];
    } catch (e) {
        cart = [];
    }

    function saveCart() {
        try {
            localStorage.setItem(CART_KEY, JSON.stringify(cart));
        } catch (e) {}
        renderCartUI();
    }

    // Toast message
    function showToast(msg) {
        let toast = document.getElementById('ecToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'ecToast';
            toast.className = 'ec-toast';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<span>✓</span> <span>${msg}</span>`;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2500);
    }

    // Add to cart function
    window.addToCart = function (id, name, price, img, mrp) {
        const existing = cart.find(item => item.id === id);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({
                id: id,
                name: name,
                price: parseFloat(price),
                mrp: parseFloat(mrp || price),
                img: img,
                qty: 1
            });
        }
        saveCart();
        showToast(`Added "${name}" to your cart!`);
    };

    window.updateQty = function (id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;
        item.qty += delta;
        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        }
        saveCart();
    };

    window.removeFromCart = function (id) {
        cart = cart.filter(i => i.id !== id);
        saveCart();
    };

    // Render Cart Drawer
    function renderCartUI() {
        const badge = document.getElementById('cartBadge');
        const countElements = document.querySelectorAll('.ec-cart-count');
        const totalCount = cart.reduce((sum, i) => sum + i.qty, 0);

        countElements.forEach(el => el.textContent = totalCount);
        if (badge) badge.textContent = totalCount;

        const container = document.getElementById('cartItemsList');
        const subtotalEl = document.getElementById('cartSubtotal');
        if (!container) return;

        if (cart.length === 0) {
            container.innerHTML = `
                <div style="text-align:center;padding:3rem 1rem;color:var(--muted)">
                    <div style="font-size:2.5rem;margin-bottom:0.5rem">🛒</div>
                    <strong style="display:block;color:var(--dark);font-size:1.05rem">Your cart is empty</strong>
                    <p style="font-size:0.85rem;margin-top:0.25rem">Add Ayurvedic wellness products to begin.</p>
                </div>
            `;
            if (subtotalEl) subtotalEl.textContent = '₹0.00';
            return;
        }

        let subtotal = 0;
        let html = '';

        cart.forEach(item => {
            const itemTotal = item.price * item.qty;
            subtotal += itemTotal;
            html += `
                <div class="ec-cart-item">
                    <img src="${item.img || 'assets/img/product-ph.png'}" alt="${item.name}" class="ec-cart-item-img" onerror="this.src='uploads/branding/logo_20260928160803_68a005.png'">
                    <div class="ec-cart-item-info">
                        <div class="ec-cart-item-name" title="${item.name}">${item.name}</div>
                        <div class="ec-cart-item-price">₹${item.price.toFixed(2)}</div>
                    </div>
                    <div class="ec-cart-qty-ctrl">
                        <button type="button" onclick="updateQty(${item.id}, -1)">−</button>
                        <span style="font-size:0.85rem;font-weight:700;min-width:18px;text-align:center">${item.qty}</span>
                        <button type="button" onclick="updateQty(${item.id}, 1)">+</button>
                    </div>
                    <button type="button" class="ec-cart-item-del" onclick="removeFromCart(${item.id})" title="Remove item">×</button>
                </div>
            `;
        });

        container.innerHTML = html;
        if (subtotalEl) subtotalEl.textContent = '₹' + subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2 });
    }

    // Open / Close Drawer
    const drawer = document.getElementById('ecCartDrawer');
    const openBtns = document.querySelectorAll('[data-open-cart]');
    const closeBtns = document.querySelectorAll('[data-close-cart]');

    openBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (drawer) drawer.classList.add('is-open');
        });
    });

    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (drawer) drawer.classList.remove('is-open');
        });
    });

    // WhatsApp Checkout builder
    const checkoutBtn = document.getElementById('btnWhatsappCheckout');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            if (cart.length === 0) {
                alert('Your cart is empty! Add products first.');
                return;
            }

            const phone = checkoutBtn.getAttribute('data-phone') || '919876543210';
            let msg = `*New Order Enquiry - Earn Health Store*\n\n`;
            let total = 0;

            cart.forEach((i, idx) => {
                const lineTotal = i.price * i.qty;
                total += lineTotal;
                msg += `${idx + 1}. *${i.name}* x ${i.qty} = ₹${lineTotal}\n`;
            });

            msg += `\n*Total Amount:* ₹${total}\n\n`;
            msg += `Please confirm availability and share payment / delivery instructions.\n`;
            msg += `Customer Name: \nDelivery Address: \nCity & Pincode: `;

            const url = `https://wa.me/${phone}?text=${encodeURIComponent(msg)}`;
            window.open(url, '_blank');
        });
    }

    // Category Filter Function
    function filterCategory(catId, scrollToCatalog = false) {
        // Sync chips
        document.querySelectorAll('.ec-cat-chip').forEach(c => {
            if (c.getAttribute('data-cat-id') === String(catId)) {
                c.classList.add('is-active');
            } else {
                c.classList.remove('is-active');
            }
        });

        // Sync header strip pills
        document.querySelectorAll('[data-strip-cat]').forEach(p => {
            if (p.getAttribute('data-strip-cat') === String(catId)) {
                p.classList.add('is-active');
            } else {
                p.classList.remove('is-active');
            }
        });

        // Filter cards
        const cards = document.querySelectorAll('.ec-card');
        cards.forEach(card => {
            const cardCat = card.getAttribute('data-category-id');
            if (catId === 'all' || cardCat === String(catId)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });

        if (scrollToCatalog) {
            const catalog = document.getElementById('catalog');
            if (catalog) {
                catalog.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    // Category Filter Chips
    const catChips = document.querySelectorAll('.ec-cat-chip');
    catChips.forEach(chip => {
        chip.addEventListener('click', () => {
            const catId = chip.getAttribute('data-cat-id');
            filterCategory(catId, false);
        });
    });

    // Header Strip Category Pills
    const stripPills = document.querySelectorAll('[data-strip-cat]');
    stripPills.forEach(pill => {
        pill.addEventListener('click', (e) => {
            e.preventDefault();
            const catId = pill.getAttribute('data-strip-cat');
            filterCategory(catId, true);
        });
    });

    // Deals pill
    const dealsBtn = document.querySelector('[data-strip-deals]');
    if (dealsBtn) {
        dealsBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const cards = document.querySelectorAll('.ec-card');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes('off') || text.includes('bestseller') || text.includes('save') || text.includes('hot')) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
            const catalog = document.getElementById('catalog');
            if (catalog) {
                catalog.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    // Search bar filter
    const searchInput = document.getElementById('ecProductSearch');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const val = e.target.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.ec-card');
            cards.forEach(card => {
                const title = (card.getAttribute('data-name') || '').toLowerCase();
                const desc = (card.getAttribute('data-desc') || '').toLowerCase();
                if (val === '' || title.includes(val) || desc.includes(val)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Init UI
    renderCartUI();
})();
