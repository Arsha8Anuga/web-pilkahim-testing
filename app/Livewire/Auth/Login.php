<?php

namespace App\Livewire\Auth;

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

    public function auth(){ 
        
        $credentials = $this->validate();

        if(!Auth::attempt($credentials)){
            
            Toaster::error('NIM atau Password Salah!!');
            return;

        }

        session()->regenerate();

        Toaster::success("Semangat, Berjuang, Sukses!! 👏🏼👏🏼👏🏼");

        return redirect()->route("dahboard");
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
