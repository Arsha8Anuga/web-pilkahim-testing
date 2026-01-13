<div class="space-y-4">
  <input wire:model.defer="nim" type="text" class="input input-bordered w-full" placeholder="NIM" />
  <input wire:model.defer="password" type="password" class="input input-bordered w-full" placeholder="Password" />

  <button wire:click="auth" class="btn btn-primary w-full mt-2">
    Login
  </button>

</div>
