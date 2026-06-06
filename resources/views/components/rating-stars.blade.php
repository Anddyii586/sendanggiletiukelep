@props(['rating' => 5, 'class' => 'h-4 w-4 text-[#007A5A]'])

<span class="inline-flex items-center gap-0.5" aria-label="Rating {{ $rating }} dari 5">
    @for($i = 1; $i <= 5; $i++)
        <x-icon name="star" :class="$class . ($i <= (int) $rating ? '' : ' opacity-25')" />
    @endfor
</span>
