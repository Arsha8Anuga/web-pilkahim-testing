<x-layouts.app :title="$title ?? 'Auth'">
  <div class="min-h-screen flex items-center justify-center bg-base-200">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
      <div class="card-body">

          <h2 class="card-title justify-center">
              {{ $title }}
          </h2>

          {{ $slot }}

      </div>
    </div>
  </div>
</x-layouts.app>