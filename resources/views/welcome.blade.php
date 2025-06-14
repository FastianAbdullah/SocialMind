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
    
    <!-- Preload CDN assets -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style" crossorigin>
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" as="style" crossorigin>
    
    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/fonts/ff-1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fonts/ff-3.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fonts/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/plugins.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.min.css') }}">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin>
    
    <!-- GLightbox CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" crossorigin>
    
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/js/app.js', 'resources/js/legacy/app.js'])
</head>
<body class="bg-black body-clip light">
    <div id="app"></div>
    
    <!-- Add jQuery first -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- GLightbox JS -->
    <script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js" crossorigin defer></script>
    
    <script>
        // Initialize GLightbox after everything loads
        window.addEventListener('load', function() {
            if (typeof GLightbox !== 'undefined') {
                const lightbox = GLightbox({
                    selector: '[data-glightbox]',
                    touchNavigation: true,
                    loop: true
                });
            }
        });
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                const addressDiv = document.getElementById('addressDiv');
                const originalText = addressDiv.innerHTML;
                addressDiv.innerHTML = 'Copied!';
                addressDiv.style.background = 'rgba(34, 197, 94, 0.8)';
                
                setTimeout(function() {
                addressDiv.innerHTML = originalText;
                addressDiv.style.background = 'rgba(255, 255, 255, 0.1)';
                }, 1500);
            });
}
    </script>
</body>
</html>