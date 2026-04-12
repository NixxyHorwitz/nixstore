    </div> <!-- End Content Wrap -->
</div> <!-- End Main Content -->

<!-- Bootstrap JS Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    function toggleSidebar(e) {
        if(e) e.preventDefault();
        document.getElementById('sidebar').classList.toggle('open');
        document.querySelector('.sb-overlay').classList.toggle('show');
    }
    
    // Default config for SweetAlert2 toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: 'var(--surface)',
        color: 'var(--text)',
        iconColor: 'var(--accent)'
    });
    
    $.ajaxSetup({
        error: function(xhr, status, error) {
            Toast.fire({
                icon: 'error',
                title: 'AJAX Error: ' + error
            });
        }
    });

    // Initialize simple Datatables
    $(document).ready(function() {
        if($('.datatable').length) {
            $('.datatable').DataTable({
                "pageLength": 10,
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampil _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Tidak ada data",
                    "paginate": {
                        "first": "Awal",
                        "last": "Akhir",
                        "next": "Lanjut",
                        "previous": "Mundur"
                    }
                }
            });
        }
    });
</script>
</body>
</html>
