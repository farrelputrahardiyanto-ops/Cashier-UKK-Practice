<?php

use Livewire\Component;
use App\Models\User;
use Livewire\WithFileUploads;
use WireUi\Traits\WireUiActions;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;
    use WireUiActions;

    public $search = '';

    public $name;
    public $email;
    public $role;
    public $profile;
    public $password;
    public $userId;

    public $roles=[
        'admin' => 'Admin',
        'cashier' => 'Cashier'
    ];


    public function notif($message)
    {
        $this->notification()->send([
            'title' => 'Berhasil',
            'icon' => 'success',
            'message' => $message
        ]);
    }


    public function resetForm()
    {
        $this->reset(
            'userId',
            'name',
            'email',
            'role',
            'password',
            'profile'
        );
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->role = $user->role;
        $this->email = $user->email;

        $this->dispatch('edit');
    }

    public function save()
    {
        $rules = [
            'profile' => 'nullable|image:png, jpg, svg|max:2048',
            'name' => 'required',
            'email' => 'required',
            'role' => 'required'
        ];

        $rules['password'] = $this->userId
        ? 'nullable|min:8'
        : 'required|min:8';

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role
        ];

        if($this->userId)
        {
            $user = User::findOrFail($this->userId);

            if($this->password){
                $data['password'] = $this->password;
            }

            if($this->profile){
                if($user->profile)
                {
                    Storage::disk('public')->delete('users/'. $user->profile);
                }
                $data['profile'] = $this->profile->hashName();
                $this->profile->storeAs('users', $data['profile'], 'public' );
            }

            $user->update($data);
            $message = "Data Berhasdil DI Update";
        }else {
            $data['password'] = $this->password;

            if($this->profile)
            {
                $data['profile'] = $this->profile->hashName();
                $this->profile->storeAs('users', $data['profile'], 'public' );
            }

            User::create($data);

            $message = "Data Berhasil DI Buat";
        }

        $this->dispatch('created');

        $this->resetForm();

        $this->notif($message);


    }

    public function delete($id)
    {
        $user = User::findOrFail($id);

        $user->delete($id);

        $message = "Data Berhasil DI Hapus";

        $this->notif($message);
    }


    public function render()
    {
        return $this->view([
            "users" => User::query()
            ->where(function ($query){
                $query->where('name', 'like', '%'.$this->search.'%')
                ->orwhere('role', 'like', '%'.$this->search.'%')
                ->orwhere('email', 'like', '%'.$this->search.'%');
            })->latest()->paginate(5)
        ])->layout('layouts.app')
        ->title('Users');
    }
    
};
?>

<div
x-on:edit.window="$openModal('user')"
x-on:created.window="$closeModal('user')">
    <div class="max-w-6xl p-3 mx-auto">
        <div class="flex justify-between my-3">
            <x-button label="Create" icon="plus" x-on:click="$openModal('user')" />

            <div class="max-w-2xl">
                <x-input icon="magnifying-glass" placeholder="Search..." wire:model.live="search" />
            </div>
        </div>

        <x-card>
            <x-slot name="title"><h1 class="text-lg text-center">Users</h1></x-slot>

            <div class="overflow-x-auto min-w-full">
                <table class="min-w-full text-left">
                    <thead>
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Profile</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Role</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                        <tr>
                            <td class="p-3">{{ $user->id}}</td>
                            <td class="p-3"><x-avatar :src="asset('storage/users/'. $user->profile)"  /></td>
                            <td class="p-3">{{$user->name}}</td>
                            <td class="p-3">{{$user->role}}</td>
                            <td class="p-3">{{$user->email}}</td>
                            <td class="p-3">
                                <div class="flex justify-center space-x-2">
                                    <x-button warning icon="pencil" wire:click="edit('{{$user->id}}')" />
                                    <x-button negative icon="trash" x-on:confirm="{
                                    icon: 'warning',
                                    title: 'Yakin?',
                                    description: 'Yakin ingin hapus data ini?',
                                    method: 'delete',
                                    params: '{{$user->id}}'
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
                    {{$users->links()}}
                </div>
            </div>
        </x-card>
    </div>

    <x-modal-card :title="$this->userId? 'edit':'Create'" name="user">
        <div class="flex flex-col space-y-3">
            <label for="" class="text-sm font-md text-gray-400">Profile</label>
            <input type="file" wire:model="profile" class="block">

            <x-input label="Name" wire:model="name" />
            <x-native-select
            label="Role"
            :options="$this->roles"
            wire:model="role"
            />
            <x-input label="Email" wire:model="email" />
            <x-password wire:model="password" label="Password" />

            <x-slot name="footer">
                <div class="flex justify-end space-x-2">
                    <x-button flat label="Close" x-on:click="close" />
                    <x-button :label="$this->userId? 'Update':'Save'" wire:click="save" />
                </div>
            </x-slot>
        </div>
    </x-modal-card>
    {{-- Well begun is half done. - Aristotle --}}
</div>