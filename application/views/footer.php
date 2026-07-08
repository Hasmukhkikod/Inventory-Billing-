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

<!-- Floating Language Selector Button -->
<button class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="position: fixed; bottom: calc(80px + env(safe-area-inset-bottom)); right: 20px; width: 50px; height: 50px; z-index: 1050;" data-bs-toggle="modal" data-bs-target="#languageModal" title="Select Language">
    <i class="fa-solid fa-language fs-4"></i>
</button>

<!-- Language Selector Modal -->
<div class="modal fade" id="languageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h6 class="modal-title"><i class="fa-solid fa-language text-indigo me-2"></i>Choose Language</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="lang-search" class="form-control mb-3" placeholder="Search language... (e.g., Hindi)">
                
                <div class="list-group" id="lang-list" style="max-height: 300px; overflow-y: auto;">
                    <button class="list-group-item list-group-item-action lang-btn" data-lang="en">English (Default)</button>
                    <button class="list-group-item list-group-item-action lang-btn" data-lang="hi">Hindi (हिंदी)</button>
                    <button class="list-group-item list-group-item-action lang-btn" data-lang="gu">Gujarati (ગુજરાતી)</button>
                    <button class="list-group-item list-group-item-action lang-btn" data-lang="mr">Marathi (मराठी)</button>
                    <button class="list-group-item list-group-item-action lang-btn" data-lang="bn">Bengali (বাংলা)</button>
                    <button class="list-group-item list-group-item-action lang-btn" data-lang="ta">Tamil (தமிழ்)</button>
                    <button class="list-group-item list-group-item-action lang-btn" data-lang="te">Telugu (తెలుగు)</button>
                    <button class="list-group-item list-group-item-action lang-btn" data-lang="kn">Kannada (ಕನ್ನಡ)</button>
                    <button class="list-group-item list-group-item-action lang-btn" data-lang="ml">Malayalam (മലയാളം)</button>
                    <button class="list-group-item list-group-item-action lang-btn" data-lang="pa">Punjabi (ਪੰਜਾਬੀ)</button>
                    <button class="list-group-item list-group-item-action lang-btn" data-lang="ur">Urdu (اردو)</button>
                    <button class="list-group-item list-group-item-action lang-btn" data-lang="or">Odia (ଓଡ଼ିଆ)</button>
                    <button class="list-group-item list-group-item-action lang-btn" data-lang="as">Assamese (অসমীয়া)</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Google Translate Element -->
<div id="google_translate_element" style="display:none;"></div>
<script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en', autoDisplay: false}, 'google_translate_element');
}
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<style>
    .goog-te-banner-frame.skiptranslate, .goog-te-gadget-icon { display: none !important; }
    body { top: 0px !important; }
    #goog-gt-tt { display: none !important; }
</style>

<!-- Include Mobile Bottom Navigation -->
<?php require_once __DIR__ . '/bottom_nav.php'; ?>

<!-- Global scripts -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables Core & Bootstrap 5 Integration -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>$.fn.dataTable.ext.errMode = 'none';</script>
<!-- SweetAlert 2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Select2 Searchable Dropdowns -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Bulk Actions -->
<script src="<?php echo BASE_URL; ?>/assets/js/bulk-actions.js"></script>

<!-- App Global Logic -->
<script>
$(document).ready(function() {
    // Global AJAX Error Handler to prevent silent UI failures
    $.ajaxSetup({
        error: function(jqXHR, textStatus, errorThrown) {
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

// ==================== DYNAMIC LANGUAGE ENGINE ====================
$(document).ready(function() {
    $('#lang-search').on('input', function() {
        const query = $(this).val().toLowerCase();
        $('.lang-btn').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(query));
        });
    });

    $('.lang-btn').on('click', function(e) {
        e.preventDefault();
        const langCode = $(this).data('lang');
        const select = document.querySelector('select.goog-te-combo');
        
        if (select) {
            select.value = langCode;
            select.dispatchEvent(new Event('change'));
        }
        
        // Save to cookie so it persists
        document.cookie = `googtrans=/en/${langCode}; path=/`;
        document.cookie = `googtrans=/en/${langCode}; domain=${location.hostname}; path=/`;
        
        $('#languageModal').modal('hide');
    });

    // Auto-enforce System Language Default
    const sysLang = '<?php echo $compSettings["system_language"] ?? "en"; ?>';
    if (sysLang !== 'en' && document.cookie.indexOf('googtrans=') === -1) {
        document.cookie = `googtrans=/en/${sysLang}; path=/`;
        document.cookie = `googtrans=/en/${sysLang}; domain=${location.hostname}; path=/`;
        location.reload();
    }
});
</script>
</body>
</html>
