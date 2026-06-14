<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Adopta') }}</title>
        <meta name="description" content="Plataforma tecnológica del Ecosistema Integral de Rescate y Adopción Animal. Conectamos de forma eficiente y segura a adoptantes con mascotas para una tenencia responsable.">
        <meta name="robots" content="index, follow">
        <meta name="keywords" content="adopcion de mascotas, rescate animal, tenencia responsable, bienestar animal, buscar mascotas, fundaciones de animales">

        @php
            $ogTitle = "Adopta - Ecosistema de Bienestar Animal";
            $ogDescription = "Conectando de forma eficiente y segura a adoptantes con mascotas para fomentar la tenencia responsable.";
            $ogImage = asset('images/og-image.jpg');

            if (request()->routeIs('pets.story')) {
                $petParam = request()->route('pet');
                $pet = null;
                if ($petParam instanceof \App\Models\Pet) {
                    $pet = $petParam;
                } elseif ($petParam) {
                    $pet = \App\Models\Pet::find($petParam);
                }
                
                if ($pet) {
                    $ogTitle = "La Nueva Vida de " . $pet->name . " 💖";
                    $ogDescription = $pet->description ?: "Sigue de cerca la historia de adaptación y finales felices de " . $pet->name . " en su nuevo hogar.";
                    if ($pet->photo_path) {
                        $ogImage = asset($pet->photo_path);
                    }
                }
            }
        @endphp

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDescription }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ $ogImage }}">

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:title" content="{{ $ogTitle }}">
        <meta property="twitter:description" content="{{ $ogDescription }}">
        <meta property="twitter:image" content="{{ $ogImage }}">

        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
        
        <!-- PWA Manifest & Meta Tags -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#ff6b4a">
        <link rel="apple-touch-icon" href="{{ asset('images/pwa-icon.png') }}">
    </head>
    <body class="antialiased">
        @inertia

        <!-- PWA Service Worker Registration -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('{{ asset("sw.js") }}')
                        .then(reg => console.log('Service Worker registered successfully!', reg.scope))
                        .catch(err => console.error('Service Worker registration failed:', err));
                });
            }
        </script>
    </body>
</html>
