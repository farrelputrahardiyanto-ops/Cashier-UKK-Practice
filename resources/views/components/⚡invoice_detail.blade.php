<?php

use Livewire\Component;
use App\Models\Invoice_Detail;
use App\Models\Invoice;

new class extends Component
{

    public $search='';
    //

    public function edit($id)
    {
        return redirect()->route('cashier_edit', $id);
    }

    public function render()
    {
        $query = Invoice_Detail::query();

        if(auth()->user()->role == 'cashier')
        {
            $invoices = Invoice::latest()->where('user_id', auth()->user()->id)->get();
            $query->whereIn('invoice_id', $invoices->pluck('id') );
        }

        $invoice_detail = $query
         ->where(function ($query){
                $query->where('invoice_id', 'like', '%'.$this->search.'%')
                ->orwhere('subtotal', 'like', '%'.$this->search.'%')
                ->orwhere('created_at', 'like', '%'.$this->search.'%')
                ->orWhereHas('product', function ($query){
                    $query->where('name', 'like', '%'.$this->search.'%'); 
                });
            })->latest()->paginate(5);


        return $this->view(["invoice_detail" => $invoice_detail])
        ->layout('layouts.app')
        ->title('Invoice Detail');
    }
};
?>

<div>

    <div class="max-w-6xl mx-auto my-4 p-3">
        <div class="flex justify-between my-3">
            <x-button label="Create" icon="plus" />

            <div class="max-w-2xl">
                <x-input icon="magnifying-glass" placeholder="Search..." wire:model.live="search" />
            </div>
        </div>

        <x-card>
            <x-slot name="title"><h1 class="text-lg">Invoice Detail</h1></x-slot>
            <div class="overflow-x-auto min-w-full">
                <table class="min-w-full text-left">
                    <thead>
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Invoice ID</th>
                            <th class="p-3">Product</th>
                            <th class="p-3">Qty</th>
                            <th class="p-3">Subtotal</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoice_detail as $detail)
                            <tr>
                                <td class="p-3">{{$detail->id}}</td>
                                <td class="p-3">{{$detail->invoice_id}}</td>
                                <td class="p-3">{{$detail->product?->name}}</td>
                                <td class="p-3">{{$detail->qty}}</td>
                                <td class="p-3">Rp.{{number_format($detail->subtotal)}}</td>
                                <td class="p-3">{{$detail->created_at}}</td>
                                <td class="p-3">
                                    <div class="flex space-x-2">
                                        <x-button warning icon="pencil" wire:click="edit({{$detail->invoice_id}})" />
                                
                                    </div>
                                </td>
                            </tr>
                        @empty
                        <x-alert warning title="Data Null" />
                            
                        @endforelse
                    </tbody>
                </table>

                <div class="flex justify-end my-5">
                    {{$invoice_detail->links()}}
                </div>
            </div>
        </x-card>
    </div>
    {{-- Live as if you were to die tomorrow. Learn as if you were to live forever. - Mahatma Gandhi --}}
</div>