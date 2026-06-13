@php
    $socialLinks = [
        ['name' => 'Facebook', 'icon' => 'facebook', 'url' => 'FACEBOOK_URL_PLACEHOLDER'],
        ['name' => 'Instagram', 'icon' => 'instagram', 'url' => 'INSTAGRAM_URL_PLACEHOLDER'],
        ['name' => 'WhatsApp', 'icon' => 'whatsapp', 'url' => 'WHATSAPP_URL_PLACEHOLDER'],
    ];
@endphp

<footer class="bg-[#0F1B2D] text-white">
    <div class="app-container py-14">
        <div class="grid gap-10 md:grid-cols-[1.4fr_.6fr_.6fr]">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-black text-[#67FFD0]">
                    <x-icon name="droplet" class="h-6 w-6" />
                    Sendang Gile & Tiu Kelep
                </a>
                <p class="mt-5 max-w-md text-sm leading-7 text-slate-300">
                    Gerbang utama menuju keindahan alami kaki Gunung Rinjani. Kami membantu wisatawan merencanakan kunjungan yang aman, informatif, dan berkesan.
                </p>
            </div>
            <div>
                <h3 class="text-sm font-black uppercase tracking-wider text-[#67FFD0]">Menu</h3>
                <div class="mt-4 grid gap-2 text-sm text-slate-300">
                    <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                    <a href="{{ route('destination') }}" class="hover:text-white">Destinasi</a>
                    <a href="{{ route('gallery') }}" class="hover:text-white">Galeri</a>
                </div>
            </div>
            <div>
                <h3 class="text-sm font-black uppercase tracking-wider text-[#67FFD0]">Support</h3>
                <div class="mt-4 grid gap-2 text-sm text-slate-300">
                    <a href="{{ route('reviews') }}" class="hover:text-white">Review</a>
                    <a href="{{ route('contact') }}" class="hover:text-white">Kontak</a>
                    <a href="{{ route('login') }}" class="hover:text-white">Login</a>
                </div>
            </div>
        </div>
        <div class="mt-12 flex flex-col gap-5 border-t border-white/10 pt-7 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} Sendang Gile & Tiu Kelep Waterfalls. Senaru, North Lombok.</p>
            <div class="flex items-center gap-3" aria-label="Social media">
                @foreach($socialLinks as $social)
                    <a
                        href="{{ $social['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="{{ $social['name'] }}"
                        title="{{ $social['name'] }}"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 text-slate-300 transition hover:-translate-y-0.5 hover:border-[#67FFD0]/50 hover:bg-[#67FFD0]/10 hover:text-[#67FFD0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#67FFD0]"
                    >
                        <x-icon :name="$social['icon']" class="h-5 w-5" />
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</footer>
