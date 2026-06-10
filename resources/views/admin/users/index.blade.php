@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<section>
    <div class="mb-8">
        <h1 class="text-4xl font-black tracking-tight">User Management</h1>
        <p class="mt-2 text-[#6B7280]">Read-only monitoring akun traveler dan admin.</p>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="surface-card mb-6 grid gap-4 p-5 md:grid-cols-[1fr_220px_160px]">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau phone" class="form-input">
        <select name="role" class="form-input">
            <option value="">Semua role</option>
            @foreach($roles as $role)
                <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
            @endforeach
        </select>
        <button class="btn-dark" type="submit"><x-icon name="search" class="h-5 w-5" /> Filter</button>
    </form>

    <div class="surface-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="premium-table text-sm">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Total Bookings</th>
                        <th>Total Paid</th>
                        <th>Total Reviews</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar-initial">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                    <p class="font-black">{{ $user->name }}</p>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td><span class="rounded-full bg-[#EEF3FF] px-3 py-1 text-[11px] font-black uppercase text-[#0F1B2D]">{{ $user->role }}</span></td>
                            <td>{{ $user->bookings_count }}</td>
                            <td>{{ $user->paid_bookings_count }}</td>
                            <td>{{ $user->reviews_count }}</td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td><a class="text-[#007A5A]" href="{{ route('admin.users.show', $user) }}"><x-icon name="eye" class="h-5 w-5" /></a></td>
                        </tr>
                    @empty
                        <tr><td class="py-8 text-[#6B7280]" colspan="9">User tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">{{ $users->links() }}</div>
</section>
@endsection
