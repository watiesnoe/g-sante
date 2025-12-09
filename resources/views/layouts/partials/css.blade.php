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
      /* Ajuste la hauteur et la taille du texte */
      .select2-container--default .select2-selection--single {
          height: 36px;          /* ajuste selon ton besoin */
          padding: 6px 12px;     /* padding interne */
          font-size: 1rem;       /* taille du texte */
      }

      .select2-container--default .select2-selection--single .select2-selection__rendered {
          line-height: 32px;     /* centrer verticalement le texte */
      }

      .select2-container--default .select2-selection--single .select2-selection__arrow {
          height: 36px;          /* même hauteur que le select */
      }
  </style>
