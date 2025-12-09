<!doctype html>
<html lang="en" class="remember-theme">
<head>
    <meta charset="utf-8">
    <!--
      Available classes for <html> element:

      'dark'                  Enable dark mode - Default dark mode preference can be set in app.js file (always saved and retrieved in localStorage afterwards):
                                window.Dashmix = new App({ darkMode: "system" }); // "on" or "off" or "system"
      'dark-custom-defined'   Dark mode is always set based on the preference in app.js file (no localStorage is used)
      'remember-theme'        Remembers active color theme between pages using localStorage when set through
                                - Theme helper buttons [data-toggle="theme"]
    -->
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ges-Santé</title>

    <meta name="description" content="Dashmix - Bootstrap 5 Admin Template &amp; UI Framework created by pixelcave">
    <meta name="author" content="pixelcave">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta -->
    <meta property="og:title" content="Dashmix - Bootstrap 5 Admin Template &amp; UI Framework">
    <meta property="og:site_name" content="Dashmix">
    <meta property="og:description"
          content="Dashmix - Bootstrap 5 Admin Template &amp; UI Framework created by pixelcave">
    <meta property="og:type" content="website">
    <meta property="og:url" content="">
    <meta property="og:image" content="">

    <!-- Icons -->
    <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
    <link rel="shortcut icon" href="admin/media/favicons/favicon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="admin/media/favicons/favicon-192x192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="admin/media/favicons/apple-touch-icon-180x180.png">
    <!-- END Icons -->

    <!-- Stylesheets -->
    <!-- Page JS Plugins CSS -->
    @include('layouts.partials.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <style>
            .card-statistic {
                transition: all 0.3s ease;
                border-radius: 12px;
                overflow: hidden;
            }
            .card-statistic:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
            }
            .card-statistic-sm {
                border-radius: 8px;
            }
            .hover-lift:hover {
                transform: translateY(-2px);
            }
            .hover-scale:hover {
                transform: scale(1.02);
            }
            .alert-gradient-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                color: white;
            }
            .avatar {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 50px;
                height: 50px;
            }
            .page-title {
                font-size: 1.8rem;
                font-weight: 700;
                color: #2c3e50;
            }
            .table th {
                font-weight: 600;
                font-size: 0.875rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #6c757d;
            }
            .quick-access-card {
                transition: all 0.3s ease;
                border: 1px solid #e9ecef;
            }
            .quick-access-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                border-color: #4670ff;
            }
            .module-card {
                transition: all 0.3s ease;
            }
            .module-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            }
            .hover-bg:hover {
                background-color: #f8f9fa;
            }
        </style>

    <style>
        :root {
            --primary: #2c7fb8;
            --primary-dark: #1a5a8a;
            --primary-light: #e8f4fe;
            --secondary: #7fcdbb;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
            --dark: #2c3e50;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --border: #dee2e6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: var(--dark);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary), var(--primary-dark));
            color: white;
            height: 100vh;
            position: fixed;
            transition: all 0.3s;
            box-shadow: 3px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background-color: rgba(0, 0, 0, 0.1);
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 600;
        }

        .sidebar-user {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--secondary), var(--primary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
            margin: 0 auto 15px;
            border: 3px solid rgba(255, 255, 255, 0.2);
        }

        .user-info h3 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .user-info p {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu ul {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .sidebar-menu a i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid var(--secondary);
        }

        .menu-badge {
            margin-left: auto;
            background-color: var(--danger);
            color: white;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 0.75rem;
        }

        .menu-divider {
            padding: 15px 25px 5px;
            font-size: 0.8rem;
            text-transform: uppercase;
            opacity: 0.6;
            letter-spacing: 1px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 25px;
            transition: all 0.3s;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .header h1 {
            font-size: 1.8rem;
            color: var(--dark);
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 10px 15px 10px 40px;
            border: 1px solid var(--border);
            border-radius: 8px;
            width: 300px;
            font-size: 0.9rem;
            background-color: white;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .notification-bell {
            position: relative;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 8px;
            color: var(--dark);
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--danger);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 1.8rem;
        }

        .stat-1 { background-color: rgba(44, 127, 184, 0.1); color: var(--primary); }
        .stat-2 { background-color: rgba(46, 204, 113, 0.1); color: var(--success); }
        .stat-3 { background-color: rgba(243, 156, 18, 0.1); color: var(--warning); }
        .stat-4 { background-color: rgba(231, 76, 60, 0.1); color: var(--danger); }

        .stat-info h3 {
            font-size: 2rem;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .stat-info p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Content Sections */
        .content-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--gray-light);
        }

        .section-header h2 {
            font-size: 1.4rem;
            color: var(--dark);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border);
            color: var(--dark);
        }

        .btn-outline:hover {
            background-color: var(--gray-light);
        }

        /* Table */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            background-color: var(--light);
            color: var(--gray);
            font-weight: 600;
            font-size: 0.9rem;
        }

        tr:hover {
            background-color: var(--primary-light);
        }

        .patient-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .patient-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .patient-info h4 {
            font-size: 0.95rem;
            margin-bottom: 3px;
        }

        .patient-info p {
            font-size: 0.8rem;
            color: var(--gray);
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: rgba(46, 204, 113, 0.15);
            color: var(--success);
        }

        .badge-warning {
            background-color: rgba(243, 156, 18, 0.15);
            color: var(--warning);
        }

        .badge-danger {
            background-color: rgba(231, 76, 60, 0.15);
            color: var(--danger);
        }

        .badge-primary {
            background-color: rgba(44, 127, 184, 0.15);
            color: var(--primary);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 0.8rem;
            border-radius: 6px;
        }

        /* Charts & Calendar */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .chart-actions {
            display: flex;
            gap: 10px;
        }

        .chart-wrapper {
            height: 300px;
            position: relative;
        }

        /* Calendar */
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            margin-top: 15px;
        }

        .calendar-header {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .calendar-day {
            height: 80px;
            border: 1px solid var(--border);
            padding: 8px;
            border-radius: 8px;
            background-color: white;
            overflow-y: auto;
            font-size: 0.85rem;
        }

        .calendar-day.other-month {
            background-color: var(--light);
            color: #adb5bd;
        }

        .calendar-day.today {
            background-color: var(--primary-light);
            border-color: var(--primary);
        }

        .calendar-event {
            font-size: 0.7rem;
            background-color: var(--primary);
            color: white;
            padding: 2px 5px;
            border-radius: 4px;
            margin-bottom: 2px;
            cursor: pointer;
        }

        /* Emergency Section */
        .emergency-section {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .emergency-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .emergency-header h3 {
            font-size: 1.3rem;
        }

        .emergency-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .emergency-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 15px;
            border-radius: 8px;
            backdrop-filter: blur(5px);
        }

        .emergency-patient {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .emergency-patient h4 {
            font-size: 1rem;
        }

        .emergency-info {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            .sidebar-header h2 span,
            .sidebar-user .user-info,
            .sidebar-menu a span,
            .menu-divider,
            .menu-badge {
                display: none;
            }
            .sidebar-menu a i {
                margin-right: 0;
                font-size: 1.3rem;
            }
            .main-content {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            .search-box input {
                width: 200px;
            }
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>

<body>

<div id="page-container"
     class="sidebar-o sidebar-light enable-page-overlay side-scroll page-header-fixed main-content-narrow">

    @include('layouts.partials.sidebare')
    <!-- END Sidebar -->

    @include('layouts.partials.navbare')
    <!-- END Header -->

    <!-- Main Container -->
    <main id="main-container">
        <!-- Hero -->


        <!-- Page Content -->
        @yield('content')
        <!-- END Page Content -->
    </main>
    <!-- END Main Container -->

    <!-- Footer -->
    @include('layouts.partials.footer')
    <!-- END Footer -->
</div>
<!-- END Page Container -->

<!--
  Dashmix JS

  Core libraries and functionality
  webpack is putting everything together at asset/_js/main/app.js
-->
@include('layouts.partials.js')
@yield('scripts')

</body>
</html>
