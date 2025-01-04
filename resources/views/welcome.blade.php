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
  <link rel="stylesheet" href="/css/fonts/ff-1.css">
  <link rel="stylesheet" href="/css/fonts/ff-3.css">
  <link rel="stylesheet" href="/css/fonts/bootstrap-icons.css">
  <link rel="icon" href="/img/favicon.png" type="image/png">
  <link rel="stylesheet" href="/css/plugins.min.css">
  <link rel="stylesheet" href="/css/style.min.css">

  <!-- DashBoard CSS> -->
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
    <!-- Range slider css-->
    <link rel="stylesheet" type="text/css" href="/css/vendors/rangeslider/rSlider.min.css">
    <link rel="stylesheet" type="text/css" href="/css/vendors/animate.css">
    <link rel="stylesheet" type="text/css" href="/css/vendors/prism.css">
    <link rel="stylesheet" type="text/css" href="/css/vendors/fullcalender.css">
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="/css/vendors/bootstrap.css">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <link id="color" rel="stylesheet" href="/css/color-1.css" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="/css/responsive.css">
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
    'resources/js/legacy/jquery.min.js',
    'resources/js/legacy/bootstrap/bootstrap.bundle.min.js',
    'resources/js/legacy/icons/feather-icon/feather.min.js',
    'resources/js/legacy/icons/feather-icon/feather-icon.js',
    'resources/js/legacy/scrollbar/simplebar.js',
    'resources/js/legacy/scrollbar/custom.js',
    'resources/js/legacy/config.js',
    'resources/js/legacy/sidebar-menu.js',
    'resources/js/legacy/sidebar-pin.js',
    'resources/js/legacy/slick/slick.min.js',
    'resources/js/legacy/slick/slick.js',
    'resources/js/legacy/header-slick.js',
    'resources/js/legacy/chart/apex-chart/apex-chart.js',
    'resources/js/legacy/chart/apex-chart/stock-prices.js',
    'resources/js/legacy/range-slider/rSlider.min.js',
    'resources/js/legacy/rangeslider/rangeslider.js',
    'resources/js/legacy/prism/prism.min.js',
    'resources/js/legacy/clipboard/clipboard.min.js',
    'resources/js/legacy/counter/jquery.waypoints.min.js',
    'resources/js/legacy/counter/jquery.counterup.min.js',
    'resources/js/legacy/counter/counter-custom.js',
    'resources/js/legacy/custom-card/custom-card.js',
    'resources/js/legacy/calendar/fullcalender.js',
    'resources/js/legacy/calendar/custom-calendar.js',
    'resources/js/legacy/dashboard/dashboard_2.js',
    'resources/js/legacy/animation/wow/wow.min.js',
    'resources/js/legacy/script.js',
    'resources/js/legacy/theme-customizer/customizer.js'
    
  ])

</body>
</html>
