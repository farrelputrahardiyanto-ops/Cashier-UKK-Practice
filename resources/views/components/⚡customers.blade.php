<?php

use Livewire\Component;
use App\Models\Customer;
use WireUi\Traits\WireUiActions;

new class extends Component
{
    use WireUiActions;


    public $search = '';
    public $name;
    public $email;
    public $address;
    public $phone;
    public $customerId;


    public function resetForm()
    {
        $this->reset(
            'customerId',
            'name',
            'email',
            'phone',
            'address'
        );
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
        $customer = Customer::findOrFail($id);

        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->address = $customer->address;
        $this->customerId = $customer->id;


        $this->dispatch('edit');
    }


    public function save()
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required'
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address
        ];

        if($this->customerId)
        {
            $customer = Customer::findOrFail($this->customerId);

            $customer->update($data);

            $message = "Data Berhasil Di Update";
        }else {
            Customer::create($data);

            $message = "Data Berhasil Di Buat";
        }
        $this->dispatch('created');
        $this->resetForm();
        $this->notif($message);
    }

    public function delete($id)
    {

        $customer = Customer::findOrFail($id);

        $customer->delete($id);

        $message = "Data Berhasil Di Hapus";
        $this->notif($message);
    } 



    public function render()
    {

        return $this->view([
            "customers" => Customer::query()
            ->where(function ($query){
                $query->where('name', 'like', '%'.$this->search.'%')
                ->orwhere('email', 'like', '%'.$this->search.'%')
                ->orwhere('phone', 'like', '%'.$this->search.'%')
                ->orwhere('address', 'like', '%'.$this->search.'%');
            })->latest()->paginate(5)
        ])
        ->layout('layouts.app')
        ->title('Customers');
    }

};
?>

<div 
x-on:edit.window="$openModal('customer')"
x-on:created.window="$closeModal('customer')">

    <div class="max-w-6xl p-3 mx-auto">
        <div class="flex justify-between my-3">
            <x-button label="Create" icon="plus" x-on:click="$openModal('customer')" />

            <div class="max-w-2xl">
                <x-input placeholder="Search..." icon="magnifying-glass" wire:model.live="search" />
            </div>
        </div>

        <x-card>
            <x-slot name="title"><h1 class="text-lg">Customer</h1></x-slot>

            <div class="overflow-x-auto min-w-full">
                <table class="min-w-full text-left">
                    <thead>
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Phone</th>
                            <th class="p-3">Address</th>
                            <th class="p-3">Points</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($customers as $customer)
                        <tr>
                            <td class="p-3">{{$customer->id}}</td>
                            <td class="p-3">{{$customer->name}}</td>
                            <td class="p-3">{{$customer->email}}</td>
                            <td class="p-3">{{$customer->phone}}</td>
                            <td class="p-3">{{$customer->address}}</td>
                            <td class="p-3">{{$customer->points}}</td>
                            <td class="p-3">
                                <div class="fles justify-center space-x-2">
                                    <x-button warning icon="pencil" wire:click="edit('{{$customer->id}}')" />
                                    <x-button negative icon="trash" x-on:confirm="{
                                    icon:'warning',
                                    title:'Yakin?',
                                    description:'Yakin ingin hapus?',
                                    method:'delete',
                                    params:'{{$customer->id}}'
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
                    {{$customers->links()}}
                </div>
            </div>
        </x-card>
    </div>

    <x-modal-card :title="$this->customerId? 'Edit':'Create'" name="customer">
        <div class="flex flex-col space-y-2">
            <x-input label="name" wire:model="name" />
            <x-input label="email" wire:model="email" />
            <x-input label="phone" wire:model="phone" />
            <x-textarea label="address" wire:model="address" />
        </div>
        <x-slot name="footer">
            <div class="flex justify-end space-x-2">
                 <x-button flat label="Close" x-on:click="close" />
                 <x-button :label="$this->customerId? 'Update':'Save'" wire:click="save" />
            </div>
        </x-slot>
    </x-modal-card>
    {{-- I have not failed. I've just found 10,000 ways that won't work. - Thomas Edison --}}
</div>