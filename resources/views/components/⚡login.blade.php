<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    

    public $email;

    public $password;

    public $rememberme=  false;


    public function login()
    {
        $rememberme = $this->rememberme;    
        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ];

        $this->validate($rules);

        $credentials = [
            'email' => $this->email,
            'password' => $this->password
        ];

        if(Auth::attempt($credentials, $rememberme)){
            session()->regenerate();

            return redirect()->route('cashier');
        }else{

        }
    }


    public function render()
    {
        return $this->view()
        ->layout('layouts.login')
        ->title('Login');
    }
};
?>

<div class="">

<div class="min-h-screen min-w-screen  flex items-center">
        <div class="w-full mx-auto px-3 max-w-xl">
        <x-card class="p-4">
            <div class="mb-7 mt-2">
                <h1 class="text-3xl text-center my-2 font-semibold">Login</h1>
                <h1 class="text-3xl text-center my-2 font-semibold">Walah Coffe Cashier</h1>

            </div>
            <div class="flex flex-col space-y-3">
                <x-input label="Email" wire:model="email" />
                <x-password label="Password" wire:model="password" />
                <div class="flex justify-end my-3">
                    <x-checkbox left-label="Remember Me" wire:model="rememberme" />
                </div>
            </div>
            <div class="w-full p-2 m-2">
                <x-button label="Login" wire:click="login" class="w-full" />
            </div>
        </x-card>
    </div>
</div>
    {{-- Walk as if you are kissing the Earth with your feet. - Thich Nhat Hanh --}}
</div>