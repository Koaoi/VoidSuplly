{{--
    Countdown Timer Component
    Usage: @include('components.countdown', ['releaseDate' => $product->release_date, 'id' => 'drop-1'])
--}}
<div
    x-data="countdown('{{ $releaseDate->toISOString() }}')"
    x-init="start()"
    class="flex items-center gap-3"
    id="countdown-{{ $id ?? 'default' }}"
>
    {{-- Days --}}
    <div class="flex flex-col items-center">
        <div class="bg-void-dark border border-void-border rounded-xl w-14 h-14 flex items-center justify-center">
            <span x-text="String(days).padStart(2,'0')"
                  class="text-2xl font-black text-void-accent tabular-nums"></span>
        </div>
        <span class="text-[9px] text-void-gray uppercase tracking-widest mt-1.5">Hari</span>
    </div>

    <span class="text-void-muted text-xl font-light mb-4">:</span>

    {{-- Hours --}}
    <div class="flex flex-col items-center">
        <div class="bg-void-dark border border-void-border rounded-xl w-14 h-14 flex items-center justify-center">
            <span x-text="String(hours).padStart(2,'0')"
                  class="text-2xl font-black text-void-white tabular-nums"></span>
        </div>
        <span class="text-[9px] text-void-gray uppercase tracking-widest mt-1.5">Jam</span>
    </div>

    <span class="text-void-muted text-xl font-light mb-4">:</span>

    {{-- Minutes --}}
    <div class="flex flex-col items-center">
        <div class="bg-void-dark border border-void-border rounded-xl w-14 h-14 flex items-center justify-center">
            <span x-text="String(minutes).padStart(2,'0')"
                  class="text-2xl font-black text-void-white tabular-nums"></span>
        </div>
        <span class="text-[9px] text-void-gray uppercase tracking-widest mt-1.5">Menit</span>
    </div>

    <span class="text-void-muted text-xl font-light mb-4">:</span>

    {{-- Seconds --}}
    <div class="flex flex-col items-center">
        <div class="bg-void-dark border border-void-border rounded-xl w-14 h-14 flex items-center justify-center">
            <span x-text="String(seconds).padStart(2,'0')"
                  class="text-2xl font-black text-void-gray tabular-nums"></span>
        </div>
        <span class="text-[9px] text-void-gray uppercase tracking-widest mt-1.5">Detik</span>
    </div>
</div>

@once
@push('scripts')
<script>
function countdown(targetDateStr) {
    return {
        days: 0, hours: 0, minutes: 0, seconds: 0,
        interval: null,
        target: new Date(targetDateStr),

        start() {
            this.tick();
            this.interval = setInterval(() => this.tick(), 1000);
        },

        tick() {
            const now  = new Date();
            const diff = this.target - now;

            if (diff <= 0) {
                this.days = this.hours = this.minutes = this.seconds = 0;
                clearInterval(this.interval);
                // Reload halaman setelah countdown selesai agar status produk berubah
                setTimeout(() => window.location.reload(), 1500);
                return;
            }

            this.days    = Math.floor(diff / (1000 * 60 * 60 * 24));
            this.hours   = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            this.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            this.seconds = Math.floor((diff % (1000 * 60)) / 1000);
        },
    };
}
</script>
@endpush
@endonce