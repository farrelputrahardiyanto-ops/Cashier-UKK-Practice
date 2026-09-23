<?php

use Livewire\Component;
use App\Models\Product;
use WireUi\Traits\WireUiActions;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

new class extends Component
{

    use WithFileUploads;

    use WIreUiActions;

    public $search = '';


    public $productId;

    public $name;

    public $description;

    public $price;

    public $discount;

    public $image;

    public $stock;


    public function resetForm()
    {
        $this->reset([
            'productId',
            'name',
            'description',
            'price',
            'discount',
            'stock',
            'image'
        ]);
    }

    public function notif($message)
    {
        $this->notification()->send([ 
            'icon' => 'success',
            'title' => 'Berhasil',
            'description' => $message
        ]);
    }


    public function edit($id)
    {
        $product = Product::findOrfail($id);

        $this->productId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->price = $product->price;
        $this->discount = $product->discount;
        $this->stock = $product->stock;

        $this->dispatch('edit');
    }


    public function save()
    {
        $rules = [
            "name" => "required",
            "description" => "required",
            "price" => "required",
            "discount" =>"nullable",
            "stock" => 'required',
            "image" =>"nullable|image:jpg, png, svg|max=2048"
         ];

         $data = [
            "name" => $this->name,
            "description" => $this->description,
            "price" => $this->price,
            "discount" => $this->discount,
            "stock" => $this->stock
         ];

         if($this->productId)
         {
            $product = Product::findOrFail($this->productId);
            if($this->image)
            {

                    if($product->image)
                    {
                        Storage::disk('public')->delete('product/'. $product->image);
                    }
                    $data['image'] = $this->image->hashName();
                    $this->image->storeAs('products', $data['image'], 'public');
                
            }

            $product->update($data);

            $message = "Data Berhasil Di Update";

         }else {
            if($this->image)
            {
                $data['image'] = $this->image->hashName();
                $this->image->storeAs('products', $data['image'], 'public');
            }

            Product::create($data);

            $message = "Data Berhasil Di Buat";
         }

         $this->dispatch('created');
         $this->resetForm();
         $this->notif($message);
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);

        $product->delete($id);

        $message = "Berhasil diHapus";

        $this->notification();
    }

    

    public function  render()
    {
        return $this->view([
            "products" => Product::query()->
            where(function ($query){
                $query->where('name', 'like', '%'.$this->search.'%')
                ->orwhere('description', 'like', '%'.$this->search.'%')
                ->orwhere('price', 'like', '%'.$this->search.'%')
                ->orwhere('discount', 'like', '%'.$this->search.'%');
            })->latest()->paginate(5)
        ])
        ->layout('layouts.app')
        ->title('Products');
    }
};
?>

<div
x-on:edit.window="$openModal('product')"
x-on:created.window="$closeModal('product')">
    <div class="max-w-6xl m-auto p-3 my-3">
        <div class="min-w-max my-3 flex justify-between">
            <x-button label="Create" icon="plus" x-on:click="$openModal('product')" />
            <div class="max-w-2xl">
                <x-input icon="magnifying-glass" placeholder="Search...." wire:model.live="search" />
            </div>
        </div>
       
        
             <x-card>
                <x-slot name="title"><h1 class="text-lg text-center">Products</h1></x-slot>
                <div class="overflow-x-auto min-w-full">
                <table class="table min-w-full text-left">
                    <thead>
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Description</th>
                            <th class="p-3">Price</th>
                            <th class="p-3">Discount</th>
                            <th class="p-3">Stock</th>
                            <th class="p-3">Image</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td class="p-3">{{$product->id}}</td>
                                <td class="p-3">{{$product->name}}</td>
                                <td class="p-3">{{$product->description}}</td>
                                <td class="p-3">Rp.{{number_format($product->price)}}</td>
                                <td class="p-3">{{$product->discount*100}}%</td>
                                <td class="p-3">{{$product->stock}}</td>
                                <td class="p-3"><img src="{{asset('storage/products/'. $product->image)}}" class="w-20 h-max-min object-cover" alt=""></td>
                                <td class="p-3">
                                    <div class="flex justify-center gap-2">
                                        <x-button warning icon="pencil" wire:click="edit({{$product->id}})" />
                                        <x-button negative icon="trash"
                                        x-on:confirm="{
                                            icon: 'warning',
                                            title: 'Yakin?',
                                            description: 'Yakin ingin hapus data ini?',
                                            method: 'delete',
                                            params: '{{$product->id}}'
                                        }" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-alert warning title="Data Null" />
                        @endforelse
                    </tbody>
                </table>
                <div class="flex justify-end my-3">
                    {{$products->links()}}
                </div>
            </x-card>
        </div>
    </div>

    <x-modal-card :title="$this->productId? 'Edit':'Save'" name="product">
        <div class="flex flex-col space-y-3">
            <label for="" class="dark:text-gray-400 font-medium text-sm text-bold">Image</label>
        <input type="file" wire:model="image" class="block">

        <x-input label="name" wire:model="name" />
        <x-textarea label="description" wire:model="description" />
        <x-input label="price" wire:model="price" />
        <x-input label="discount" wire:model="discount" />
        <x-input label="stock" wire:model="stock" />
        </div>

        <x-slot name="footer">
            <div class="flex justify-end gap-2">
                <x-button flat x-on:click="close" label="close" />
                <x-button wire:click="save" :label="$this->productId? 'Update':'save'" />
            </div>
        </x-slot>
    </x-modal-card>
    {{-- Nothing in life is to be feared, it is only to be understood. Now is the time to understand more, so that we may fear less. - Maria Skłodowska-Curie --}}
</div>