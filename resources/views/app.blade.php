<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0B0C0F">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0B0C0F">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/pwa/icon-512.jpg">
    <link rel="mask-icon" href="/pwa/icon-192.jpg" color="#0B0C0F">
    @inertiaHead
    <link href="{{ mix('css/app.css') }}" rel="stylesheet" />
    <style>
      :root { --glint-black: #000; --glint-white: #fff; --nav-h: 70px; }
      body { margin:0; padding-top: var(--banner-offset, 0); transition: padding-top .2s ease; }
    </style>
    {{-- Title intentionally omitted for troubleshooting white screen issue --}}
    <link rel="icon" type="image/jpeg" href="https://cdn.shopify.com/s/files/1/0820/3947/2469/files/glint-favicon-black.jpg?v=1762130152" />
    <script data-cfasync="false" defer src="{{ mix('js/app.js') }}"></script>
</head>
<body>
@inertia
</body>
</html>
