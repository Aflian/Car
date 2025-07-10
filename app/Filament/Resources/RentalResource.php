<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentalResource\Pages;
use App\Models\Car;
use App\Models\Driver;
use App\Models\PaymentMethod;
use App\Models\Rental;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class RentalResource extends Resource
{
    protected static ?string $model = Rental::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Manajemen Transaksi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->label('Pengguna')
                ->options(User::all()->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Select::make('car_id')
                ->label('Mobil')
                ->options(Car::all()->pluck('merk', 'id'))
                ->searchable()
                ->required(),
                Select::make('status_mobil')
                ->label('Status Mobil')
                ->options([
                    'tersedia' => 'Tersedia',
                    'di rental' => 'Di Rental',
                ])
                ->required()
                ->default(function ($get) {
                    $car = \App\Models\Car::find($get('car_id'));
                    return $car?->status ?? null;
                })
                ->visible(fn ($get) => filled($get('car_id')))
                ->live()
                ->afterStateUpdated(function ($state, callable $get) {
                    $car = \App\Models\Car::find($get('car_id'));
                    if ($car) {
                        $car->status = $state;
                        $car->save();
                    }
                }),


            Select::make('driver_id')
                ->label('Driver')
                ->options(Driver::all()->pluck('nama', 'id'))
                ->searchable()
                ->nullable(),

            Forms\Components\DatePicker::make('tanggal_rental')
                ->label('Tanggal Rental')
                ->required(),

            Forms\Components\DatePicker::make('tanggal_kembali')
                ->label('Tanggal Kembali')
                ->required(),

            Select::make('jenis_pemakaian')
                ->label('Jenis Pemakaian')
                ->options([
                    'dalam kota' => 'Dalam Kota',
                    'luar kota' => 'Luar Kota',
                ])
                ->required(),

            Select::make('payment_method_id')
                ->label('Metode Pembayaran')
                ->options(PaymentMethod::all()->pluck('nama_metode', 'id'))
                ->searchable()
                ->required(),

            FileUpload::make('bukti_pembayaran')
                ->label('Bukti Pembayaran')
                ->directory('bukti')
                ->image()
                ->imagePreviewHeight('150')
                ->nullable(),

                TextInput::make('jumlah_hari')
                ->label('Jumlah Hari')
                ->readOnly(),

            TextInput::make('total_biaya')
                ->label('Total Biaya')
                ->readOnly(),


            Select::make('status')
                ->label('Status')
                ->options([
                    'Menunggu Persetujuan' => 'Menunggu Konfirmasi',
                    'Konfirmasi' => 'Disetujui',
                    'Sedang di Rental' => 'Sedang di Rental',
                    'Selesai' => 'Selesai',
                ])
                ->required(),

            Forms\Components\Toggle::make('denda')
                ->label('Denda')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
{
    return $table->columns([
        TextColumn::make('user.name')->label('Pengguna')->searchable(),
        TextColumn::make('car.merk')->label('Mobil'),
        TextColumn::make('car.status')->label('Status Mobil'),
        TextColumn::make('driver.nama')->label('Driver')->default('-'),
        TextColumn::make('tanggal_rental')->label('Tanggal Rental')->date(),
        TextColumn::make('tanggal_kembali')->label('Tanggal Kembali')->date(),
        TextColumn::make('jenis_pemakaian')->label('Pemakaian'),
        TextColumn::make('paymentMethod.nama_metode')->label('Metode'),
        ImageColumn::make('bukti_pembayaran')
                ->label('Bukti')
                ->height(40)
                ->width(40)
                ->circular()
                ->url(fn ($record) => $record->bukti_pembayaran ? asset('storage/' . $record->bukti_pembayaran) : null)
                ->openUrlInNewTab(),
        TextColumn::make('jumlah_hari')->label('Hari')->sortable(),
        TextColumn::make('total_biaya')->label('Total')->money('IDR')->sortable(),
        TextColumn::make('status')->label('Status')->badge(),
        IconColumn::make('denda')->label('Denda')->boolean(),
        TextColumn::make('created_at')->label('Dibuat')->dateTime(),
    ])
    ->defaultSort('created_at', 'desc')
    ->actions([
        Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(), // ✅ Tambahkan ini
    ])
    ->bulkActions([
        Tables\Actions\DeleteBulkAction::make(),
    ]);
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
}