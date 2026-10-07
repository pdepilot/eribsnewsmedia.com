@extends('layouts.admin')
@section('title', 'User')
@section('content')
    <h1 class="mb-4 font-[Rubik,sans-serif] text-2xl">{{ $account->exists ? 'Edit user' : 'New user' }}</h1>
    <form method="post" action="{{ $account->exists ? route('admin.users.update', $account) : route('admin.users.store') }}" class="grid max-w-xl gap-3 rounded bg-white p-4 shadow-sm">
        @csrf
        @if ($account->exists) @method('PUT') @endif
        <label class="grid gap-1 text-sm">Name <input name="name" value="{{ old('name', $account->name) }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Email <input type="email" name="email" value="{{ old('email', $account->email) }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Password <input type="password" name="password" class="rounded border border-zinc-300 px-3 py-2" {{ $account->exists ? '' : 'required' }}></label>
        <label class="grid gap-1 text-sm">Confirm password <input type="password" name="password_confirmation" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Role
            <select name="role_id" class="rounded border border-zinc-300 px-3 py-2">
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected(old('role_id', $account->exists ? $account->roles->first()?->id : null) == $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
        </label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save</button>
    </form>
@endsection
