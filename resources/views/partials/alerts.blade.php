@if(session('success') || session('error') || $errors->any())
    <div class="mb-6 space-y-3">
        @if(session('success'))
            <div class="rounded-[16px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-[16px] border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-800">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-[16px] border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                <p class="font-bold">Ada input yang perlu diperbaiki.</p>
            </div>
        @endif
    </div>
@endif
