<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('car_id')->constrained();
            $table->foreignId('driver_id')->nullable()->constrained(); // nullable jika tidak pakai driver
            $table->date('tanggal_rental');
            $table->date('tanggal_kembali');
            $table->enum('jenis_pemakaian', ['Dalam Kota', 'Luar Kota']);
            $table->foreignId('payment_method_id')->constrained();
            $table->string('bukti_pembayaran')->nullable();
            $table->integer('jumlah_hari');
            $table->decimal('total_biaya', 10, 2);
            $table->enum('status', ['Menunggu Persetujuan', 'Konfirmasi', 'Sedang di Rental', 'Selesai'])
            ->default('Menunggu Persetujuan');
            $table->boolean('denda')->default(false);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
