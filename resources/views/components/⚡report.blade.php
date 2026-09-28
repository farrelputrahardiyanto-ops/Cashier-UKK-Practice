<?php

use Livewire\Component;
use App\Models\Invoice;
use App\Models\Invoice_Detail;
use Carbon\Carbon;

new class extends Component
{

    public $month_option = [
        '01' => "January",
        '02' => "February",
        '03' => "March",
        '04' => "April",
        '05' => 'May',
        '06' => "June",
        '07' => "July",
        '08' => "August",
        "09" => "September",
        '10' => "October",
        '11' => "November",
        '12' => 'December'

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

    
    public function mount()
    {
        $this->month_now = now()->format('F');

        $this->month_number = date('m', strtotime($this->month_now));

        $this->startDate =  Carbon::create(now()->year, $this->month_number, 1)->startOfMonth();
        $this->endDate = Carbon::create(now()->year, $this->month_number, 1)->endOFMonth();

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
    
    <div class="max-w-6xl p-4 mx-auto my-4">
        <h1 class="text-3xl py-5 font-semibold">Transaction Report {{$this->month_now}}</h1>
        <div class="flex justify-between">
            <div class="max-w-2xl">
                <x-native-select :options="$this->month_option" wire:model.live="month_now"  />
            </div>
            <div class="max-w-2xl">
                <x-input placeholder="Search..." icon="magnifying-glass" class="max-w-2xl" wire:model.live="search"/>
            </div>
        </div>

        <div class="flex justify-evenly space-x-3 w-full my-3">
            <div class="flex flex-col bg-gray-500 w-200 rounded-md w-full py-3">
                <h1 class="text-lg font-semibold text-center my-1">Total Payment</h1>
                <h1 class="text-2xl my-2 font-semibold text-center">Rp.{{number_format($this->total_payment)}}</h1>
            </div>
            <div class="flex flex-col bg-gray-500 w-200 rounded-md w-full py-3">
                <h1 class="text-lg font-semibold text-center my-1">Total Transaction</h1>
                <h1 class="text-2xl my-2 font-semibold text-center">{{$this->total_transaction}}</h1>
            </div>
            <div class="flex flex-col bg-gray-500 w-200 rounded-md w-full py-3">
                <h1 class="text-lg font-semibold text-center my-1">Total Product</h1>
                <h1 class="text-2xl my-2 font-semibold text-center">{{$this->total_product}}</h1>
            </div>
            
        </div>

        <div class="flex bg-gray-500 w-200 rounded-md w-full py-3">
            <div class="flex justify-start">
                <img src="{{asset('products/'. $this->best_seller->product->image) }}" alt="iamge" class="h-auto w-40 object-cover">
            </div>
            <h1 class="text-2xl my-2 font-semibold text-center">{{$this->best_seller->product->name}}</h1>
        </div>


    </div>
    {{-- Let all your things have their places; let each part of your business have its time. - Benjamin Franklin --}}
</div>