<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * MVC Common Footer View
 */
?>
        <!-- Footer info bar (adds extra bottom margin on mobile to clear bottom nav) -->
        <footer class="mt-5 pt-4 border-top border-secondary text-center text-secondary small pb-3 mb-5 mb-md-0">
            <p class="mb-0">© 2026 Grovixo. All rights reserved. Invoice & Inventory Management System.</p>
        </footer>
    </main>
</div>

<!-- POS Guide Modal -->
<div class="modal fade" id="posGuideModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-barcode text-indigo me-2"></i>How to Use Barcode Scanner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="d-flex align-items-start mb-3">
                    <div class="bg-indigo text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 35px; height: 35px; font-weight: bold;">1</div>
                    <div>
                        <h6 class="fw-bold mb-1">Connect Your Scanner</h6>
                        <p class="text-secondary small mb-0">Plug in your USB or Bluetooth barcode scanner to your device.</p>
                    </div>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <div class="bg-indigo text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 35px; height: 35px; font-weight: bold;">2</div>
                    <div>
                        <h6 class="fw-bold mb-1">Ensure "Enter Key" is Enabled</h6>
                        <p class="text-secondary small mb-0">Hardware scanners type the barcode and must press "Enter" automatically. If it doesn't add the item to the cart, configure your scanner manual to "Append Enter Key".</p>
                    </div>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <div class="bg-indigo text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 35px; height: 35px; font-weight: bold;">3</div>
                    <div>
                        <h6 class="fw-bold mb-1">Scan to Add</h6>
                        <p class="text-secondary small mb-0">Just scan the product's barcode label. The product will be instantly added to your cart with a success beep!</p>
                    </div>
                </div>
                <div class="d-flex align-items-start mb-1">
                    <div class="bg-indigo text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 35px; height: 35px; font-weight: bold;">4</div>
                    <div>
                        <h6 class="fw-bold mb-1">Scan Again for Qty</h6>
                        <p class="text-secondary small mb-0">If you scan the same product twice, its quantity will automatically increase in the cart.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Got it!</button>
            </div>
        </div>
    </div>
</div>

<!-- Floating Help Widget -->
<div class="floating-help-widget">
    <div class="floating-help-menu d-none" id="floating-help-menu">
        <a href="#" class="floating-help-menu-item" data-bs-toggle="modal" data-bs-target="#helpCenterModal">
            <span class="floating-help-menu-icon bg-light-primary text-indigo"><i class="fa-solid fa-circle-question"></i></span>
            <span>Help Center</span>
        </a>
        <a href="https://wa.me/919978740360" target="_blank" rel="noopener" class="floating-help-menu-item">
            <span class="floating-help-menu-icon bg-light-success text-emerald"><i class="fa-brands fa-whatsapp"></i></span>
            <span>WhatsApp</span>
        </a>
        <a href="#" class="floating-help-menu-item" data-bs-toggle="modal" data-bs-target="#feedbackModal">
            <span class="floating-help-menu-icon bg-light-warning text-warning"><i class="fa-solid fa-comment-dots"></i></span>
            <span>Feedback</span>
        </a>
    </div>
    <button class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center floating-help-btn" id="floating-help-btn" type="button" title="Help">
        <i class="fa-solid fa-headset fs-4" id="floating-help-icon"></i>
    </button>
</div>

<!-- Help Center Modal -->
<div class="modal fade" id="helpCenterModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-circle-question text-indigo me-2"></i>Help Center</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="help-topic-card">
                            <i class="fa-solid fa-file-invoice text-indigo"></i>
                            <h6>Billing &amp; Invoices</h6>
                            <p class="text-secondary small mb-0">Search or scan a product to add it to the cart, apply a discount or coupon, then Generate Invoice. Print via A4 or Thermal from the confirmation screen.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="help-topic-card">
                            <i class="fa-solid fa-box-open text-indigo"></i>
                            <h6>Inventory</h6>
                            <p class="text-secondary small mb-0">Add products under Inventory, set a minimum stock level to get low-stock alerts, and adjust stock manually if you need to correct a count.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="help-topic-card">
                            <i class="fa-solid fa-print text-indigo"></i>
                            <h6>Receipt Printer Setup</h6>
                            <p class="text-secondary small mb-0">Connect a USB, Bluetooth, or WiFi/LAN receipt printer under Settings &rarr; Printer Settings, then set it as your default for one-tap printing.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="help-topic-card">
                            <i class="fa-solid fa-chart-line text-indigo"></i>
                            <h6>Reports</h6>
                            <p class="text-secondary small mb-0">Sales, purchases, stock, GST, and overdue balances are all under Reports, filterable by date range.</p>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-secondary small mb-2">Still need a hand?</p>
                    <a href="https://wa.me/919978740360" target="_blank" rel="noopener" class="btn btn-success btn-sm"><i class="fa-brands fa-whatsapp me-1"></i>Chat with us on WhatsApp</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-comment-dots text-indigo me-2"></i>Send Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="feedbackForm">
                <?php echo \App\Models\Helpers::csrfField(); ?>
                <div class="modal-body pt-2">
                    <p class="text-secondary small">Found a bug, or have an idea to make this better? Let us know.</p>
                    <textarea class="form-control" name="message" id="feedback-message" rows="4" placeholder="What's on your mind?" required></textarea>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-paper-plane me-1"></i>Send Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>





<!-- Include Mobile Bottom Navigation -->
<?php require_once __DIR__ . '/bottom_nav.php'; ?>

<!-- Global scripts -->
<!-- jQuery already loaded in header.php -->
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables Core & Bootstrap 5 Integration -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<!-- DataTables Responsive extension: collapses extra columns into an
     expandable row instead of horizontal-scrolling on narrow screens -->
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
    $.fn.dataTable.ext.errMode = 'none';
    // Show a branded skeleton-row loading effect on every DataTable while its data
    // is being fetched, shaped to that table's own columns, instead of a blank or
    // flashing table. Applies app-wide by default - driven off DataTables' own
    // processing.dt event rather than the built-in indicator.
    // responsive:true applies to every DataTable app-wide with no per-page
    // changes needed - extra columns collapse into an expandable "+" row on
    // narrow screens instead of forcing a horizontal scroll.
    $.extend(true, $.fn.dataTable.defaults, { processing: true, responsive: true });

    // Minimum time the loader stays visible once shown, so a fast (e.g. local/
    // cached) response doesn't flash it on and off faster than the eye can register.
    const DT_LOADER_MIN_VISIBLE_MS = 450;
    const DT_LOADER_ROW_COUNT = 6;

    function buildSkeletonRows($table) {
        const $headCells = $table.find('thead tr').first().find('th');
        const widths = $headCells.map(function () { return $(this).outerWidth(); }).get();
        if (!widths.length) return '';

        const gridCols = widths.map(w => w + 'px').join(' ');
        let rowsHtml = '';
        for (let r = 0; r < DT_LOADER_ROW_COUNT; r++) {
            let cellsHtml = '';
            widths.forEach(function (w, i) {
                // Narrow columns (checkboxes, icons, short numbers) get a short
                // centered bar; wider text columns get a longer, varied-length one
                // so the rows don't look like a uniform, robotic grid.
                const isNarrow = w < 70;
                const pct = isNarrow ? 45 : (50 + ((r * 37 + i * 53) % 40));
                const delay = ((r * 0.06) + (i * 0.03)).toFixed(2);
                cellsHtml += `<div class="dt-skel-cell${isNarrow ? ' dt-skel-cell-narrow' : ''}"><span class="dt-skel-bar" style="width:${pct}%; animation-delay:${delay}s;"></span></div>`;
            });
            rowsHtml += `<div class="dt-skel-row" style="grid-template-columns:${gridCols};">${cellsHtml}</div>`;
        }
        return rowsHtml;
    }

    $(document).on('processing.dt', function (e, settings, isProcessing) {
        const $wrapper = $(settings.nTableWrapper);
        const $table = $(settings.nTable);
        let $overlay = $wrapper.find('> .dt-loader-overlay');

        if (isProcessing) {
            if (!$overlay.length) {
                $overlay = $('<div class="dt-loader-overlay"><div class="dt-skel-topbar"></div><div class="dt-skel-table"></div></div>');
                $wrapper.css('position', 'relative').append($overlay);
            }
            $overlay.find('.dt-skel-table').html(buildSkeletonRows($table));
            clearTimeout($overlay.data('hideTimer'));
            $overlay.data('shownAt', Date.now()).show();
        } else if ($overlay.length) {
            const elapsed = Date.now() - ($overlay.data('shownAt') || 0);
            const remaining = DT_LOADER_MIN_VISIBLE_MS - elapsed;
            if (remaining > 0) {
                const hideTimer = setTimeout(() => $overlay.hide(), remaining);
                $overlay.data('hideTimer', hideTimer);
            } else {
                $overlay.hide();
            }
        }
    });

    // First letter of up to the first two words of a name - for the round
    // avatar badge on mobile cards (e.g. "Priya Sharma" -> "PS").
    function entityInitials(name) {
        const words = (name || '').trim().split(/\s+/).filter(Boolean);
        return (words.slice(0, 2).map(w => w[0]).join('') || '?').toUpperCase();
    }

    // Renders a mobile card per row for any DataTable-driven list page, built
    // directly from that row's already-rendered <td> HTML - so badges, links,
    // and formatting never drift out of sync with the desktop table. Call from
    // a table's drawCallback (fires on every load/search/page/sort) with:
    //   tableId:      DataTable's <table> id
    //   containerId:  empty <div> id to render cards into
    //   titleCol:     column index used as the card's heading
    //   metaCols:     column indexes shown as a small line under the heading
    //   fieldCols:    [{ col, label, wide? }] shown in the details grid
    //   actionsCol:   column index whose HTML (the .btn-group) is reused as-is
    //   initials(row): optional fn($tds) -> avatar text; omit to hide the avatar
    //   emptyText:    shown when the table has no rows
    function renderEntityMobileCards(config) {
        const $container = $('#' + config.containerId);
        if (!$container.length) return;

        const $rows = $('#' + config.tableId).find('> tbody > tr');
        $container.empty();

        // DataTables renders a single full-width <td> ("No data available...")
        // when empty - treat that the same as zero rows.
        if ($rows.length === 0 || ($rows.length === 1 && $rows.find('td').length === 1)) {
            $container.html('<div class="entity-mobile-empty">' + (config.emptyText || 'No records found.') + '</div>');
            return;
        }

        $rows.each(function () {
            const $tds = $(this).find('> td');
            const titleHtml = $tds.eq(config.titleCol).html() || '';

            const metaHtml = (config.metaCols || []).map(function (m) {
                const c = typeof m === 'object' ? m.col : m;
                const icon = typeof m === 'object' && m.icon ? '<i class="' + m.icon + '"></i>' : '';
                const html = ($tds.eq(c).html() || '').trim();
                return (html && html !== '-') ? '<span>' + icon + html + '</span>' : '';
            }).join('');

            const fieldsHtml = (config.fieldCols || []).map(function (f) {
                const cls = f.wide ? ' class="entity-field-wide"' : '';
                return '<div' + cls + '><span class="entity-field-label">' + f.label + '</span><strong>' + ($tds.eq(f.col).html() || '') + '</strong></div>';
            }).join('');

            const actionsHtml = config.actionsCol != null ? ($tds.eq(config.actionsCol).html() || '') : '';
            // Avatar is either a reused column's HTML (e.g. a product thumbnail
            // <img>/placeholder already rendered for the desktop table) or
            // initials text computed from the row - avatarCol takes priority.
            const avatarInner = config.avatarCol != null ? ($tds.eq(config.avatarCol).html() || '') : (config.initials ? config.initials($tds) : '');
            const showAvatar = config.avatarCol != null || !!config.initials;

            const $card = $(
                '<article class="entity-mobile-card">' +
                    '<div class="entity-mobile-card-head">' +
                        (showAvatar ? '<div class="entity-mobile-avatar' + (config.avatarCol != null ? ' entity-mobile-avatar-img' : '') + '">' + avatarInner + '</div>' : '') +
                        '<div class="entity-mobile-identity">' +
                            '<h2>' + titleHtml + '</h2>' +
                            (metaHtml ? '<div class="entity-mobile-meta">' + metaHtml + '</div>' : '') +
                        '</div>' +
                    '</div>' +
                    (fieldsHtml ? '<div class="entity-mobile-details">' + fieldsHtml + '</div>' : '') +
                    (actionsHtml ? '<div class="entity-mobile-actions">' + actionsHtml + '</div>' : '') +
                '</article>'
            );
            $container.append($card);
        });
    }
</script>
<!-- SweetAlert 2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Select2 Searchable Dropdowns -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Bulk Actions -->
<script src="<?php echo BASE_URL; ?>/assets/js/bulk-actions.js?v=<?php echo \App\Models\Helpers::assetVersion('/assets/js/bulk-actions.js'); ?>"></script>

<!-- App Global Logic -->
<script>
$(document).ready(function() {
    // Global AJAX Error Handler to prevent silent UI failures
    $.ajaxSetup({
        error: function(jqXHR, textStatus, errorThrown) {
            // Ignore intentionally cancelled requests (e.g. Select2 aborting a stale
            // in-flight search as the user keeps typing) - these are not real errors.
            if (textStatus === 'abort' || jqXHR.statusText === 'abort') {
                return;
            }
            console.error("AJAX Error: ", textStatus, errorThrown, jqXHR.responseText);
            Swal.fire({
                icon: 'error',
                title: 'System Error',
                text: 'An unexpected server error occurred. Please try again or check system logs.',
                background: '#ffffff',
                color: '#0f172a'
            });
        }
    });

    // Floating Help speed-dial menu
    $('#floating-help-btn').on('click', function (e) {
        e.stopPropagation();
        const $menu = $('#floating-help-menu');
        const opening = $menu.hasClass('d-none');
        $menu.toggleClass('d-none', !opening);
        $('#floating-help-icon').toggleClass('fa-headset', !opening).toggleClass('fa-xmark', opening);
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.floating-help-widget').length) {
            $('#floating-help-menu').addClass('d-none');
            $('#floating-help-icon').removeClass('fa-xmark').addClass('fa-headset');
        }
    });
    $('.floating-help-menu-item').on('click', function () {
        $('#floating-help-menu').addClass('d-none');
        $('#floating-help-icon').removeClass('fa-xmark').addClass('fa-headset');
    });

    // Feedback form
    $('#feedbackForm').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        $btn.prop('disabled', true);
        $.post(BASE_URL + '/api/feedback.php?action=save', $form.serialize() + '&page_url=' + encodeURIComponent(window.location.href), function (res) {
            if (res.status) {
                $('#feedbackModal').modal('hide');
                $form[0].reset();
                Swal.fire({ icon: 'success', title: 'Thank you!', text: 'Your feedback has been sent.', timer: 1800, showConfirmButton: false, background: '#ffffff', color: '#0f172a' });
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: res.message, background: '#ffffff', color: '#0f172a' });
            }
        }, 'json').fail(function () {
            Swal.fire({ icon: 'error', title: 'Failed', text: 'Could not reach the server.', background: '#ffffff', color: '#0f172a' });
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    // Auto-apply Select2 to all searchable dropdowns
    $('.searchable-select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: $(this).data('placeholder') || 'Search...',
        allowClear: true
    });
    // ==================== DARK/LIGHT THEME TOGGLE ====================
    const savedTheme = localStorage.getItem('grovixo-theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    $('#theme-toggle-btn').on('click', function() {
        const current = document.documentElement.getAttribute('data-theme') || 'light';
        const next = current === 'light' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('grovixo-theme', next);
        updateThemeIcon(next);
    });

    function updateThemeIcon(theme) {
        const icon = $('#theme-icon');
        if (theme === 'dark') {
            icon.removeClass('fa-moon').addClass('fa-sun');
        } else {
            icon.removeClass('fa-sun').addClass('fa-moon');
        }
    }

    // Enable Bootstrap tooltips globally
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // Auto-fade flash alerts
    setTimeout(function() {
        $(".alert-dismissible").fadeOut('slow');
    }, 5000);

    // Responsive Sidebar toggles
    $('#sidebar-toggle-btn, #bottom-menu-toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#app-sidebar').addClass('show');
        $('#sidebar-backdrop').fadeIn(250);
    });

    $('#sidebar-close-btn, #sidebar-backdrop, .btn-logout-icon').on('click', function() {
        $('#app-sidebar').removeClass('show');
        $('#sidebar-backdrop').fadeOut(250);
    });

    // Removed applyGlobalTableLabels as we now use native responsive scrolling
});



</body>
</html>
