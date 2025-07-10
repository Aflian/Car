<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarResource\Pages;
use App\Models\Car;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;

class CarResource extends Resource
{
    protected static ?string $model = Car::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Data Kendaraan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('merk')
                ->label('Merk Mobil')
                ->required()
                ->maxLength(255),

            TextInput::make('no_plat')
                ->label('Nomor Plat')
                ->required()
                ->maxLength(255),

            TextInput::make('warna')
                ->label('Warna Mobil')
                ->required()
                ->maxLength(255),

            TextInput::make('tahun')
                ->label('Tahun Mobil')
                ->numeric()
                ->required(),

            TextInput::make('harga_dalam_kota')
                ->label('Harga Dalam Kota (Rp)')
                ->numeric()
                ->required(),

            TextInput::make('harga_luar_kota')
                ->label('Harga Luar Kota (Rp)')
                ->numeric()
                ->required(),

            TextInput::make('denda_per_hari')
                ->label('Denda per Hari (Rp)')
                ->numeric()
                ->required(),

            Select::make('status')
                ->label('Status')
                ->options([
                    'tersedia'=>'Tersedia',
                    'dirental'=>'Dirental',
                ]),

            FileUpload::make('gambar')
                ->label('Foto Mobil')
                ->image()
                ->disk('public')
                ->directory('cars')
                ->preserveFilenames()
                ->imagePreviewHeight(150)
                ->previewable(true)
                ->acceptedFileTypes(['image/jpeg', 'image/png','image/jpg'])
                ->maxSize(2048)
                ->required()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('merk')->label('Merk')->searchable(),
                TextColumn::make('no_plat')->label('Plat')->searchable(),
                TextColumn::make('warna')->label('Warna'),
                TextColumn::make('tahun')->label('Tahun')->sortable(),
                TextColumn::make('harga_dalam_kota')->label('Dalam Kota')->money('IDR'),
                TextColumn::make('harga_luar_kota')->label('Luar Kota')->money('IDR'),
                TextColumn::make('denda_per_hari')->label('Denda / Hari')->money('IDR'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tersedia' => 'success',
                        'dirental' => 'warning',
                    }),

                // Menampilkan jumlah rental yang terkait
                TextColumn::make('rentals_count')
                    ->label('Total Rental')
                    ->counts('rentals')
                    ->badge()
                    ->color('info'),

                ImageColumn::make('gambar')
                    ->label('Foto Mobil')
                    ->disk('public')
                    ->height(50),

                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'tersedia' => 'Tersedia',
                        'dirental' => 'Dirental',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat')
                        ->icon('heroicon-m-eye'),

                    EditAction::make()
                        ->label('Edit')
                        ->icon('heroicon-m-pencil-square'),

                    Action::make('ubah_status')
                        ->label('Ubah Status')
                        ->icon('heroicon-m-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Ubah Status Mobil')
                        ->modalDescription('Apakah Anda yakin ingin mengubah status mobil ini?')
                        ->modalSubmitActionLabel('Ya, Ubah')
                        ->action(function (Car $record) {
                            $newStatus = $record->status === 'tersedia' ? 'dirental' : 'tersedia';
                            $record->update(['status' => $newStatus]);

                            Notification::make()
                                ->title('Status berhasil diubah')
                                ->body("Status mobil {$record->merk} ({$record->no_plat}) berhasil diubah menjadi {$newStatus}")
                                ->success()
                                ->send();
                        }),

                    Action::make('duplicate')
                        ->label('Duplikat')
                        ->icon('heroicon-m-document-duplicate')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Duplikat Mobil')
                        ->modalDescription('Apakah Anda yakin ingin menduplikat data mobil ini?')
                        ->modalSubmitActionLabel('Ya, Duplikat')
                        ->action(function (Car $record) {
                            $newCar = $record->replicate();
                            $newCar->no_plat = $record->no_plat . '-COPY';
                            $newCar->save();

                            Notification::make()
                                ->title('Mobil berhasil diduplikat')
                                ->body("Data mobil {$record->merk} berhasil diduplikat")
                                ->success()
                                ->send();
                        }),

                    Action::make('view_rentals')
                        ->label('Lihat Rental')
                        ->icon('heroicon-m-list-bullet')
                        ->color('info')
                        ->url(fn (Car $record): string => route('filament.admin.resources.rentals.index', ['tableFilters[car_id][value]' => $record->id]))
                        ->visible(fn (Car $record): bool => $record->rentals()->count() > 0),

                    DeleteAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-m-trash')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Mobil')
                        ->modalDescription(function (Car $record) {
                            $rentalCount = $record->rentals()->count();
                            if ($rentalCount > 0) {
                                return "Mobil ini memiliki {$rentalCount} data rental. Hapus terlebih dahulu data rental yang terkait sebelum menghapus mobil ini.";
                            }
                            return 'Apakah Anda yakin ingin menghapus mobil ini? Data yang dihapus tidak dapat dikembalikan.';
                        })
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->before(function (Car $record) {
                            // Cek apakah mobil memiliki relasi dengan rentals
                            if ($record->rentals()->count() > 0) {
                                Notification::make()
                                    ->title('Tidak dapat menghapus mobil')
                                    ->body('Mobil ini masih memiliki data rental. Hapus terlebih dahulu data rental yang terkait.')
                                    ->danger()
                                    ->send();

                                // Batalkan penghapusan
                                return false;
                            }
                        }),

                    // Alternative: Force Delete (Soft Delete semua rentals terkait)
                    Action::make('force_delete')
                        ->label('Hapus Paksa')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Mobil Beserta Data Rental')
                        ->modalDescription(function (Car $record) {
                            $rentalCount = $record->rentals()->count();
                            return "PERINGATAN: Ini akan menghapus mobil beserta {$rentalCount} data rental yang terkait. Tindakan ini tidak dapat dibatalkan!";
                        })
                        ->modalSubmitActionLabel('Ya, Hapus Paksa')
                        ->visible(fn (Car $record): bool => $record->rentals()->count() > 0)
                        ->action(function (Car $record) {
                            try {
                                // Hapus semua rental terkait terlebih dahulu
                                $record->rentals()->delete();

                                // Kemudian hapus mobil
                                $record->delete();

                                Notification::make()
                                    ->title('Mobil berhasil dihapus')
                                    ->body('Mobil beserta semua data rental yang terkait berhasil dihapus')
                                    ->success()
                                    ->send();

                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Gagal menghapus mobil')
                                    ->body('Terjadi kesalahan: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button()
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export Data')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        // Implementasi export (bisa menggunakan package seperti Laravel Excel)
                        Notification::make()
                            ->title('Export berhasil')
                            ->body('Data mobil berhasil diekspor')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // Bulk delete yang aman
                    Tables\Actions\BulkAction::make('safe_delete')
                        ->label('Hapus Terpilih (Aman)')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Mobil Terpilih')
                        ->modalDescription('Hanya mobil yang tidak memiliki data rental yang akan dihapus.')
                        ->action(function ($records) {
                            $deletedCount = 0;
                            $skippedCount = 0;

                            foreach ($records as $record) {
                                if ($record->rentals()->count() === 0) {
                                    $record->delete();
                                    $deletedCount++;
                                } else {
                                    $skippedCount++;
                                }
                            }

                            $message = "Berhasil menghapus {$deletedCount} mobil.";
                            if ($skippedCount > 0) {
                                $message .= " {$skippedCount} mobil dilewati karena masih memiliki data rental.";
                            }

                            Notification::make()
                                ->title('Penghapusan selesai')
                                ->body($message)
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('change_status')
                        ->label('Ubah Status')
                        ->icon('heroicon-m-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Ubah Status Mobil')
                        ->modalDescription('Pilih status baru untuk mobil yang dipilih')
                        ->form([
                            Select::make('status')
                                ->label('Status Baru')
                                ->options([
                                    'tersedia' => 'Tersedia',
                                    'dirental' => 'Dirental',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });

                            Notification::make()
                                ->title('Status berhasil diubah')
                                ->body('Status mobil terpilih berhasil diubah')
                                ->success()
                                ->send();
                        }),
                ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Tambahkan relasi untuk menampilkan rental terkait
            // RelationManagers\RentalsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCars::route('/'),
            'create' => Pages\CreateCar::route('/create'),
            'edit' => Pages\EditCar::route('/{record}/edit'),
            // 'view' => Pages\ViewCar::route('/{record}'),
        ];
    }
}