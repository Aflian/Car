<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\RentalResource\Pages;
use App\Models\Rental;
use App\Models\Car;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class RentalResource extends Resource
{
    protected static ?string $model = Rental::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $userId = Auth::id();

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Penyewaan')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Pelanggan')
                            ->default($userId)
                            ->disabled()
                            ->relationship('user', 'name'),

                        Forms\Components\Select::make('car_id')
                            ->label('Mobil')
                            ->required()
                            ->relationship('car', 'merk')
                            ->options(
                                Car::query()
                                    ->where('status', 'tersedia')
                                    ->get()
                                    ->mapWithKeys(function ($car) {
                                        return [$car->id => $car->merk . ' - ' . $car->no_plat . ' (' . $car->warna . ')'];
                                    })
                            )
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $jenisPemakaian = $get('jenis_pemakaian');
                                $jumlahHari = $get('jumlah_hari') ?? 1;

                                if ($state && $jenisPemakaian) {
                                    $car = Car::find($state);
                                    if ($car) {
                                        $hargaPerHari = $jenisPemakaian === 'Dalam Kota'
                                            ? $car->harga_dalam_kota
                                            : $car->harga_luar_kota;

                                        $totalBiaya = $hargaPerHari * $jumlahHari;
                                        $set('total_biaya', $totalBiaya);
                                    }
                                }
                            }),

                        Forms\Components\Select::make('driver_id')
                            ->label('Driver')
                            ->relationship('driver', 'nama')
                            ->nullable()
                            ->searchable(),

                        Forms\Components\DatePicker::make('tanggal_rental')
                            ->label('Tanggal Sewa')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $endDate = $get('tanggal_kembali');

                                if ($state && $endDate) {
                                    $startCarbon = \Carbon\Carbon::parse($state);
                                    $endCarbon = \Carbon\Carbon::parse($endDate);

                                    // Pastikan tanggal kembali tidak lebih awal dari tanggal sewa
                                    if ($endCarbon->lt($startCarbon)) {
                                        $set('jumlah_hari', 0);
                                        $set('total_biaya', 0);
                                        return;
                                    }

                                    // Hitung jumlah hari (minimal 1 hari)
                                    $days = $startCarbon->diffInDays($endCarbon);
                                    $days = $days == 0 ? 1 : $days;
                                    $set('jumlah_hari', $days);

                                    // Hitung ulang total biaya
                                    $carId = $get('car_id');
                                    $jenisPemakaian = $get('jenis_pemakaian');

                                    if ($carId && $jenisPemakaian) {
                                        $car = Car::find($carId);
                                        if ($car) {
                                            $hargaPerHari = $jenisPemakaian === 'Dalam Kota'
                                                ? $car->harga_dalam_kota
                                                : $car->harga_luar_kota;

                                            $totalBiaya = $hargaPerHari * $days;
                                            $set('total_biaya', $totalBiaya);
                                        }
                                    }
                                }
                            }),

                        Forms\Components\DatePicker::make('tanggal_kembali')
                            ->label('Tanggal Kembali')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $startDate = $get('tanggal_rental');

                                if ($startDate && $state) {
                                    $startCarbon = \Carbon\Carbon::parse($startDate);
                                    $endCarbon = \Carbon\Carbon::parse($state);

                                    // Pastikan tanggal kembali tidak lebih awal dari tanggal sewa
                                    if ($endCarbon->lt($startCarbon)) {
                                        $set('jumlah_hari', 0);
                                        $set('total_biaya', 0);
                                        return;
                                    }

                                    // Hitung jumlah hari (minimal 1 hari)
                                    $days = $startCarbon->diffInDays($endCarbon);
                                    $days = $days == 0 ? 1 : $days;
                                    $set('jumlah_hari', $days);

                                    // Hitung ulang total biaya
                                    $carId = $get('car_id');
                                    $jenisPemakaian = $get('jenis_pemakaian');

                                    if ($carId && $jenisPemakaian) {
                                        $car = Car::find($carId);
                                        if ($car) {
                                            $hargaPerHari = $jenisPemakaian === 'Dalam Kota'
                                                ? $car->harga_dalam_kota
                                                : $car->harga_luar_kota;

                                            $totalBiaya = $hargaPerHari * $days;
                                            $set('total_biaya', $totalBiaya);
                                        }
                                    }
                                }
                            }),

                        Forms\Components\Select::make('jenis_pemakaian')
                            ->label('Jenis Pemakaian')
                            ->options([
                                'Dalam Kota' => 'Dalam Kota',
                                'Luar Kota' => 'Luar Kota'
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $carId = $get('car_id');
                                $jumlahHari = $get('jumlah_hari') ?? 1;

                                if ($carId && $state) {
                                    $car = Car::find($carId);
                                    if ($car) {
                                        $hargaPerHari = $state === 'Dalam Kota'
                                            ? $car->harga_dalam_kota
                                            : $car->harga_luar_kota;

                                        $totalBiaya = $hargaPerHari * $jumlahHari;
                                        $set('total_biaya', $totalBiaya);
                                    }
                                }
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Pembayaran')
                    ->schema([
                        Forms\Components\Select::make('payment_method_id')
                            ->label('Metode Pembayaran')
                            ->required()
                            ->relationship('paymentMethod', 'nama_metode'),

                        Forms\Components\FileUpload::make('bukti_pembayaran')
                            ->label('Bukti Pembayaran')
                            ->directory('bukti-pembayaran')
                            ->image()
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png']),

                        Forms\Components\TextInput::make('jumlah_hari')
                            ->label('Jumlah Hari')
                            ->numeric()
                            ->readOnly()
                            ->default(1),

                        Forms\Components\TextInput::make('total_biaya')
                            ->label('Total Biaya')
                            ->numeric()
                            ->prefix('Rp ')
                            ->readOnly()
                            ->default(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Penyewaan')
                            ->options([
                                'Menunggu Persetujuan' => 'Menunggu Persetujuan',
                                'Disetujui' => 'Disetujui',
                                'Sedang Digunakan' => 'Sedang Digunakan',
                                'Selesai' => 'Selesai'
                            ])
                            ->default('Menunggu Persetujuan')
                            ->visible(fn (): bool => auth()->user()->is_admin ?? false),

                        Forms\Components\Toggle::make('denda')
                            ->label('Ada Denda?')
                            ->default(false)
                            ->disabled(),
                    ])
            ]);
    }

    // Method lama dihapus karena sudah inline
    // protected static function updateCalculation() dan calculateTotalCost() sudah tidak diperlukan

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pelanggan')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('car.merk')
                    ->label('Mobil')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_rental')
                    ->label('Tgl Sewa')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_kembali')
                    ->label('Tgl Kembali')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Menunggu Persetujuan' => 'warning',
                        'Disetujui' => 'success',
                        'Sedang Digunakan' => 'primary',
                        'Selesai' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\IconColumn::make('denda')
                    ->label('Denda')
                    ->boolean(),

                Tables\Columns\TextColumn::make('total_biaya')
                    ->label('Total')
                    ->numeric()
                    ->prefix('Rp ')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Menunggu Persetujuan' => 'Menunggu Persetujuan',
                        'Disetujui' => 'Disetujui',
                        'Sedang Digunakan' => 'Sedang Digunakan',
                        'Selesai' => 'Selesai'
                    ]),

                Tables\Filters\SelectFilter::make('jenis_pemakaian')
                    ->options([
                        'Dalam Kota' => 'Dalam Kota',
                        'Luar Kota' => 'Luar Kota'
                    ]),

                Tables\Filters\Filter::make('tanggal_rental')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_rental', '>=', $date),
                            )
                            ->when(
                                $data['sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_rental', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal_rental', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRentals::route('/'),
            'create' => Pages\CreateRental::route('/create'),
            'edit' => Pages\EditRental::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(
                !auth()->user()->is_admin ?? false,
                fn (Builder $query): Builder => $query->where('user_id', auth()->id())
            );
    }
}