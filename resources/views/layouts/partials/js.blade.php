    <script src="{{ asset('admin/js/dashmix.app.min.js') }}"></script>

    <!-- jQuery (required for DataTables plugin) -->
    <script src="{{ asset('admin/js/lib/jquery.min.js') }}"></script>

    <!-- Page JS Plugins -->
    <script src="{{ asset('admin/js/plugins/datatables/dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/datatables-buttons/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/datatables-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/datatables-buttons-jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/datatables-buttons-pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/datatables-buttons-pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/datatables-buttons/buttons.print.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/datatables-buttons/buttons.html5.min.js') }}"></script>

    <!-- Page JS Code -->
    <script src="{{ asset('admin/js/pages/be_tables_datatables.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/jquery-validation/additional-methods.js') }}"></script>
{{--    <script src="assets/js/plugins/jquery-validation/jquery.validate.min.js"></script>--}}

    <!-- Page JS Code -->
    <script src="{{asset('admin/js/pages/op_auth_signin.min.js')}}"></script>
    <!-- Page JS Helpers (Select2 plugin) -->
    <script>Dashmix.helpersOnLoad(['jq-select2']);</script>

    <!-- Page JS Code -->
    <script src="{{ asset('admin/js/pages/be_forms_validation.min.js') }}"></script>
{{-- <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>--}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


{{-- Table offre js datatbable---}}
    <script>
        $(document).ready(function() {
            $('.js-select2').select2({
                placeholder: "-- Sélectionner un patient existant --",
                allowClear: true,
                width: '100%' // prend toute la largeur du parent
            });
        });
    </script>



