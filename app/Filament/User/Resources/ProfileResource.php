<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\ProfileResource\Pages;
use App\Models\Profile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Profil Saya';
    protected static ?string $navigationGroup = 'Panel Pengguna';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('user_id')->default(fn () => Auth::id()),

            Forms\Components\TextInput::make('username')
                ->label('Username')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('jenis_kelamin')
                ->label('Jenis Kelamin')
                ->options([
                    'L' => 'Laki-laki',
                    'P' => 'Perempuan',
                ])
                ->required(),

            Forms\Components\Textarea::make('alamat')
                ->label('Alamat')
                ->required()
                ->columnSpanFull(),

            Forms\Components\TextInput::make('no_hp')
                ->label('No HP')
                ->required()
                ->tel(),

            Forms\Components\FileUpload::make('foto_ktp')
                ->label('Foto KTP')
                ->image()
                ->directory('ktp')
                ->preserveFilenames()
                ->maxSize(2048),

            Forms\Components\FileUpload::make('foto_sim')
                ->label('Foto SIM')
                ->image()
                ->directory('sim')
                ->preserveFilenames()
                ->maxSize(2048),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')->label('Nama'),
                Tables\Columns\TextColumn::make('jenis_kelamin')->label('Jenis Kelamin'),
                Tables\Columns\TextColumn::make('no_hp')->label('No HP'),
                Tables\Columns\ImageColumn::make('foto_ktp')->label('KTP')->height(50),
                Tables\Columns\ImageColumn::make('foto_sim')->label('SIM')->height(50),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfiles::route('/'),
            'edit' => Pages\EditProfile::route('/{record}/edit'),
            'create' => Pages\CreateProfile::route('/create'),
        ];
    }
}
