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
    // Auto-apply Select2 to all searchable dropdowns
    $('.searchable-select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: $(this).data('placeholder') || 'Search...',
        allowClear: true
    });
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

    // Automatically inject data-labels for mobile-responsive table cards
    function applyGlobalTableLabels() {
        $('table').each(function() {
            const table = $(this);
            const headers = [];
            table.find('thead th').each(function() {
                headers.push($(this).text().trim());
            });
            table.find('tbody tr').each(function() {
                $(this).find('td').each(function(index) {
                    if (headers[index] && !$(this).attr('data-label')) {
                        $(this).attr('data-label', headers[index]);
                    }
                });
            });
        });
    }

    applyGlobalTableLabels();
    $(document).on('draw.dt', 'table', function() {
        applyGlobalTableLabels();
    });
});
</script>

</body>
</html>
