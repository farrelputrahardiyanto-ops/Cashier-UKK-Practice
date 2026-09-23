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

<div>
    <nav class="dark:bg-gray-800 dark:text-gray-200 bg-stone-300 shadow-md py-2">
        <div class="w-full p-3 flex justify-between">

            <div class=" my-auto flex justify-between">
                <a href=""><h1 class="my-auto text-lg">Halo Rex</h1></a>

                <x-icon name="bars-3" class="md:hidden" />
            </div>

            <div class="flex flex-row space-x-3 my-auto ">
                <a href="{{route('cashier')}}" >Cashier</a>
                <a href="{{route('users')}}" @if (auth()->user()->role == 'cashier' ) hidden @endif>Users</a>
                <a href="{{route('product')}}"  @if (auth()->user()->role == 'cashier' ) hidden @endif>Products</a>
                <a href="{{route('customers')}}"  @if (auth()->user()->role == 'cashier' ) hidden @endif>Customers</a>
                <a href="{{route('invoice')}}">Invoice</a>
                <a href="{{route('invoice_detail')}}">Invoice Detail</a>
            </div>

            <div class="flex space-x-3">
               
                <x-button negative x-on:confirm="{
                icon: 'warning',
                title: 'Yakin?',
                description: 'Yakin ingin Logout?',
                method: 'logout'
                }" label="Logout" />
            </div>
        </div>
    </nav>
    {{-- Order your soul. Reduce your wants. - Augustine --}}
</div>