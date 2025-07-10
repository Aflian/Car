<section id="fleet" class="py-5">
    <div class="container">
        <h2 class="text-center section-title">🚘 Galeri Armada Mobil</h2>
        <div class="row">
            @foreach ( $mobil as $mobils )
            <div class="col-lg-4 g-4 col-md-6 mb-4">
                <div class="car-card">
                    <div class="car-placeholder  ">
                        <img style="height: 250px; width:100% " class="card-img" src="{{ asset('storage').'/'.$mobils->gambar }}" alt="">
                    </div>
                    <div class="p-3 mt-4 ">
                        <h5>Toyota Avanza</h5>
                        <hr>
                        <p class="text-muted">MPV keluarga yang nyaman dan irit BBM</p>
                        <hr>
                        <p class="text-muted">DALAM KOTA : <span class="text-success fw-bold" > {{$mobils->harga_dalam_kota}}</span>-,Rp/hari</p>
                        <p class="text-muted">LUAR KOTA : <span class="text-success fw-bold" > {{$mobils->harga_luar_kota}}</span>-,Rp/hari</p>
                        <hr>
                        <p class="text-muted">WARNA : {{$mobils->warna}}</p>
                        <hr>
                        <a class="btn btn-success" href="/user"> ORDER SEKARANG </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>