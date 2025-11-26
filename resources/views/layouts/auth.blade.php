<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', 'Glint Labs')</title>
  <meta name="description" content="@yield('meta_description', 'Sign in or create your Glint Labs account.')">

  <link rel="icon" type="image/jpeg" href="https://cdn.shopify.com/s/files/1/0820/3947/2469/files/glint-favicon-black.jpg?v=1762130152" />

  @stack('head')
</head>
<body>
  @yield('content')
  @stack('scripts')
</body>
</html>

