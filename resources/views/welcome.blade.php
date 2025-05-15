<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Modern AI Landing HTML Template">
    <meta name="keywords" content="bootstrap 5, saas, landing page">
    <meta name="author" content="Themetags">
    <title>Social Mind - Modern AI Landing HTML Template</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Font Awesome (loaded from CDN as it's more efficient) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- GLightbox -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
    <script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
    
    @vite([ 'resources/js/app.js', 'resources/js/legacy/app.js'])
</head>
<body class="bg-black body-clip light">
    <div id="app"></div>
    
    <script>
        // Initialize GLightbox after it's loaded
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof GLightbox !== 'undefined') {
                const lightbox = GLightbox();
            }
        });
    </script>
</body>
</html> 