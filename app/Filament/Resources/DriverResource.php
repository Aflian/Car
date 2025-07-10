<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Models\Driver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('nama')
                ->label('Nama Driver')
                ->required()
                ->maxLength(255),

            TextInput::make('no_hp')
                ->label('Nomor HP')
                ->tel()
                ->required()
                ->maxLength(20),

            Textarea::make('alamat')
                ->label('Alamat')
                ->required()
                ->columnSpanFull(),

            FileUpload::make('foto_sim')
                ->label('Foto SIM')
                ->image()
                ->directory('sim_driver')
                ->disk('public')
                ->preserveFilenames()
                ->imagePreviewHeight(150)
                ->acceptedFileTypes(['image/jpeg', 'image/png'])
                ->maxSize(2048)
                ->required(),

            Select::make('status')
                ->label('Status Ketersediaan')
                ->options([
                    'tersedia' => 'Tersedia',
                    'tidak tersedia' => 'Tidak Tersedia',
                ])
                ->required()
                ->default('tersedia'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nama')
                ->label('Nama Driver')
                ->searchable(),

            TextColumn::make('no_hp')
                ->label('No. HP')
                ->searchable(),

            TextColumn::make('alamat')
                ->label('Alamat')
                ->limit(40)
                ->searchable(),

            ImageColumn::make('foto_sim')
                ->label('Foto SIM')
                ->disk('public')
                ->height(50),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn ($state) => $state === 'tersedia' ? 'success' : 'danger'),

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
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
