<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Manajemen Pembayaran';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('nama_metode')
                ->label('Nama Metode')
                ->placeholder('Contoh: QRIS, Transfer Bank')
                ->required()
                ->maxLength(100),

            FileUpload::make('kode_static')
                ->label('Gambar QRIS')
                ->directory('qris')
                ->image()
                ->imagePreviewHeight('150')
                ->maxSize(2048)
                ->helperText('Unggah gambar kode QR jika metode menggunakan QRIS')
                ->nullable(),

            TextInput::make('no_rekening')
                ->label('No Rekening')
                ->placeholder('Hanya jika metode Transfer')
                ->maxLength(100)
                ->nullable(),

            TextInput::make('atas_nama')
                ->label('Atas Nama')
                ->placeholder('Nama pemilik rekening')
                ->maxLength(100)
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nama_metode')
                ->label('Metode')
                ->searchable(),

                ImageColumn::make('kode_static')
                ->label('Gambar Rekening')
                ->disk('public')
                ->height(100)
                ->width(100)
                ->defaultImageUrl(url('/img/no-image.png'))
                ->url(fn ($record) => $record->kode_static ? asset('storage/' . $record->kode_static) : null)
                ->openUrlInNewTab(),


            TextColumn::make('no_rekening')
                ->label('No Rekening')
                ->placeholder('-')
                ->toggleable(),

            TextColumn::make('atas_nama')
                ->label('Atas Nama')
                ->placeholder('-')
                ->toggleable(),

            TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('updated_at')
                ->label('Diperbarui')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
