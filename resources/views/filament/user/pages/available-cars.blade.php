<x-filament::page>
    <div class="grid  grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($this->cars as $car)
            <div class="bg-white rounded p-3 dark:bg-gray-900 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-5 flex flex-col">
                {{-- Gambar Mobil --}}
                <img style="height: 300px" src="{{ Storage::url($car->gambar) }}"
                     class="w-full h-48 object-cover rounded-xl mb-4"
                     alt="{{ $car->merk }}">

                {{-- Informasi Mobil --}}
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-1">{{ $car->merk }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">Plat: <span class="font-medium">{{ $car->no_plat }}</span></p>
                <p class="text-sm text-gray-600 dark:text-gray-300">Warna: <span class="font-medium">{{ $car->warna }}</span></p>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">Tahun: <span class="font-medium">{{ $car->tahun }}</span></p>

                {{-- Badge Status --}}
                <span class="self-start px-3 py-1 rounded-full text-xs font-semibold mb-2
                    {{ $car->status === 'tersedia'
                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                    {{ ucfirst($car->status) }}
                </span>

                {{-- Harga --}}
                <div class="mt-auto">
                    <p class="text-sm text-gray-700 dark:text-gray-200">
                        <strong>Dalam Kota:</strong> Rp{{ number_format($car->harga_dalam_kota, 0, ',', '.') }}
                    </p>
                    <p class="text-sm text-gray-700 dark:text-gray-200 mb-3">
                        <strong>Luar Kota:</strong> Rp{{ number_format($car->harga_luar_kota, 0, ',', '.') }}
                    </p>

                    {{-- Tombol Aksi --}}
                    @if($car->status === 'tersedia')
                        <a href="{{ route('filament.user.resources.rentals.create', ['car_id' => $car->id]) }}"
                           class="block text-center text-sm font-semibold bg-primary-500 text-white hover:bg-primary-600 dark:hover:bg-primary-400 px-4 py-2 rounded-md transition">
                            Pesan Sekarang
                        </a>
                    @else
                        <span class="block text-center text-sm text-gray-500 dark:text-gray-400">Tidak Tersedia</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-filament::page>
