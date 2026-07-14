/**
 * IIMS v2.0 - POS Billing Controller
 * Features: Hold/Recall, Split Payment, Coupons, Loyalty, GST (CGST/SGST/IGST)
 */
$(document).ready(function () {
    let cart = [];
    let appliedCoupon = null;
    let lastInvoiceId = null;
    let customerData = {};
    const csrfToken = $('input[name="csrf_token"]').val();
    const loyaltyEnabled = parseInt($('#config-loyalty-enabled').val()) || 0;
    const loyaltyPer100 = parseInt($('#config-loyalty-per-100').val()) || 1;
    const loyaltyRedeemValue = parseFloat($('#config-loyalty-redeem-value').val()) || 1.0;
    const companyState = $('#config-company-state').val() || '';

    loadCustomers();
    loadHeldBillsCount();


    // ==================== PRODUCT SEARCH (Add Product) ====================
    // A small purpose-built typeahead instead of Select2: Select2 opens its
    // dropdown synchronously on mousedown, and when this row sits low on a
    // mobile screen it flips to render *above* the trigger, right under the
    // finger that's still down - the matching touchend/mouseup then lands on
    // a result row instead of the trigger and Select2 immediately closes
    // itself again. Owning the whole interaction here avoids that class of
    // bug entirely and lets the same code work identically on touch and
    // mouse, plus keeps results open after each add for fast repeat scanning.
    let searchResults = [];
    let activeResultIndex = -1;
    let searchDebounce = null;
    let searchRequestSeq = 0;

    const $search = $('#cart-product-search');
    const $searchResults = $('#product-search-results');
    const $searchClear = $('#cart-product-search-clear');

    function escapeHtml(str) {
        return $('<div>').text(str == null ? '' : str).html();
    }

    function renderSearchResults(products) {
        searchResults = products;
        activeResultIndex = -1;
        if (!products.length) {
            $searchResults.html('<div class="product-result-empty">No products found</div>');
        } else {
            $searchResults.html(products.map(function (p, idx) {
                const stock = parseFloat(p.current_stock);
                let stockTxt = stock + ' ' + (p.unit_name || 'Pcs');
                if (p.secondary_unit_name && p.conversion_factor) {
                    stockTxt += ' (' + parseFloat((stock * parseFloat(p.conversion_factor)).toFixed(2)) + ' ' + p.secondary_unit_name + ')';
                }
                const badge = stock > 0
                    ? '<span class="badge bg-light-success float-end">' + stockTxt + '</span>'
                    : '<span class="badge bg-light-danger float-end">Out of stock</span>';
                return '<div class="product-result-item" data-idx="' + idx + '">' +
                    '<strong>' + escapeHtml(p.product_name) + '</strong> <span class="text-muted small">(' + escapeHtml(p.sku) + ')</span>' + badge +
                    '<div class="small text-indigo">₹' + parseFloat(p.selling_price).toFixed(2) + ' | GST: ' + parseFloat(p.gst_percentage) + '%</div>' +
                    '</div>';
            }).join(''));
        }
        $searchResults.addClass('show');
    }

    function hideSearchResults() {
        $searchResults.removeClass('show');
    }

    function fetchAndShowResults(term) {
        // Guard against out-of-order responses: e.g. selecting a result kicks
        // off a "refresh with all products" request, and if the user is
        // already typing the next search, that request can resolve *after*
        // the newer filtered one and silently clobber it with stale results.
        // Only the response to the most recently *sent* request is ever
        // rendered, no matter what order they come back in.
        term = (term || '').trim();
        if (!term) {
            hideSearchResults();
            return;
        }
        const seq = ++searchRequestSeq;
        $.getJSON(BASE_URL + '/api/billing.php', { action: 'search_product', q: term }, function (res) {
            if (seq !== searchRequestSeq) return;
            renderSearchResults(res.status ? res.data : []);
        });
    }

    function setActiveResult(idx) {
        activeResultIndex = idx;
        $searchResults.find('.product-result-item').removeClass('active');
        if (idx >= 0) {
            const $item = $searchResults.find('.product-result-item').eq(idx).addClass('active');
            if ($item.length) $item[0].scrollIntoView({ block: 'nearest' });
        }
    }

    function selectSearchResult(product) {
        addToCart(product);
        playBeep();
        $search.val('').focus();
        $searchClear.addClass('d-none');
        fetchAndShowResults('');
    }

    $search.on('focus', function () {
        fetchAndShowResults($(this).val());
    });

    $search.on('input', function () {
        const term = $(this).val();
        $searchClear.toggleClass('d-none', !term);
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(function () { fetchAndShowResults(term); }, 200);
    });

    $search.on('keydown', function (e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (searchResults.length) setActiveResult((activeResultIndex + 1) % searchResults.length);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (searchResults.length) setActiveResult((activeResultIndex - 1 + searchResults.length) % searchResults.length);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeResultIndex >= 0 && searchResults[activeResultIndex]) {
                selectSearchResult(searchResults[activeResultIndex]);
            } else if (searchResults.length === 1) {
                selectSearchResult(searchResults[0]);
            }
        } else if (e.key === 'Escape') {
            hideSearchResults();
        }
    });

    $searchResults.on('click', '.product-result-item', function () {
        const idx = parseInt($(this).data('idx'), 10);
        if (searchResults[idx]) selectSearchResult(searchResults[idx]);
    });

    $searchClear.on('click', function () {
        $search.val('').focus();
        $(this).addClass('d-none');
        fetchAndShowResults('');
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#product-search').length) hideSearchResults();
    });

    // ==================== POS SCANNER MODE ====================
    const posMode = parseInt($('#config-pos-mode').val()) || 0;
    if (posMode === 1 && $('#barcode-scanner').length) {
        const $scanner = $('#barcode-scanner');
        
        // Listen for Enter key
        $scanner.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                const barcode = $(this).val().trim();
                if (!barcode) return;
                
                // Fetch product by exact barcode/sku
                $.getJSON(BASE_URL + '/api/billing.php?action=scan_product&barcode=' + encodeURIComponent(barcode), function(res) {
                    if (res.status && res.data) {
                        addToCart(res.data);
                        $scanner.val('');
                        playBeep();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Not Found', text: 'No product matches this barcode.', timer: 1500, showConfirmButton: false, background: '#151e30', color: '#f3f4f6' });
                        $scanner.val('');
                    }
                    $scanner.focus();
                });
            }
        });

        // Keep focus on the scanner whenever clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('input, select, button, a, textarea').length) {
                $scanner.focus();
            }
        });
    }

    // ==================== CART ====================
    function addToCart(product) {
        const existing = cart.find(i => i.id === product.id);
        if (existing) {
            if (existing.is_secondary_unit) {
                const primaryQty = (existing.qty + 1) / existing.conversion_factor;
                if (primaryQty > parseFloat(product.current_stock)) { showStockWarning(product.product_name); return; }
            } else {
                if (existing.qty + 1 > parseFloat(product.current_stock)) { showStockWarning(product.product_name); return; }
            }
            existing.qty += 1;
        } else {
            if (parseFloat(product.current_stock) < 1) { showStockWarning(product.product_name); return; }
            cart.push({
                id: product.id, product_name: product.product_name, sku: product.sku,
                hsn_code: product.hsn_code || '', rate: parseFloat(product.selling_price),
                gst_percentage: parseFloat(product.gst_percentage), qty: 1,
                discount: 0, discount_val: 0, discount_type: 'pct',
                max_stock: parseFloat(product.current_stock),
                unit_name: product.unit_name || 'PCS',
                unit_id: product.unit_id || null,
                secondary_unit_name: product.secondary_unit_name || null,
                secondary_unit_id: product.secondary_unit_id || null,
                conversion_factor: product.conversion_factor ? parseFloat(product.conversion_factor) : null,
                billing_unit_id: product.unit_id || null,
                billing_unit_name: product.unit_name || 'PCS',
                is_secondary_unit: 0,
                original_rate: parseFloat(product.selling_price)
            });
        }
        renderCart();
        playBeep();
    }

    function showStockWarning(name) {
        Swal.fire({ icon: 'warning', title: 'Stock Limit', text: 'Cannot add more ' + name + '.', background: '#151e30', color: '#f3f4f6' });
    }

    function renderCart() {
        const body = $('#pos-cart-table tbody');
        body.find('tr:not(.cart-add-row):not(.cart-empty-row)').remove();
        if (cart.length === 0) { $('.cart-empty-row').show(); recalculateBill(); return; }
        $('.cart-empty-row').hide();

        cart.forEach(function (item, idx) {
            const base = item.qty * item.rate;
            const disc = item.discount_type === 'flat' ? item.discount_val : base * (item.discount_val / 100);
            item.discount = disc;
            const taxable = Math.max(0, base - disc);
            const tax = taxable * (item.gst_percentage / 100);
            const total = taxable + tax;

            let unitCell;
            if (item.secondary_unit_name && item.conversion_factor) {
                unitCell = '<td><select class="form-select form-select-sm py-1 cart-unit-select" style="width:90px;">' +
                    '<option value="primary"' + (item.is_secondary_unit === 0 ? ' selected' : '') + '>' + (item.unit_name || 'PCS') + '</option>' +
                    '<option value="secondary"' + (item.is_secondary_unit === 1 ? ' selected' : '') + '>' + item.secondary_unit_name + '</option>' +
                    '</select>';
                if (item.is_secondary_unit === 0) {
                    const secQty = parseFloat((item.qty * item.conversion_factor).toFixed(2));
                    unitCell += '<div class="text-muted small mt-1">= ' + secQty + ' ' + item.secondary_unit_name + '</div>';
                } else {
                    const priQty = parseFloat((item.qty / item.conversion_factor).toFixed(4));
                    unitCell += '<div class="text-muted small mt-1">= ' + priQty + ' ' + (item.unit_name || 'PCS') + '</div>';
                }
                unitCell += '</td>';
            } else {
                unitCell = '<td class="text-muted small">' + (item.billing_unit_name || item.unit_name || 'PCS') + '</td>';
            }

            const maxStock = item.is_secondary_unit ? item.max_stock * item.conversion_factor : item.max_stock;

            $('.cart-add-row').before(
                '<tr data-index="' + idx + '">' +
                '<td class="text-secondary">' + (idx + 1) + '</td>' +
                '<td><strong>' + item.product_name + '</strong><div class="text-muted small">Code: ' + item.sku + '</div></td>' +
                '<td class="small">' + (item.hsn_code || '-') + '</td>' +
                '<td><input type="number" step="1" min="0" max="' + maxStock + '" class="form-control form-control-sm py-1 text-center cart-qty" value="' + item.qty + '" style="width:70px;"></td>' +
                unitCell +
                '<td><input type="number" step="1" min="0" class="form-control form-control-sm py-1 cart-rate" value="' + item.rate.toFixed(2) + '"></td>' +
                '<td>' +
                '<div class="d-flex align-items-center gap-1">' +
                '<input type="number" step="1" min="0" class="form-control form-control-sm py-1 cart-discount-val" value="' + item.discount_val + '" style="width:55px;">' +
                '<select class="form-select form-select-sm py-1 cart-discount-type" style="width:62px;font-size:0.8rem;">' +
                '<option value="pct"' + (item.discount_type === 'pct' ? ' selected' : '') + '>%</option>' +
                '<option value="flat"' + (item.discount_type === 'flat' ? ' selected' : '') + '>₹</option></select>' +
                '</div>' +
                (disc > 0 ? '<div class="text-emerald small">₹' + disc.toFixed(2) + ' off</div>' : '') +
                '</td>' +
                '<td class="text-center small">' + item.gst_percentage + '%</td>' +
                '<td class="text-end fw-bold">₹' + total.toFixed(2) + '</td>' +
                '<td class="text-center"><button class="btn btn-sm text-danger btn-remove-item" title="Remove"><i class="fa-solid fa-trash-can"></i></button></td>' +
                '</tr>'
            );
        });
        recalculateBill();
    }

    $('#pos-cart-table').on('change', '.cart-qty', function () {
        const idx = $(this).closest('tr').data('index');
        let v = parseFloat($(this).val());
        if (isNaN(v) || v < 0.01) v = 0.01;
        const item = cart[idx];
        const effectiveMax = item.is_secondary_unit ? item.max_stock * item.conversion_factor : item.max_stock;
        if (v > effectiveMax) { v = effectiveMax; $(this).val(v); }
        item.qty = v;
        renderCart();
    });

    $('#pos-cart-table').on('change', '.cart-rate', function () {
        const idx = $(this).closest('tr').data('index');
        let v = parseFloat($(this).val());
        if (isNaN(v) || v < 0) v = 0;
        cart[idx].rate = v;
        renderCart();
    });

    $('#pos-cart-table').on('change', '.cart-discount-val', function () {
        const idx = $(this).closest('tr').data('index');
        let v = parseFloat($(this).val());
        if (isNaN(v) || v < 0) v = 0;
        if (cart[idx].discount_type === 'pct' && v > 100) v = 100;
        cart[idx].discount_val = v;
        renderCart();
    });

    $('#pos-cart-table').on('change', '.cart-discount-type', function () {
        const idx = $(this).closest('tr').data('index');
        cart[idx].discount_type = $(this).val();
        renderCart();
    });

    $('#pos-cart-table').on('click', '.btn-remove-item', function () {
        cart.splice($(this).closest('tr').data('index'), 1);
        renderCart();
    });

    $('#pos-cart-table').on('change', '.cart-unit-select', function () {
        const idx = $(this).closest('tr').data('index');
        const selectedValue = $(this).val();
        const item = cart[idx];

        if (selectedValue === 'secondary' && item.is_secondary_unit === 0) {
            item.rate = parseFloat((item.rate / item.conversion_factor).toFixed(2));
            item.is_secondary_unit = 1;
            item.billing_unit_id = item.secondary_unit_id;
            item.billing_unit_name = item.secondary_unit_name;
        } else if (selectedValue === 'primary' && item.is_secondary_unit === 1) {
            item.rate = parseFloat((item.rate * item.conversion_factor).toFixed(2));
            item.is_secondary_unit = 0;
            item.billing_unit_id = item.unit_id;
            item.billing_unit_name = item.unit_name;
        }
        renderCart();
    });

    // ==================== BILL CALCULATION ====================
    $('#bill-discount-input').on('input', recalculateBill);
    $('#redeem-points-input').on('input', recalculateBill);

    function isInterState() {
        const custId = $('#pos-customer-select').val();
        if (!custId || !companyState) return false;
        const cust = customerData[custId];
        if (!cust || !cust.state) return false;
        return cust.state.toLowerCase().trim() !== companyState.toLowerCase().trim();
    }

    function recalculateBill() {
        let subtotal = 0, totalCgst = 0, totalSgst = 0, totalIgst = 0, totalTax = 0;
        const igst = isInterState();

        cart.forEach(function (item) {
            const base = item.qty * item.rate;
            const disc = item.discount_type === 'flat' ? item.discount_val : base * (item.discount_val / 100);
            const taxable = Math.max(0, base - disc);
            const tax = taxable * (item.gst_percentage / 100);
            subtotal += taxable;
            totalTax += tax;
            if (igst) { totalIgst += tax; }
            else { totalCgst += tax / 2; totalSgst += tax / 2; }
        });

        const flatDiscount = parseFloat($('#bill-discount-input').val()) || 0;
        const couponDiscount = appliedCoupon ? appliedCoupon.discount_amount : 0;

        let loyaltyDiscount = 0;
        if (loyaltyEnabled && $('#redeem-loyalty-toggle').is(':checked')) {
            const pts = parseInt($('#redeem-points-input').val()) || 0;
            loyaltyDiscount = pts * loyaltyRedeemValue;
            $('#redeem-discount-display').text('= ₹' + loyaltyDiscount.toFixed(2));
            $('#loyalty-discount-row').removeClass('d-none');
            $('#bill-loyalty-discount').text('-₹' + loyaltyDiscount.toFixed(2));
        } else {
            $('#loyalty-discount-row').addClass('d-none');
        }

        const grandTotal = subtotal + totalTax - flatDiscount - couponDiscount - loyaltyDiscount;
        const roundedTotal = Math.round(grandTotal);
        const roundoff = roundedTotal - grandTotal;

        $('#bill-subtotal').text('₹' + subtotal.toFixed(2));
        $('#bill-cgst').text('₹' + totalCgst.toFixed(2));
        $('#bill-sgst').text('₹' + totalSgst.toFixed(2));
        $('#bill-igst').text('₹' + totalIgst.toFixed(2));
        $('#bill-tax').text('₹' + totalTax.toFixed(2));
        $('#bill-roundoff').text((roundoff >= 0 ? '+' : '') + '₹' + roundoff.toFixed(2));
        $('#bill-grand-total').text('₹' + roundedTotal.toFixed(2));

        if (igst) { $('#igst-row').removeClass('d-none'); $('#bill-cgst').parent().addClass('d-none'); $('#bill-sgst').parent().addClass('d-none'); }
        else { $('#igst-row').addClass('d-none'); $('#bill-cgst').parent().removeClass('d-none'); $('#bill-sgst').parent().removeClass('d-none'); }

        updatePaymentSummary();
    }

    // ==================== SPLIT PAYMENT ====================
    let paymentIndex = 1;

    $('#btn-add-split').click(function () {
        const html = '<div class="payment-row d-flex gap-2 mb-2" data-index="' + paymentIndex + '">' +
            '<select class="form-select form-select-sm pay-method" style="width: 50%;">' +
            '<option value="CASH">CASH</option><option value="UPI">UPI</option><option value="CARD">CARD</option>' +
            '<option value="NET_BANKING">NET BANKING</option><option value="CREDIT">CREDIT</option></select>' +
            '<input type="number" step="0.01" min="0" class="form-control form-control-sm pay-amount" placeholder="Amount" value="0.00">' +
            '<button class="btn btn-sm btn-outline-danger py-0 px-1 btn-remove-split"><i class="fa-solid fa-xmark"></i></button></div>';
        $('#payment-methods-container').append(html);
        paymentIndex++;
        autoFillLastPayment();
    });

    $(document).on('click', '.btn-remove-split', function () {
        $(this).closest('.payment-row').remove();
        updatePaymentSummary();
    });

    $(document).on('input', '.pay-amount', updatePaymentSummary);
    $(document).on('change', '.pay-method', function () {
        const method = $(this).val();
        if (method === 'CREDIT') {
            $('#due-date-row').removeClass('d-none');
        }
        updatePaymentSummary();
    });

    function autoFillLastPayment() {
        const total = parseFloat($('#bill-grand-total').text().replace('₹', '').replace(',', '')) || 0;
        let paid = 0;
        $('.payment-row').each(function (i) {
            if (i < $('.payment-row').length - 1) paid += parseFloat($(this).find('.pay-amount').val()) || 0;
        });
        const remaining = Math.max(0, total - paid);
        $('.payment-row:last .pay-amount').val(remaining.toFixed(2));
        updatePaymentSummary();
    }

    function updatePaymentSummary() {
        const total = parseFloat($('#bill-grand-total').text().replace('₹', '').replace(',', '')) || 0;
        let paid = 0;
        let hasCredit = false;
        $('.payment-row').each(function () {
            paid += parseFloat($(this).find('.pay-amount').val()) || 0;
            if ($(this).find('.pay-method').val() === 'CREDIT') hasCredit = true;
        });
        const diff = paid - total;
        $('#total-paid-display').text('₹' + paid.toFixed(2));
        if (diff >= 0) {
            $('#change-balance-display').text('₹' + diff.toFixed(2) + ' change').removeClass('text-rose').addClass('text-emerald');
        } else {
            $('#change-balance-display').text('₹' + Math.abs(diff).toFixed(2) + ' due').removeClass('text-emerald').addClass('text-rose');
        }
        if (hasCredit) $('#due-date-row').removeClass('d-none');
        else $('#due-date-row').addClass('d-none');
    }

    function getPayments() {
        const payments = [];
        $('.payment-row').each(function () {
            const method = $(this).find('.pay-method').val();
            const amount = parseFloat($(this).find('.pay-amount').val()) || 0;
            if (amount > 0) payments.push({ method: method, amount: amount });
        });
        return payments;
    }

    // ==================== COUPON ====================
    $('#btn-apply-coupon').click(function () {
        const code = $('#coupon-code-input').val().trim();
        if (!code) return;
        const subtotal = parseFloat($('#bill-subtotal').text().replace('₹', '')) || 0;
        $.post(BASE_URL + '/api/coupons.php?action=validate', { csrf_token: csrfToken, coupon_code: code, order_amount: subtotal }, function (res) {
            if (res.status) {
                appliedCoupon = res.data;
                $('#coupon-label').text(res.data.coupon_name + ' (' + code + ')');
                $('#bill-coupon-discount').text('-₹' + parseFloat(res.data.discount_amount).toFixed(2));
                $('#coupon-discount-row').removeClass('d-none');
                $('#coupon-code-input').prop('disabled', true);
                recalculateBill();
            } else {
                Swal.fire({ icon: 'error', title: 'Invalid Coupon', text: res.message, background: '#151e30', color: '#f3f4f6' });
            }
        }, 'json');
    });

    $('#btn-remove-coupon').click(function () {
        appliedCoupon = null;
        $('#coupon-discount-row').addClass('d-none');
        $('#coupon-code-input').val('').prop('disabled', false);
        recalculateBill();
    });

    // ==================== LOYALTY ====================
    $('#pos-customer-select').on('change', function () {
        const id = $(this).val();
        if (loyaltyEnabled && id && customerData[id]) {
            const pts = customerData[id].loyalty_points || 0;
            $('#customer-loyalty-points').text(pts);
            $('#redeem-points-input').attr('max', pts).val(0);
            $('#loyalty-panel').removeClass('d-none');
        } else {
            $('#loyalty-panel').addClass('d-none');
        }
        recalculateBill();
    });

    $('#redeem-loyalty-toggle').on('change', function () {
        if ($(this).is(':checked')) $('#redeem-points-row').removeClass('d-none');
        else { $('#redeem-points-row').addClass('d-none'); $('#redeem-points-input').val(0); recalculateBill(); }
    });

    // ==================== HOLD & RECALL ====================
    $('#btn-hold-bill, #btn-confirm-hold').on('click', function () {
        if (this.id === 'btn-hold-bill') {
            if (cart.length === 0) { Swal.fire({ icon: 'info', title: 'Empty Cart', text: 'Add products to hold.', background: '#151e30', color: '#f3f4f6' }); return; }
            $('#holdBillModal').modal('show');
            return;
        }
        const note = $('#hold-bill-note').val().trim();
        let subtotal = 0;
        cart.forEach(i => { subtotal += i.qty * i.rate; });
        $.post(BASE_URL + '/api/held_bills.php?action=hold', {
            csrf_token: csrfToken, customer_id: $('#pos-customer-select').val(), bill_note: note,
            cart_data: JSON.stringify(cart), subtotal: subtotal, invoice_type: $('#pos-invoice-type').val()
        }, function (res) {
            if (res.status) {
                $('#holdBillModal').modal('hide');
                cart = [];
                renderCart();
                loadHeldBillsCount();
                Swal.fire({ icon: 'success', title: 'Bill Held', text: res.message, timer: 1500, showConfirmButton: false, background: '#151e30', color: '#f3f4f6' });
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#151e30', color: '#f3f4f6' });
            }
        }, 'json');
    });

    $('#btn-toggle-held, #btn-close-held').click(function () {
        const panel = $('#held-bills-panel');
        if (panel.hasClass('open')) panel.removeClass('open');
        else { panel.addClass('open'); loadHeldBills(); }
    });

    function loadHeldBillsCount() {
        $.getJSON(BASE_URL + '/api/held_bills.php?action=list', function (res) {
            if (res.status) {
                const c = res.data.length;
                if (c > 0) $('#held-bills-count').text(c).removeClass('d-none');
                else $('#held-bills-count').addClass('d-none');
            }
        });
    }

    function loadHeldBills() {
        $.getJSON(BASE_URL + '/api/held_bills.php?action=list', function (res) {
            const container = $('#held-bills-list').empty();
            if (!res.status || res.data.length === 0) {
                container.html('<div class="text-center text-secondary py-4">No held bills</div>');
                return;
            }
            res.data.forEach(function (bill) {
                const items = JSON.parse(bill.cart_data || '[]');
                const time = new Date(bill.created_at).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
                container.append(
                    '<div class="held-bill-card" data-id="' + bill.id + '">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                    '<div><strong class="text-dark">' + (bill.customer_name || 'Walk-in') + '</strong>' +
                    '<div class="text-muted small">' + (bill.bill_note || 'No note') + '</div>' +
                    '<div class="text-muted small">' + items.length + ' items &bull; ₹' + parseFloat(bill.subtotal).toFixed(2) + ' &bull; ' + time + '</div></div>' +
                    '<div class="d-flex gap-1">' +
                    '<button class="btn btn-sm btn-success py-0 px-2 btn-recall-bill" data-id="' + bill.id + '" title="Recall"><i class="fa-solid fa-play"></i></button>' +
                    '<button class="btn btn-sm btn-outline-danger py-0 px-1 btn-delete-held" data-id="' + bill.id + '" title="Delete"><i class="fa-solid fa-trash-can"></i></button>' +
                    '</div></div></div>'
                );
            });
        });
    }

    $(document).on('click', '.btn-recall-bill', function () {
        const id = $(this).data('id');
        $.post(BASE_URL + '/api/held_bills.php?action=recall', { csrf_token: csrfToken, id: id }, function (res) {
            if (res.status) {
                cart = JSON.parse(res.data.cart_data || '[]');
                if (res.data.customer_id) $('#pos-customer-select').val(res.data.customer_id).trigger('change');
                if (res.data.invoice_type) $('#pos-invoice-type').val(res.data.invoice_type);
                renderCart();
                loadHeldBillsCount();
                $('#held-bills-panel').removeClass('open');
                Swal.fire({ icon: 'success', title: 'Bill Recalled', timer: 1000, showConfirmButton: false, background: '#151e30', color: '#f3f4f6' });
            }
        }, 'json');
    });

    $(document).on('click', '.btn-delete-held', function () {
        const id = $(this).data('id');
        $.post(BASE_URL + '/api/held_bills.php?action=delete', { csrf_token: csrfToken, id: id }, function (res) {
            if (res.status) { loadHeldBills(); loadHeldBillsCount(); }
        }, 'json');
    });

    // ==================== SAVE INVOICE ====================
    $('#btn-save-invoice').click(function () {
        if (cart.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Empty Cart', text: 'Add products first.', background: '#151e30', color: '#f3f4f6' });
            return;
        }
        const payments = getPayments();
        const grandTotal = parseFloat($('#bill-grand-total').text().replace('₹', '').replace(',', '')) || 0;
        let totalPaid = 0;
        payments.forEach(p => totalPaid += p.amount);

        if (totalPaid < grandTotal && !payments.some(p => p.method === 'CREDIT')) {
            Swal.fire({
                title: 'Partial Payment?', text: 'Received ₹' + totalPaid.toFixed(2) + ' of ₹' + grandTotal.toFixed(2) + '. Balance will be added to customer ledger.',
                icon: 'question', showCancelButton: true, confirmButtonText: 'Proceed', confirmButtonColor: '#10b981', background: '#151e30', color: '#f3f4f6'
            }).then(r => { if (r.isConfirmed) submitInvoice(payments); });
        } else {
            submitInvoice(payments);
        }
    });

    function submitInvoice(payments) {
        const flatDiscount = parseFloat($('#bill-discount-input').val()) || 0;
        const couponDiscount = appliedCoupon ? appliedCoupon.discount_amount : 0;
        let loyaltyPointsRedeem = 0, loyaltyDiscount = 0;
        if (loyaltyEnabled && $('#redeem-loyalty-toggle').is(':checked')) {
            loyaltyPointsRedeem = parseInt($('#redeem-points-input').val()) || 0;
            loyaltyDiscount = loyaltyPointsRedeem * loyaltyRedeemValue;
        }

        $.ajax({
            url: BASE_URL + '/api/billing.php?action=create_invoice', type: 'POST', dataType: 'json',
            data: {
                csrf_token: csrfToken, customer_id: $('#pos-customer-select').val(),
                invoice_type: $('#pos-invoice-type').val(), payments: JSON.stringify(payments),
                discount_amount: flatDiscount, coupon_id: appliedCoupon ? appliedCoupon.id : '',
                coupon_discount: couponDiscount, loyalty_points_redeemed: loyaltyPointsRedeem,
                loyalty_discount: loyaltyDiscount, due_date: $('#bill-due-date').val() || '',
                notes: $('#bill-notes').val() || '', is_igst: isInterState() ? 1 : 0,
                cart: JSON.stringify(cart)
            },
            success: function (res) {
                if (res.status) {
                    lastInvoiceId = res.data.invoice_id;
                    let printButtons = '';
                    if (posMode === 1) {
                        printButtons = '<a href="' + BASE_URL + '/invoice_thermal?id=' + res.data.invoice_id + '" target="_blank" class="btn btn-primary btn-sm"><i class="fa-solid fa-receipt me-1"></i>Print Thermal</a>' +
                                       '<a href="' + BASE_URL + '/invoice_print?id=' + res.data.invoice_id + '" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-print me-1"></i>A4 Print</a>';
                    } else {
                        printButtons = '<a href="' + BASE_URL + '/invoice_print?id=' + res.data.invoice_id + '" target="_blank" class="btn btn-primary btn-sm"><i class="fa-solid fa-print me-1"></i>Print</a>' +
                                       '<a href="' + BASE_URL + '/invoice_thermal?id=' + res.data.invoice_id + '" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-receipt me-1"></i>Thermal</a>';
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Invoice Created!',
                        html: '<div class="fw-bold fs-5 mb-3">' + res.data.invoice_number + '</div>' +
                            '<div class="d-flex flex-wrap gap-2 justify-content-center">' +
                            printButtons +
                            '<a href="https://api.whatsapp.com/send?text=' + encodeURIComponent('Invoice ' + res.data.invoice_number + ' - Total: ' + $('#bill-grand-total').text() + '. Thank you!') + '" target="_blank" class="btn btn-success btn-sm"><i class="fa-brands fa-whatsapp me-1"></i>WhatsApp</a>' +
                            '</div>',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2563eb',
                        background: '#ffffff',
                        color: '#1e293b'
                    }).then(function () {
                        resetCheckout();
                        window.location.href = BASE_URL + '/billing/index';
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#ffffff', color: '#1e293b' });
                }
            }
        });
    }

    function resetCheckout() {
        cart = [];
        appliedCoupon = null;
        renderCart();
        $('#bill-discount-input').val('0.00');
        $('#bill-notes').val('');
        $('#bill-due-date').val('');
        $('#pos-customer-select').val('').trigger('change');
        $('#coupon-code-input').val('').prop('disabled', false);
        $('#coupon-discount-row').addClass('d-none');
        if (loyaltyEnabled) { $('#redeem-loyalty-toggle').prop('checked', false); $('#redeem-points-row').addClass('d-none'); }
        // Reset payment rows to single
        $('#payment-methods-container').html(
            '<div class="payment-row d-flex gap-2 mb-2" data-index="0">' +
            '<select class="form-select form-select-sm pay-method" style="width: 55%;"><option value="CASH">CASH</option><option value="UPI">UPI / QR SCAN</option><option value="CARD">CARD</option><option value="NET_BANKING">NET BANKING</option><option value="CREDIT">CREDIT</option></select>' +
            '<input type="number" step="0.01" min="0" class="form-control form-control-sm pay-amount" placeholder="Amount" value="0.00"></div>'
        );
        paymentIndex = 1;
    }

    // ==================== KEYBOARD SHORTCUTS ====================
    $(window).on('keydown', function (e) {
        if (e.key === 'F2') { e.preventDefault(); $('#cart-product-search').focus(); }
        if (e.key === 'F3') { e.preventDefault(); $('#btn-hold-bill').click(); }
        if (e.key === 'F4') { e.preventDefault(); $('#btn-save-invoice').click(); }
        if (e.key === 'F5') { e.preventDefault(); $('#btn-toggle-held').click(); }
        if (e.key === 'F6') {
            e.preventDefault();
            if (lastInvoiceId) {
                const printUrl = posMode === 1 ? '/invoice_thermal?id=' : '/invoice_print?id=';
                window.open(BASE_URL + printUrl + lastInvoiceId, '_blank');
            } else Swal.fire({ icon: 'info', title: 'No Invoice', text: 'Generate an invoice first.', timer: 1500, showConfirmButton: false, background: '#151e30', color: '#f3f4f6' });
        }
        if (e.key === 'Escape') {
            if ($('.modal.show').length) { $('.modal.show').modal('hide'); return; }
            if ($('#held-bills-panel').hasClass('open')) { $('#held-bills-panel').removeClass('open'); return; }
            if (cart.length > 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'Cancel Checkout?', text: 'This will clear all items!', icon: 'warning',
                    showCancelButton: true, confirmButtonText: 'Clear', cancelButtonText: 'Keep', background: '#151e30', color: '#f3f4f6'
                }).then(r => { if (r.isConfirmed) resetCheckout(); });
            }
        }
    });

    // ==================== CUSTOMER MANAGEMENT ====================
    $('#btn-quick-customer').click(function () { $('#quickCustomerForm')[0].reset(); $('#quickCustomerModal').modal('show'); });

    $('#quickCustomerForm').submit(function (e) {
        e.preventDefault();
        $.post(BASE_URL + '/api/customers.php?action=save', $(this).serialize(), function (res) {
            if (res.status) {
                $('#quickCustomerModal').modal('hide');
                loadCustomers(function () { $('#pos-customer-select').val(res.data.id).trigger('change'); });
                Swal.fire({ icon: 'success', title: 'Customer Added', timer: 1500, showConfirmButton: false, background: '#151e30', color: '#f3f4f6' });
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#151e30', color: '#f3f4f6' });
            }
        }, 'json');
    });

    function loadCustomers(cb) {
        $.getJSON(BASE_URL + '/api/billing.php?action=get_customers', function (res) {
            if (res.status) {
                const sel = $('#pos-customer-select');
                sel.find('option:not(:first)').remove();
                customerData = {};
                res.data.forEach(function (c) {
                    sel.append('<option value="' + c.id + '">' + c.customer_name + ' (' + c.mobile + ')</option>');
                    customerData[c.id] = c;
                });
                if (cb) cb();
            }
        });
    }

    // ==================== NEW UI BUTTONS ====================
    $('#btn-focus-search').click(function () { $('#cart-product-search').focus(); });
    $('#btn-clear-cart').click(function () {
        if (cart.length === 0) return;
        Swal.fire({
            title: 'Clear All Items?', text: 'This will remove all products from the cart.', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Yes, Clear', confirmButtonColor: '#dc2626',
            background: '#ffffff', color: '#1e293b'
        }).then(function (r) { if (r.isConfirmed) { cart = []; renderCart(); } });
    });

    // Auto-fill mobile when customer selected
    $('#pos-customer-select').on('change', function () {
        const id = $(this).val();
        if (id && customerData[id]) {
            $('#pos-customer-mobile').val(customerData[id].mobile || '');
        } else {
            $('#pos-customer-mobile').val('');
        }
    });

    // ==================== UTILITIES ====================
    function playBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine'; osc.frequency.setValueAtTime(800, ctx.currentTime);
            gain.gain.setValueAtTime(0.05, ctx.currentTime);
            osc.connect(gain); gain.connect(ctx.destination);
            osc.start(); osc.stop(ctx.currentTime + 0.1);
        } catch (e) { }
    }
});
