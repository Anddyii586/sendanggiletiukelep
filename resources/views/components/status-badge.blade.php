@props(['status'])

@php
    $map = [
        'draft' => ['Draft', 'bg-slate-100 text-slate-700 ring-slate-200'],
        'pending' => ['Pending', 'bg-amber-100 text-amber-700 ring-amber-200'],
        'waiting_payment' => ['Waiting Payment', 'bg-orange-100 text-orange-700 ring-orange-200'],
        'waiting_verification' => ['Waiting Verification', 'bg-orange-100 text-orange-700 ring-orange-200'],
        'confirmed' => ['Confirmed', 'bg-emerald-100 text-emerald-700 ring-emerald-200'],
        'cancelled' => ['Cancelled', 'bg-red-100 text-red-700 ring-red-200'],
        'completed' => ['Completed', 'bg-sky-100 text-sky-700 ring-sky-200'],
        'expired' => ['Expired', 'bg-slate-100 text-slate-700 ring-slate-200'],
        'unpaid' => ['Unpaid', 'bg-slate-100 text-slate-700 ring-slate-200'],
        'paid' => ['Paid', 'bg-emerald-100 text-emerald-700 ring-emerald-200'],
        'failed' => ['Failed', 'bg-red-100 text-red-700 ring-red-200'],
        'approved' => ['Approved', 'bg-emerald-100 text-emerald-700 ring-emerald-200'],
        'rejected' => ['Rejected', 'bg-red-100 text-red-700 ring-red-200'],
        'active' => ['Active', 'bg-emerald-100 text-emerald-700 ring-emerald-200'],
        'used' => ['Used', 'bg-sky-100 text-sky-700 ring-sky-200'],
    ];
    [$label, $classes] = $map[$status] ?? [str_replace('_', ' ', ucfirst($status)), 'bg-slate-100 text-slate-700 ring-slate-200'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-wide ring-1 {$classes}"]) }}>
    {{ $label }}
</span>
