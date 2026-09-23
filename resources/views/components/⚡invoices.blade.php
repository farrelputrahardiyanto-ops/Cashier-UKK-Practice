<?php

use Livewire\Component;
use App\Models\Invoice;
use App\Models\Invoice_Detail;
use App\Models\Product;
use WireUi\Traits\WireUiActions;
use App\Models\User;
use App\Models\Customer;

new class extends Component
{
    use WireUiActions;

    public $search='';

    public $customer_name;

    public $cashier_name;

    public $invoice_detail = [];

    public $total;

    public $date;



    public function print($id)
    {
        $invoice = Invoice::findOrFail($id);

        $this->date = $invoice->created_at;

        $this->invoice_detail = Invoice_Detail::latest()->where('invoice_id', $id)->get();

        $this->customer_name = $invoice->customer?->name; 

        $this->cashier_name = $invoice->user?->name;

        $this->total = $invoice->total;
        
        $this->dispatch('open_invoice');
    }
   



    public function edit($id)
    {
        return redirect()->route('cashier_edit', ["id"=> $id]);
    }

    public function delete($id)
    {
        $invoice = Invoice::findOrFail($id);

        foreach ($invoice->invoice_detail as  $item) {
            $product = Product::findOrFail($item['product_id']);
            $product->update(['stock' => $product->stock += $item['qty']]);

        }


        $invoice->invoice_detail()->delete();
        $invoice->delete($id);

         $this->notification()->send([
            'icon' => 'success',
            'title' => 'Berhasil'
        ]);


    }

    public function render()
    {

        $query = Invoice::query();

        if(auth()->user()->role == 'cashier')
        {
            $query->where('user_id', auth()->user()->id);
        }
        
            $invoices = $query
            ->where(function ($query){
                $query->where('invoice_no', 'like', '%'.$this->search.'%')
                ->orwhere('total', 'like', '%'.$this->search.'%')
                ->orwhere('created_at', 'like', '%'.$this->search.'%')
                ->orWhereHas('user', function ($query){
                    $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('customer', function($query){
                    $query->where('name', 'like', '%'.$this->search.'%');
                });
            })->latest()->paginate(5);
       
        return $this->view(["invoices" => $invoices])
        ->layout('layouts.app')
        ->title('Invoice');
    }
};
?>

<div
x-on:open_invoice.window="$openModal('invoice')"
>

    <div class="max-w-6xl mx-auto my-4 p-3">
        <div class="flex justify-between my-3">
            <x-button label="Create" icon="plus" />

            <div class="max-w-2xl">
                <x-input icon="magnifying-glass" placeholder="Search..." wire:model.live="search" />
            </div>
        </div>

        <x-card>
            <x-slot name="title"><h1 class="text-lg">Invoice</h1></x-slot>
            <div class="overflow-x-auto min-w-full">
                <table class="min-w-full text-left">
                    <thead>
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Invoice No</th>
                            <th class="p-3">Cashier</th>
                            <th class="p-3">Customer</th>
                            <th class="p-3">Total</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td class="p-3">{{$invoice->id}}</td>
                                <td class="p-3">{{$invoice->invoice_no}}</td>
                                <td class="p-3">{{$invoice->user?->name}}</td>
                                <td class="p-3">{{$invoice->customer?->name}}</td>
                                <td class="p-3">Rp.{{number_format($invoice->total)}}</td>
                                <td class="p-3">{{$invoice->created_at}}</td>
                                <td class="p-3">
                                    <div class="flex space-x-2">
                                        <x-button icon="printer" wire:click="print({{$invoice->id}})" />
                                        <x-button warning icon="pencil" wire:click="edit({{$invoice->id}})" />
                                        <x-button negative icon="trash" x-on:confirm="{
                                        icon: 'warning',
                                        title: 'Yakin Ingin Hapus data ini?',
                                        description: 'Data akan terhapus selamanya',
                                        method: 'delete',
                                        params: {{$invoice->id}}
                                        }" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                        <x-alert warning title="Data Null" />
                            
                        @endforelse
                    </tbody>
                </table>

                <div class="flex justify-end my-5">
                    {{$invoices->links()}}
                </div>
            </div>
        </x-card>
    </div>

    <x-modal-card name="invoice">
    <x-slot name="title"><h1 class="text-2xl text-center my-2"></h1></x-slot>
    <section id="invoice">
    <div class="flex justify-center flex-col my-3">
        <h1 class="text-xl font-bold text-center">Waduh Coffe</h1>
        <h1 class="text-xl font-bold text-center">Jl.Rongawi-Suki 001/002</h1>
        <h1 class="text-xl font-bold text-center">Kec. Anti Suki Kota Ngawi 6969</h1>
    </div>
        <div class="overflow-y-auto w-full px-4  py-2 mt-8">
            <div class="flex flex-col border-dashed py-2 border-b border-t ">
                <h1 class="text-sm"><span class="inline-block w-20">Date</span>:      {{ $this->date }}</h1>
                <h1 class="text-sm"><span class="inline-block w-20">Cashier</span>:   {{$this->cashier_name}}</h1>
                <h1 class="text-sm"><span class="inline-block w-20">Customer</span>:  {{$this->customer_name}}</h1>
            </div>
            <div class="border-dashed border-b py-2">

            
                 
            
                @forelse ($this->invoice_detail as $detail)
                    <div class="flex flex-col ">
                        <div class="">
                            <h1 class="text-sm font-semibold">{{ $detail->product['name']}}</h1>
                            <div class="flex justify-between w-full">
                                    <h1 class="text-sm">{{ $detail['qty']}} x Rp.{{number_format($detail->product['price'])}}</h1>
                                    <h1 class="text-sm">Rp.{{ number_format($detail['subtotal']) }}</h1>
                            </div>
                        </div>
                    </div>
                @empty
                    
                @endforelse
             
            </div>
                
                    
            <div class="flex justify-end py-2 border-b border-dashed">
                <h1 class="text-sm font-semibold">Total: Rp.{{number_format($this->total)}}</h1>
            </div>
        

            
        </div>
    </section>
        <x-slot name="footer">
            <div class="flex space-x-2 my-2 print:hidden">
                <x-button label="Print" onclick="window.print()" icon="printer" class="w-full"/>
            </div>
        </x-slot>    
    </x-modal-card>
    {{-- Live as if you were to die tomorrow. Learn as if you were to live forever. - Mahatma Gandhi --}}
</div>