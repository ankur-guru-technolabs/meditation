 <!-- jQuery -->
 <script src="{{ asset('assets/dist/plugins/jquery/jquery.min.js')}}"></script>
 <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
 <!-- Bootstrap 4 -->
 <script src="{{ asset('assets/dist/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
 <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
 <!-- overlayScrollbars -->
 <script src="{{ asset('assets/dist/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>
 <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
 <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
 <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
 <script>
     $(document).ready(function() {
        $('.dropDownMenu').on('click', function(event) {
            event.preventDefault();
            $(this).closest('.nav-item').toggleClass('menu-open');
        });
     });
</script>