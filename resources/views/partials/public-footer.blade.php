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
        <div class="mt-12 border-t border-white/10 pt-7 text-xs text-slate-400">
            &copy; {{ date('Y') }} Sendang Gile & Tiu Kelep Waterfalls. Senaru, North Lombok.
        </div>
    </div>
</footer>
