<?php

use Livewire\Component;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Invoice_Detail;
use App\Models\Customer;
use WireUi\Traits\WireUiActions;

new class extends Component
{
    use WireUiactions;
    

    public $search = '';

    public $user_id;

    public $cart = [];

    public $customers = [];

    public $customer_id;

    public $customer_name;

    public $invoice_id;

    public $print = False;

    public function mount($id = Null, $success = false)
    {
        $this->user_id = auth()->user()->id;
        $this->customers =  Customer::latest()->get(['id', 'name'])->toArray();
        if($id)
        {
            $this->edit($id);
        }elseif ($id = Null) {
            $this->reset([
            'invoice_id',
            'customer_id'
        ]);
        }
    }

    public function edit($id)
    {
        $this->invoice_id = $id;
        $invoice =  Invoice::findOrFail($this->invoice_id);
        $this->customer_id = $invoice->customer_id;

        // $invoice_details = Invoice::fincOrfail($this->invoice_id)->invoice_detail;

        foreach ($invoice->invoice_detail as $detail) {
            $this->cart[$detail->product_id] = [
            'id' => $detail->product_id,
            'name' => $detail->product?->name,
            'price' => $detail->subtotal / $detail->qty,
            'qty' => $detail->qty
        ];
        }
    }

   


    public function addToCart($productId)
    {
        $product = Product::findOrFail($productId);


        if(isset($this->cart[$productId]))
        {
            if($product->stock == $this->cart[$productId]['qty']){
                $this->notification()->send([
                    'icon' => 'warning',
                    'title' => 'Stok Kurang',
                    'description' => 'Stok Kurang'
                ]);
            }else {
                $this->cart[$productId]['qty']++;
            }
        }else {
            
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price-($product->price*$product->discount),
                'qty' => 1
            ];
        }
    }
    
     public function getTotal()
    {
        $total = 0;

        foreach ($this->cart as $item) {
            $total += $item['price']*$item['qty'];
            
        }

        return $total;
    }

    public function deleteFromCart($productId)
    {
        unset($this->cart[$productId]);
    }

    public function decreaseQty($productId)
    {

        if($this->cart[$productId]['qty'] > 1)
        {
            $this->cart[$productId]['qty']--;
        }
    }


    public function checkout($print = false)
    {
        $lastInvoice = Invoice::latest('id')->first();

        if($lastInvoice == Null){
            $no = 1;
        }else {
            $no = $lastInvoice->id + 1;
        }
        $invoice_no = 'INV-'. now()->format('Ymd-His').'-'.$no;




        $dataInvoice = [
            'invoice_no' => $invoice_no,
            'user_id' => $this->user_id,
            'customer_id' => $this->customer_id,
            'total' => $this->getTotal()
        ];

       if($this->invoice_id)
       {

        $invoice = Invoice::findOrFail($this->invoice_id);

        $invoice->update($dataInvoice);

        foreach ($invoice->invoice_detail as $detail) {
            $product = Product::findOrFail($detail['product_id']);

            $product->update(['stock' => $product->stock += $detail['qty'] ] );
        }

        $invoice->invoice_detail()->delete();  
       

       }else {

         Invoice::create($dataInvoice);

        }

        $lastInvoice = Invoice::latest('id')->first();

        foreach ($this->cart as $item) {
            $subtotal = $item['price']*$item['qty'];
           

            $dataInvoiceDetail = [
                'invoice_id' => $lastInvoice->id,
                'product_id' => $item['id'],
                'qty' => $item['qty'],
                'subtotal' => $subtotal
            ];

            $product = Product::findOrFail($item['id']);

            $qtyProduct = $product->stock - $item['qty'];

            $dataProduct = ['stock'=>$qtyProduct];

            $product->update($dataProduct);

            Invoice_Detail::create($dataInvoiceDetail);

        if($this->customer_id){
            $customer = Customer::findOrFail($this->customer_id);

            $points = $customer->points + 5;

            $customer->update(['points' => $points]);
        }

        
       }

       if($print == True)
       {
             $this->dispatch('print');
             $this->print = True;
       }else {
                $this->cart = [];

            $this->dispatch('close');

            

            if($this->invoice_id){
                $this->dialog()->confirm([
                    "title" => "Ingin Lihat Perubahan?",
                    "description" => "Ke Halaman Invoice",
                    "accept" => [ 'method' => 'toInvoice' ],
                    "reject" => ['method' => 'back']
                ]);
            }   

            $this->reset([
                'invoice_id',
                'customer_id',
                'print'
            ]);

       }

        $this->notification()->send([
            'icon' => 'success',
            'title' => 'Berhasil'
        ]);

    }

    public function closeModal()
    {
        if($this->print == True)
        {
            $this->cart = [];

            $this->reset([
                    'invoice_id',
                    'customer_id',
                    'print'
                ]);
        }

        $this->dispatch('close');

        


    }

    public function toInvoice()
    {
         return redirect()->route('invoice');
    }

     public function back()
    {
         return redirect()->route('cashier');
    }



    public function invoice()
    {

        
        if($this->customer_id)
        {
            $customer = Customer::find($this->customer_id);

            $this->customer_name = $customer->name;
        }

        $this->dispatch('open');
        if($this->cart == [])
            {
                $this->dispatch('close');
            }

    }


    public function render()
    {
        return $this->view([
            "products" =>  Product::query()->
            where(function ($query){
                $query->where('name', 'like', '%'.$this->search.'%')
                ->orwhere('description', 'like', '%'.$this->search.'%')
                ->orwhere('price', 'like', '%'.$this->search.'%')
                ->orwhere('discount', 'like', '%'.$this->search.'%');
            })->latest()->get()
        ])
        ->layout('layouts.app')
        ->title('Cashier');
    }
};
?>

<style>

</style>

<div 
x-on:open.window="$openModal('invoice')"
x-on:close.window="$closeModal('invoice')" 
x-on:print.window="window.print()"
>
    <div class="grid grid-cols-12  pb-15 md:pb-0">
        <div class="col-span-12 md:col-span-10 pb-80">

            <div class="flex w-full my-2 px-3 py-5 justify-center md:justify-end ">
                <div class=" w-full md:max-w-xl  ">
                    <x-input icon="magnifying-glass" wire:model.live="search" placeholder="Search..."  />
                </div>
            </div>

            <div class="grid md:grid-cols-6 grid-cols-2 gap-4  px-3 my-2 h-auto  ">
                @forelse ($products as $product)
                    <div class="flex flex-col shadow dark:bg-gray-800 rounded-md bg-stone-200">
                        <img src="{{asset('storage/products/'. $product->image)}}" class="w-full h-40 object-cover rounded-md" alt="">
                        <h1 class="text-md text-center mb-1 mt-2 font-semibold">{{$product->name}}</h1>
                        @if ($product->discount > 0)
                            <h1 class="text-center text-xs  line-through">Rp.{{number_format($product->price)}}</h1>
                            <h1 class="text-center text-md">Rp.{{number_format($product->price-($product->price*$product->discount))}}</h1>
                        @else
                            <h1 class="text-center text-md  ">Rp.{{number_format($product->price)}}</h1>
                        @endif
                            <div class="flex justify-between px-2 mt-3 mb-2 ">
                                @if ($product->discount > 0)
                                    <p class="text-gray-400 text-sm ">Discount: {{$product->discount*100}}%</p>
                                @endif
                                <p class="text-sm text-gray-400">Stock: {{$product->stock}}</p>
                                
                            </div>
                            <div class="w-full  p-2 mt-auto ">
                                <x-button icon="plus" class="w-full" :disabled="$product->stock == 0" wire:click="addToCart('{{$product->id}}')"/>
                            </div>
                    </div>
                @empty

                <x-alert warning title="Product Not Found" class="col-span-full" />

                @endforelse
            </div>
        </div>


        <div class="md:col-span-2 min-h-screen dark:bg-gray-900 bg-stone-200 md:block lg:block hidden overflow-y-auto">
             <div class="w-full mt-6  flex justify-center ">
                <x-avatar sm size="w-20 h-20" :src="asset('storage/users/'. auth()->user()->profile )"/>
             </div>
             <h1 class="my-1 text-3xl text-center">{{auth()->user()->name}}</h1>

             <h2 class="text-lg text-center my-5">Cart   </h2>
             @forelse ($cart as $item)
             <div class="flex justify-between dark:bg-gray-900 border-2 border-gray-600 rounded-xl p-2  mx-3 mb-2 ">
                 <div class="flex flex-col">
                    <h3 class="text-md">{{ $item['name']}}</h3>

                    <p class="text-sm text-gray-500">Rp.{{number_format($item['price'])}}</p>
                 </div>

                 <div class="flex space-x-1 py-1">
                    <x-button sm flat icon="plus" wire:click="addToCart('{{$item['id']}}')" />
                    <h1 class="twxt-lg my-auto">{{$item['qty']}}</h1>
                    <x-button sm flat icon="minus" wire:click="decreaseQty('{{$item['id']}}')" />
                      <div class="absout -mx-4  -my-4">
                    <x-mini-button rounded secondary icon="x-mark" class="w-0.5 h-0.5"   wire:click="deleteFromCart('{{$item['id']}}')"/>
                 </div>
                 </div>
                
             </div>

             
             @empty
                <div class="w-full p-3">
                     <x-alert info title="Cart Null" />
                </div>
             @endforelse

             @if ($this->cart != Null)
                 <div class="w-full flex flex-col spacey-2 justify-center px-3 h-auto">
                    <x-native-select
                    :options="$this->customers"
                    option-value="id"
                    option-label="name"
                    label="Customer"
                    wire:model="customer_id"
                    placeholder="Pilih Cutomer"
                     />
                    <h1 class="text-center text-lg">Total: Rp.{{number_format($this->getTotal())}}</h1>
                    <x-button label="Checkout"  wire:click="invoice" class="mx-auto my-3" />
                 </div>
             @endif
        </div>
    </div>

    <div class="fixed bottom-0 z-10 rounded-t-xl dark:bg-gray-900 w-full md:hidden">
         <div class="w-full mt-6  flex justify-center space-x-2 ">
            <div class="">
                 <x-avatar s :src="asset('storage/users/'. auth()->user()->profile )"/>
            </div>
               
                <h1 class="my-1 text-3xl text-center my-auto">{{auth()->user()->name}}</h1>
             </div>
             

             <h2 class="text-lg text-center mt-1">Cart   </h2>
             <div class="max-h-30 overflow-y-auto">
                @forelse ($cart as $item)
                <div class="flex justify-between dark:bg-gray-900 border-2 border-gray-600 rounded-xl p-2  mx-3 mb-2">
                    <div class="flex flex-col">
                        <h3 class="text-md">{{ $item['name']}}</h3>

                        <p class="text-sm text-gray-500">Rp.{{number_format($item['price'])}}</p>
                     </div>

                    <div class="flex space-x-1 py-1">
                        <x-button sm flat icon="plus" wire:click="addToCart('{{$item['id']}}')" />
                        <h1 class="twxt-lg my-auto">{{$item['qty']}}</h1>
                        <x-button sm flat icon="minus" wire:click="decreaseQty('{{$item['id']}}')" />
                        <div class="absout -mx-4  -my-4">
                            <x-mini-button rounded secondary icon="x-mark" class="w-0.5 h-0.5"   wire:click="deleteFromCart('{{$item['id']}}')"/>
                        </div>
                 </div>
                
             </div>

             
             @empty
                <div class="w-full p-3">
                     <x-alert info title="Cart Null" />
                </div>
             @endforelse

             @if ($this->cart != Null)
                 <div class="w-full flex flex-col spacey-2 justify-center px-3 h-auto">
                    <x-native-select
                    :options="$this->customers"
                    option-value="id"
                    option-label="name"
                    label="Customer"
                    wire:model="customer_id"
                    placeholder="Pilih Cutomer"
                     />
                    
                 </div>
             @endif
             
             </div>
             <div class="flex flex-col my-2 shadow-[35px_35px_35px_35px_rgba(0,0,0,0.25)] z-10">
                <h1 class="text-center text-lg">Total: Rp.{{number_format($this->getTotal())}}</h1>
                    <x-button label="Checkout"  wire:click="invoice" class="mx-auto my-3" />
             </div>
    </div>

    <x-modal-card name="invoice" persistent >
        
        <x-slot name="title" class="w-full">
        <div class="flex w-full justify-end">
            <x-mini-button rounded  icon="x-mark" wire:click="closeModal" />
        </div>
    </x-slot>
    <section id="invoice">
    <div class="flex justify-center flex-col my-3">
        <h1 class="text-xl font-bold text-center">Waduh Coffe</h1>
        <h1 class="text-xl font-bold text-center">Jl.Rongawi-Suki 001/002</h1>
        <h1 class="text-xl font-bold text-center">Kec. Anti Suki Kota Ngawi 6969</h1>
    </div>
        <div class="overflow-y-auto w-full px-4  py-2 mt-8">
            <div class="flex flex-col border-dashed py-2 border-b border-t ">
                <h1 class="text-sm"><span class="inline-block w-20">Date</span>:      {{ now()->format('Y-m-d, H:i') }}</h1>
                <h1 class="text-sm"><span class="inline-block w-20">Cashier</span>:   {{auth()->user()->name}}</h1>
                <h1 class="text-sm"><span class="inline-block w-20">Customer</span>:  {{$this->customer_name}}</h1>
            </div>
            <div class="border-dashed border-b py-2">

            
            @forelse ($cart as $item)
                <div class="flex flex-col ">
                    <div class="">
                        <h1 class="text-sm font-semibold">{{ $item['name']}}</h1>
                        <div class="flex justify-between w-full">
                                <h1 class="text-sm">{{ $item['qty']}} x {{$item['price']}}</h1>
                                <h1 class="text-sm">Rp.{{ number_format($item['price']*$item['qty']) }}</h1>
                        </div>
                    </div>
                </div>
            @empty
                
            @endforelse
            </div>

            <div class="flex justify-end py-2 border-b border-dashed">
                <h1 class="text-sm font-semibold">Total: Rp.{{number_format($this->getTotal())}}</h1>
            </div>

            
        </div>
    </section>
        <x-slot name="footer">
            <div class="flex space-x-2 my-2 print:hidden">
                @if ($this->print == True)
                    <x-button label="Print" icon="printer" x-on:click="$dispatch('print')"  class="w-full" />
                @else
                    <x-button label="Checkout & Print" wire:click="checkout({{$print = true}})" icon="printer" flat class="w-full"/>
                    <x-button label="Checkout" wire:click="checkout"  class="w-full" />
                @endif
            </div>
        </x-slot>    
    </x-modal-card>
    {{-- The only way to do great work is to love what you do. - Steve Jobs --}}
</div>