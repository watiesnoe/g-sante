  <link rel="stylesheet" href="{{ asset('admin/js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">

    <!-- Dashmix framework -->
    <link rel="stylesheet" id="css-main" href="{{ asset('admin/css/dashmix.min.css') }}">

    <!-- You can include a specific file from css/themes/ folder to alter the default color theme of the template. eg: -->
    <!-- <link rel="stylesheet" id="css-theme" href="asset/css/themes/xwork.min.css"> -->
    <!-- END Stylesheets -->
      <link rel="stylesheet" href="{{ asset('admin/js/plugins/select2/css/select2.min.css') }}">

    <!-- Load and set color theme + dark mode preference (blocking script to prevent flashing) -->
    <script src="{{ asset('admin/js/setTheme.js') }}"></script>
  <style>
      /* Ajuste la hauteur, les bordures et le style général de Select2 pour correspondre à Dashmix */
      .select2-container--default .select2-selection--single {
          height: 38px;
          padding: 5px 12px;
          font-size: 0.9375rem;
          border: 1px solid #dcdfe3;
          border-radius: 0.25rem;
          background-color: #fff;
          transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
      }

      .select2-container--default .select2-selection--single .select2-selection__rendered {
          line-height: 26px;
          padding-left: 0;
          color: #495057;
      }

      .select2-container--default .select2-selection--single .select2-selection__arrow {
          height: 36px;
      }

      .select2-container--default.select2-container--focus .select2-selection--single {
          border-color: #0665d0;
          box-shadow: 0 0 0 0.2rem rgba(6, 101, 208, 0.15);
      }

      .select2-dropdown {
          border: 1px solid #dcdfe3;
          border-radius: 0.25rem;
          box-shadow: 0 4px 6px rgba(0,0,0,0.08);
      }
  </style>
