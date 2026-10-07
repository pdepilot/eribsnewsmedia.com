@extends('layouts.admin')
@section('title', 'Users')
@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Users</h1>
        <a href="{{ route('admin.users.create') }}" class="rounded bg-eribs px-3 py-2 text-sm font-semibold text-white">New user</a>
    </div>
    <ul class="rounded bg-white shadow-sm">
        @foreach ($users as $user)
            <li class="flex justify-between border-b border-zinc-100 px-4 py-3 text-sm">
                <a href="{{ route('admin.users.edit', $user) }}">{{ $user->name }}</a>
                <span>{{ $user->roleLabel() }}</span>
            </li>
        @endforeach
    </ul>
@endsection
