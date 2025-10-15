<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

   <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
  
  @vite('resources/js/app.js')
  @inertiaHead
</head>
<body class="antialiased">
  @inertia
  @routes
</body>
</html>
