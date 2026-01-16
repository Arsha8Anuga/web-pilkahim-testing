<?php

namespace App\Livewire\Auth;

use App\Enums\AuditLogAction;
use App\Enums\AuditLogResult;
use App\Helper\AuditLogger;
use App\Service\Auth\AuthService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class Login extends Component
{

    #[Title('login')]
    
    #[Layout('components.layouts.auth')]

    public string $nim = '';
    public string $password = '';

    protected $rules = [
        'nim' => 'required|string|regex:/^[0-9]{9}$/',
        'password' => 'required|string'
    ];

    public function auth(AuthService $service){
        $data = $this->validate();

        if (!$service->login($data['nim'], $data['password'])) {
            Toaster::error('NIM atau Password Salah!!');
            return;
        }

        Toaster::success("Semangat, Berjuang, Sukses!! 👏🏼👏🏼👏🏼");
        return redirect()->intended(route('dahboard'));
    }


    public function render()
    {
        return view('livewire.auth.login');
    }
}
