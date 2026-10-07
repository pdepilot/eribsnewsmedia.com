@extends('layouts.admin')
@section('title', 'Roles')
@section('content')
    <h1 class="mb-4 font-[Rubik,sans-serif] text-2xl">Roles</h1>
    <div class="grid gap-4">
        @foreach ($roles as $role)
            <form method="post" action="{{ route('admin.roles.update', $role) }}" class="rounded bg-white p-4 shadow-sm">
                @csrf @method('PUT')
                <h2 class="mb-3 font-semibold">{{ $role->name }}</h2>
                @if ($role->slug === 'admin')
                    <p class="text-sm text-zinc-500">Administrators keep every permission.</p>
                @else
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($permissions as $permission)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked($role->permissions->contains('id', $permission->id))>
                                {{ $permission->name }}
                            </label>
                        @endforeach
                    </div>
                    <button class="mt-3 rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save {{ $role->name }}</button>
                @endif
            </form>
        @endforeach
    </div>
@endsection
