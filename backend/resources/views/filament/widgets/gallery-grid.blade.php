<x-filament::section>
    <x-slot name="heading">ギャラリー（画像のみ）</x-slot>

    {{-- 大会フィルタ --}}
    <div class=" flex items-center gap-2">
        <label class="text-sm text-gray-400">大会フィルタ:</label>
        <select
            class="fi-input block rounded-md border-gray-300 text-sm
                   bg-white text-gray-900
                   focus:border-primary-500 focus:ring-primary-500
                   dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600
                   dark:focus:border-primary-400 dark:focus:ring-primary-400"
            wire:model.live="tournamentFilter"
        >
            <option value="">すべて</option>
            @foreach ($tournaments as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>

        @if($tournamentFilter)
            <button
                type="button"
                class="px-2 py-1 text-xs rounded-md border bg-white hover:bg-gray-50 text-gray-400
                       dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:hover:bg-gray-700"
                wire:click="$set('tournamentFilter', null)"
            >クリア</button>
        @endif
    </div>
    <div class="h-10"></div>
    {{-- グリッド --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-4">
        @forelse ($galleries as $gallery)
            @php
                $path = $gallery->image_url;
                if (is_string($path) && str_starts_with($path, '[')) {
                    $decoded = json_decode($path, true);
                    if (is_array($decoded)) $path = $decoded[0] ?? '';
                }
                $full = $path ? asset('storage/' . $path) : null;
            @endphp

            @if ($full)
                <div class="relative group" wire:key="gallery-{{ $gallery->id }}">
                    <button type="button"
                        class="block w-full"
                        wire:click="openLightboxById({{ $gallery->id }})">
                        <img
                            src="{{ $full }}"
                            alt="gallery-{{ $gallery->id }}"
                            loading="lazy"
                            class="aspect-square w-full object-cover rounded-xl border border-gray-200 group-hover:opacity-90 transition"
                        >
                    </button>

                    <a href="{{ \App\Filament\Resources\GalleryResource::getUrl('edit', ['record' => $gallery]) }}"
                       class="absolute top-2 right-2 text-xs px-2 py-1 rounded-md bg-white/90 border border-gray-200 shadow-sm opacity-0 group-hover:opacity-100 transition">
                        編集
                    </a>
                </div>
            @endif
        @empty
            <p class="text-sm text-gray-500">該当する画像がありません。</p>
        @endforelse
    </div>

    {{-- ライトボックス（イベント制御） --}}
    <x-filament::modal
        id="galleryLightbox"
        width="6xl"
        alignment="center"
        close-by-clicking-away="true"
    >
        <x-slot name="heading">
            {{ $this->lightboxAlt ?? '' }}
        </x-slot>

        {{-- キーボード操作（←/→/Esc） --}}
        <div
            x-data
            @keydown.window.prevent.left="$wire.prevImage()"
            @keydown.window.prevent.right="$wire.nextImage()"
            @keydown.window.escape="$dispatch('close-modal', { id: 'galleryLightbox' })"
        >
            {{-- 画像エリア（枠固定＋contain） --}}
            <div class="relative w-full flex items-center justify-center bg-black rounded-lg overflow-hidden"
                 style="height: clamp(300px, 78dvh, 800px);">
                @if ($this->lightboxSrc)
                    <img
                        src="{{ $this->lightboxSrc }}"
                        alt="{{ $this->lightboxAlt }}"
                        class="block w-auto h-auto max-w-full max-h-full object-contain"
                    >
                @endif

                {{-- 左右ナビ（全面オーバーレイ） --}}
                <div class="absolute inset-0 z-20 flex items-center justify-between px-2 md:px-4 pointer-events-none">
                    <button
                        type="button"
                        class="pointer-events-auto w-10 h-10 md:w-12 md:h-12 rounded-full bg-white/90 shadow
                               flex items-center justify-center hover:bg-white border border-gray-200"
                        wire:click="prevImage"
                        aria-label="前へ"
                    >
                        <x-heroicon-o-chevron-left class="w-6 h-6" />
                    </button>
                    <button
                        type="button"
                        class="pointer-events-auto w-10 h-10 md:w-12 md:h-12 rounded-full bg-white/90 shadow
                               flex items-center justify-center hover:bg-white border border-gray-200"
                        wire:click="nextImage"
                        aria-label="次へ"
                    >
                        <x-heroicon-o-chevron-right class="w-6 h-6" />
                    </button>
                </div>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'galleryLightbox' })">閉じる</x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament::section>
