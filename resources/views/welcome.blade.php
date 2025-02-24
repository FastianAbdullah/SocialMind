{{-- <!DOCTYPE html> --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Modern AI Landing HTML Template">
  <meta name="keywords" content="bootstrap 5, saas, landing page">
  <meta name="author" content="Themetags">
  <title>Social Mind - Modern AI Landing HTML Template</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body class="bg-black body-clip dark-only">
  <div id="app"></div>
  @vite([
    'resources/js/app.js',
    'resources/js/legacy/app.js', // Well lets just keep it here for now, because its working and does not conflict with others.   
  ])

</body>
</html>
