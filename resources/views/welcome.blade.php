{{-- <!DOCTYPE html> --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Modern AI Landing HTML Template">
  <meta name="keywords" content="bootstrap 5, saas, landing page">
  <meta name="author" content="Themetags">
  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.3/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.3/ScrollTrigger.min.js"></script> -->
  <title>Social Mind - Modern AI Landing HTML Template</title>

  <!-- Update the paths for stylesheets -->
  {{-- <link rel="stylesheet" href="/css/fonts/ff-1.css">
  <link rel="stylesheet" href="/css/fonts/ff-3.css">
  <link rel="stylesheet" href="/css/fonts/bootstrap-icons.css"> --}}
  {{-- <link rel="icon" href="/img/favicon.png" type="image/png"> --}}
  {{-- <link rel="stylesheet" href="/css/plugins.min.css">
  <link rel="stylesheet" href="/css/style.min.css"> --}}

  <!-- DashBoard CSS> -->
    <!--FONT AWESOME-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Themify icon-->
    <link rel="stylesheet" type="text/css" href="/css/vendors/themify.css">
    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="/css/vendors/flag-icon.css">
    <!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="/css/vendors/feather-icon.css">
    <!-- Plugins css start-->
    <link rel="stylesheet" type="text/css" href="/css/vendors/slick.css">
    <link rel="stylesheet" type="text/css" href="/css/vendors/slick-theme.css">
    <link rel="stylesheet" type="text/css" href="/css/vendors/scrollbar.css">
    <link rel="stylesheet" type="text/css" href="/css/vendors/animate.css">

  <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body class="bg-black body-clip">
  <div id="app"></div>
  @vite([
    'resources/js/legacy/plugins.js',
    'resources/js/legacy/app.js',
    'resources/js/legacy/animatedt-title.js',
    'resources/js/app.js',

    //Dashboard Js files.
    // latest jquery
    'resources/js/legacy/jquery.min.js',
    // Bootstrap js
    'resources/js/legacy/bootstrap/bootstrap.bundle.min.js',
    // Feather Icon
    'resources/js/legacy/icons/feather-icon/feather.min.js',
    'resources/js/legacy/icons/feather-icon/feather-icon.js',
    // ScrollBar Js
    'resources/js/legacy/scrollbar/simplebar.js',
    'resources/js/legacy/scrollbar/custom.js',
    // Sidebar Jquery
    'resources/js/legacy/config.js',
    // Plugins JS start
    // 'resources/js/legacy/sidebar-menu.js',
    'resources/js/legacy/sidebar-pin.js',
    'resources/js/legacy/slick/slick.min.js',
    'resources/js/legacy/slick/slick.js',
    'resources/js/legacy/header-slick.js',
    'resources/js/legacy/chart/apex-chart/apex-chart.js',
    'resources/js/legacy/chart/apex-chart/stock-prices.js',
    'resources/js/legacy/chart/apex-chart/moment.min.js',
    'resources/js/legacy/notify/bootstrap-notify.min.js',
    // Theme Js.
    'resources/js/legacy/script.js',
    
  ])

</body>
</html>
