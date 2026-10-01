<?php

use Livewire\Component;
use App\Models\Invoice;
use App\Models\Invoice_Detail;
use Carbon\Carbon;

new class extends Component
{

    public $month_option = [
    ['name' => 'January', 'id' => '01'],
    ['name' => 'February', 'id' => '02'],
    ['name' => 'March', 'id' => '03'],
    ['name' => 'April', 'id' => '04'],
    ['name' => 'May', 'id' => '05'],
    ['name' => 'June', 'id' => '06'],
    ['name' => 'July', 'id' => '07'],
    ['name' => 'August', 'id' => '08'],
    ['name' => 'September', 'id' => '09'],
    ['name' => 'October', 'id' => '10'],
    ['name' => 'November', 'id' => '11'],
    ['name' => 'December', 'id' => '12'],
];


    public $search = '';

    public $month_now;

    public $month_number;

    public $startDate;

    public $endDate;

    public $total_payment;

    public $total_transaction;

    public $total_product;

    public $best_seller;

    public $best_cashier;

    public $avg_total;

    public $month_name;

    
    public function mount()
    {
        $this->month_now = now()->format('m');

        

        $this->updateReport();          

        
    }

    public function updateReport()
    {   
        $this->month_number = $this->month_now;
        $this->month_name = collect($this->month_option)
         ->firstWhere('id', $this->month_now)['name'];


        $this->startDate = Carbon::create(
            now()->year,
            $this->month_now,
            1
        )->startOfMonth();

        $this->endDate = Carbon::create(
            now()->year,
            $this->month_now,
            1
        )->endOfMonth();

        // reset
        $this->total_payment = 0;
        $this->total_transaction = 0;
        $this->total_product = 0;

        $invoices = Invoice::whereBetween('created_at', [$this->startDate, $this->endDate])->get();

        foreach($invoices as $invoice){
            $this->total_payment += $invoice->total;
            $this->total_transaction++;
        }

        $invoice_detail = Invoice_Detail::whereBetween('created_at', [$this->startDate, $this->endDate])->get();

        foreach($invoice_detail as $detail){
            $this->total_product += $detail->qty;
        }

        $this->best_seller = Invoice_Detail::whereBetween('created_at', [$this->startDate, $this->endDate])
        ->select('product_id')
        ->selectRaw('SUM(qty) as total_qty')
        ->groupBy('product_id')
        ->orderByDesc('total_qty')
        ->first();

        $this->best_cashier = Invoice::whereBetween('created_at', [$this->startDate, $this->endDate])
        ->select('user_id')
        ->selectRaw('COUNT(id) as total')
        ->groupBy('user_id')
        ->orderByDesc('total')
        ->first();

        $this->avg_total = Invoice::whereBetween('created_at', [$this->startDate, $this->endDate])
        ->selectRaw('AVG(total) as avg')
        ->first();

    // dst...
    }



    public function updatedMonthNow()
    {
    
        $this->updateReport();
    }



    public function render()
    {
        return $this->view()
        ->layout('layouts.app')
        ->title('Report');
    }
};
?>

<div>
    
    <div class="max-w-6xl p-4 mx-auto my-4 overflow-x-auto">
        <h1 class="text-3xl py-5 font-semibold">Transaction Report {{$this->month_name}}</h1>
        <div class="flex justify-end">
            <div class="max-w-2xl">
                
              

                <x-native-select
                :options="$this->month_option"
                option-label="name"
                option-value="id"
                wire:model.live.change="month_now" />

            </div>
        </div>

        @if ($this->best_seller == null)

            <x-alert warning title="Data Null" class="my-3"/>

        @else    
            <div class="grid grid-cols-2 mt-4 gap-2">

            <div class="col-span-2 md:col-span-1 flex justify-between   bg-gray-500 rounded-md w-full ">
            <div class="flex justify-start ">
                <img src="{{asset('storage/products/'. $this->best_seller->product->image) }}" alt="iamge" class="h-full w-40 object-cover rounded-md">
            </div>
            <div class="w-full flex flex-col space-y-2 py-3 my-2">
                <h1 class="text-lg font-semibold text-center ">Best Seller</h1>
                <h1 class="text-3xl font-semibold text-center ">{{$this->best_seller->product->name}}</h1>
                <p class="text-3xl font-semibold text-center">Purchases: {{$this->best_seller->total_qty}}</p>
            </div>
        </div>

        <div class="col-span-2 md:col-span-1 flex justify-between  bg-gray-500 rounded-md w-full ">
            <div class="flex justify-start ">
                <img src="{{asset('storage/users/'. $this->best_cashier->user->profile) }}" alt="iamge" class="h-full w-40 object-cover rounded-md">
            </div>
            <div class="w-full flex flex-col my-2 py-3 space-y-2">
                <h1 class="text-lg font-semibold text-center ">Best Cashier</h1>
                <h1 class="text-3xl font-semibold text-center">{{$this->best_cashier->user->name}}</h1>
                <p class="text-3xl font-semibold text-center ">Transaction: {{$this->best_cashier->total}}</p>
            </div>
        </div>  
        </div>

        <div class="grid grid-cols-4 gap-2 w-full my-2">
            <div class="col-span-2 md:col-span-1 flex flex-col bg-gray-500 w-200 rounded-md w-full py-3">
                <h1 class="text-lg font-semibold text-center my-1">Total Payment</h1>
                <h1 class="text-2xl my-2 font-semibold text-center">Rp.{{number_format($this->total_payment)}}</h1>
            </div>
            <div class="col-span-2 md:col-span-1 flex flex-col bg-gray-500 w-200 rounded-md w-full py-3">
                <h1 class="text-lg font-semibold text-center my-1">Total Transaction</h1>
                <h1 class="text-2xl my-2 font-semibold text-center">{{$this->total_transaction}}</h1>
            </div>
            <div class="col-span-2 md:col-span-1 flex flex-col bg-gray-500 w-200 rounded-md w-full py-3">
                <h1 class="text-lg font-semibold text-center my-1">Product Sold</h1>
                <h1 class="text-2xl my-2 font-semibold text-center">{{$this->total_product}}</h1>
            </div>
             <div class="col-span-2 md:col-span-1 flex flex-col bg-gray-500 w-200 rounded-md w-full py-3">
                <h1 class="text-lg font-semibold text-center my-1">Average Invoice</h1>
                <h1 class="text-2xl my-2 font-semibold text-center">Rp.{{number_format($this->avg_total->avg)}}</h1>
            </div>
            
        </div>
        @endif

        

        

    </div>
    {{-- Let all your things have their places; let each part of your business have its time. - Benjamin Franklin --}}
</div>