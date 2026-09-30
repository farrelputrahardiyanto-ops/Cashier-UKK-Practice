<?php

use Livewire\Component;
use WireUi\Traits\WireUiActions;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WireUiActions;
    
    public function logout()
    {
        Auth::logout();

        return redirect()->route('login');
    }
};
?>
<div x-data="{ open: false }">

    <nav class="dark:bg-gray-800 dark:text-gray-200 bg-stone-300 shadow-md py-2">

        <div class="w-full p-3 md:flex md:justify-between md:items-center">

            {{-- Logo / Nama --}}
            <div class="flex justify-between items-center">

                <a href="">
                    <h1 class="text-2xl font-semibold">Waduh Coffe</h1>
                </a>

                {{-- Hamburger --}}
                <button
                    class="md:hidden"
                    x-on:click="open = !open"
                >
                    <x-icon name="bars-3" class="w-6 h-6" />
                </button>

            </div>


            {{-- Navigation --}}
            <div
                class="mt-3 md:mt-0 md:block"
                :class="{ 'hidden': !open }"
            >

                <div class="flex flex-col md:flex-row items-start md:items-center gap-3">

                    <a href="{{ route('cashier') }}">
                        Cashier
                    </a>

                    @if (auth()->user()->role != 'cashier')
                        <a href="{{ route('users') }}">
                            Users
                        </a>

                        <a href="{{ route('product') }}">
                            Products
                        </a>

                        <a href="{{ route('customers') }}">
                            Customers
                        </a>
                    @endif

                    <a href="{{ route('invoice') }}">
                        Invoice
                    </a>

                    <a href="{{ route('invoice_detail') }}">
                        Invoice Detail
                    </a>

                    @if (auth()->user()->role != 'cashier')
                        <a href="{{ route('report') }}">
                            Report
                        </a>
                    @endif

                    <x-button
                        negative
                        x-on:confirm="{
                            icon: 'warning',
                            title: 'Yakin?',
                            description: 'Yakin ingin Logout?',
                            method: 'logout'
                        }"
                        label="Logout"
                    />

                </div>

            </div>

        </div>

    </nav>

</div>