<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileResource\Pages;
use App\Models\Profile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->label('Akun Pengguna')
                ->relationship('user', 'name')
                ->searchable()
                ->required(),

            TextInput::make('username')
                ->label('Username')
                ->required()
                ->maxLength(255),

            Select::make('jenis_kelamin')
                ->label('Jenis Kelamin')
                ->options([
                    'L' => 'Laki-laki',
                    'P' => 'Perempuan',
                ])
                ->required(),
            Textarea::make('alamat')
                ->label('Alamat Domisili')
                ->required()
                ->columnSpanFull(),

            TextInput::make('no_hp')
                ->label('Nomor HP')
                ->tel()
                ->required()
                ->maxLength(20),

            FileUpload::make('foto_ktp')
                ->label('Foto KTP')
                ->image()
                ->directory('ktp')
                ->disk('public')
                ->preserveFilenames()
                ->imagePreviewHeight(150)
                ->acceptedFileTypes(['image/jpeg', 'image/png'])
                ->maxSize(2048)
                ->required(),

            FileUpload::make('foto_sim')
                ->label('Foto SIM')
                ->image()
                ->directory('sim')
                ->disk('public')
                ->preserveFilenames()
                ->imagePreviewHeight(150)
                ->acceptedFileTypes(['image/jpeg', 'image/png'])
                ->maxSize(2048)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('user.name')
                ->label('Nama Pengguna'),
            TextColumn::make('username')
                ->label('Username'),
                TextColumn::make('jenis_kelamin')
                ->label('Jenis Kelamin')
                ->formatStateUsing(function ($state) {
                    return $state === 'L' ? 'Laki-laki' : 'Perempuan';
                }),
            TextColumn::make('no_hp')
                ->label('No. HP')
                ->searchable(),

            TextColumn::make('alamat')
                ->label('Alamat')
                ->searchable()
                ->limit(40),

                ImageColumn::make('foto_ktp')
                ->label('KTP')
                ->disk('public')
                ->height(100)
                ->width(100)
                ->url(fn ($record) => $record->foto_ktp ? asset('storage/' . $record->foto_ktp) : null)
                ->openUrlInNewTab(),

            ImageColumn::make('foto_sim')
                ->label('SIM')
                ->disk('public')
                ->height(100)
                ->width(100)
                ->url(fn ($record) => $record->foto_sim ? asset('storage/' . $record->foto_sim) : null)
                ->openUrlInNewTab(),


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
            'index' => Pages\ListProfiles::route('/'),
            'create' => Pages\CreateProfile::route('/create'),
            'edit' => Pages\EditProfile::route('/{record}/edit'),
        ];
    }
}
