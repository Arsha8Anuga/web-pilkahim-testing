<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>
    {{ $title ? $title . ' - ' . config('app.name') : config('app.name') }}
</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

  <!-- Styles -->
  @vite('resources/css/app.css')
  @livewireStyles
</head>

<body>

  {{ $slot }}
  
  <x-toaster-hub wire:ignore /> 

  {{-- Scripts --}}
  @vite('resources/js/app.js')
  @livewireScripts
</body>
</html>
