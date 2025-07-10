<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\RentalResource\Pages;
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
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;
use Filament\Forms\Set;

class RentalResource extends Resource
{
    protected static ?string $model = Rental::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Manajemen Transaksi';
    protected static ?string $navigationLabel = 'Rental Mobil';
    protected static ?string $modelLabel = 'Rental';
    protected static ?string $pluralModelLabel = 'Rentals';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Rental')
                ->description('Data dasar rental mobil')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Select::make('user_id')
                        ->label('Pengguna')
                        ->relationship('user', 'name')
                        ->default(fn () => auth()->id())
                        ->disabled(fn () => auth()->user()?->id !== 1)
                        ->required(),


                        Select::make('car_id')
                            ->label('Mobil')
                            ->options(Car::where('status', 'tersedia')->pluck('merk', 'id'))
                            ->preload()
                            ->required()
                            ->placeholder('Pilih mobil')
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $car = Car::find($state);
                                    if ($car) {
                                        $set('harga_per_hari', $car->harga_per_hari);
                                    }
                                }
                            }),

                        Select::make('driver_id')
                            ->label('Driver (Opsional)')
                            ->options(Driver::where('status', 'tersedia')->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Pilih driver atau kosongkan'),

                        Select::make('jenis_pemakaian')
                            ->label('Jenis Pemakaian')
                            ->options([
                                'dalam_kota' => 'Dalam Kota',
                                'luar_kota' => 'Luar Kota',
                            ])
                            ->required()
                            ->placeholder('Pilih jenis pemakaian'),
                    ]),
                ]),
            Section::make('Periode Rental')
                ->description('Tanggal rental dan pengembalian')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\DatePicker::make('tanggal_rental')
                            ->label('Tanggal Rental')
                            ->required()
                            ->minDate(now())
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $tanggalKembali = $get('tanggal_kembali');
                                if ($state && $tanggalKembali) {
                                    $days = \Carbon\Carbon::parse($state)->diffInDays(\Carbon\Carbon::parse($tanggalKembali)) + 1;
                                    $set('jumlah_hari', $days);

                                    $hargaPerHari = $get('harga_per_hari') ?? 0;
                                    $set('total_biaya', $days * $hargaPerHari);
                                }
                            }),

                        Forms\Components\DatePicker::make('tanggal_kembali')
                            ->label('Tanggal Kembali')
                            ->required()
                            ->minDate(fn (Get $get) => $get('tanggal_rental') ?? now())
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $tanggalRental = $get('tanggal_rental');
                                if ($state && $tanggalRental) {
                                    $days = \Carbon\Carbon::parse($tanggalRental)->diffInDays(\Carbon\Carbon::parse($state)) + 1;
                                    $set('jumlah_hari', $days);

                                    $hargaPerHari = $get('harga_per_hari') ?? 0;
                                    $set('total_biaya', $days * $hargaPerHari);
                                }
                            }),

                        TextInput::make('jumlah_hari')
                            ->label('Jumlah Hari')
                            ->numeric()
                            ->readOnly()
                            ->default(1),

                        TextInput::make('harga_per_hari')
                            ->label('Harga per Hari')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly(),
                    ]),
                ]),

            Section::make('Pembayaran')
                ->description('Informasi pembayaran dan bukti')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('payment_method_id')
                            ->label('Metode Pembayaran')
                            ->options(PaymentMethod::all()->pluck('nama_metode', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Pilih metode pembayaran'),

                        TextInput::make('total_biaya')
                            ->label('Total Biaya')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly(),
                    ]),

                    FileUpload::make('bukti_pembayaran')
                        ->label('Bukti Pembayaran')
                        ->directory('bukti-pembayaran')
                        ->image()
                        ->imagePreviewHeight('200')
                        ->imageResizeMode('contain')
                        ->maxSize(2048)
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                        ->nullable()
                        ->helperText('Upload bukti pembayaran (PNG/JPG, max 2MB)'),
                ]),

            Section::make('Status & Informasi Tambahan')
                ->description('Status rental')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('status')
                            ->label(' STATUS MOBIL')
                            ->options([
                                'menunggu_persetujuan' => 'Menunggu Persetujuan',
                                'disetujui' => 'Disetujui',
                                'sedang_rental' => 'Sedang di Rental',
                                'selesai' => 'Selesai',
                                'dibatalkan' => 'Dibatalkan',
                            ])
                            ->required()
                            ->default('menunggu_persetujuan')
                            ->disabled(fn () => auth()->user()?->id !== 1),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('user.name')
            ->label('Pengguna')
            ->searchable()
            ->sortable()
            ->visible(fn () => auth()->user()?->role === 'admin'),

            TextColumn::make('car.merk')
                ->label('Mobil')
                ->searchable()
                ->sortable(),

            TextColumn::make('driver.nama')
                ->label('Driver')
                ->placeholder('Tanpa Driver')
                ->searchable(),

            TextColumn::make('tanggal_rental')
                ->label('Tanggal Rental')
                ->date('d/m/Y')
                ->sortable(),

            TextColumn::make('tanggal_kembali')
                ->label('Tanggal Kembali')
                ->date('d/m/Y')
                ->sortable(),

            TextColumn::make('jumlah_hari')
                ->label('Durasi')
                ->suffix(' hari')
                ->sortable(),

            TextColumn::make('jenis_pemakaian')
                ->label('Pemakaian')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'dalam_kota' => 'Dalam Kota',
                    'luar_kota' => 'Luar Kota',
                    default => $state,
                })
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'dalam_kota' => 'success',
                    'luar_kota' => 'warning',
                    default => 'gray',
                }),

            TextColumn::make('total_biaya')
                ->label('Total')
                ->money('IDR')
                ->sortable(),

            BadgeColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'menunggu_persetujuan' => 'Menunggu Persetujuan',
                    'disetujui' => 'Disetujui',
                    'sedang_rental' => 'Sedang di Rental',
                    'selesai' => 'Selesai',
                    'dibatalkan' => 'Dibatalkan',
                    default => $state,

                })
                ->colors([
                    'warning' => 'menunggu_persetujuan',
                    'success' => 'disetujui',
                    'primary' => 'sedang_rental',
                    'gray' => 'selesai',
                    'danger' => 'dibatalkan',
                ]),

                TextColumn::make('denda_nominal')
                ->label('Jumlah Denda')
                ->formatStateUsing(function ($record) {
                    if (!$record?->denda || !$record?->car) {
                        return 'Rp 0';
                    }

                    $tanggalKembali = \Carbon\Carbon::parse($record->tanggal_kembali);
                    $hariTerlambat = now()->gt($tanggalKembali) ? now()->diffInDays($tanggalKembali) : 0;

                    if ($hariTerlambat > 0) {
                        $dendaPerHari = $record->car->denda_per_hari ?? 0;
                        $totalDenda = $hariTerlambat * $dendaPerHari;
                        return 'Rp ' . number_format($totalDenda, 0, ',', '.');
                    }

                    return 'Rp 0';
                })
                ->sortable(),


            ImageColumn::make('bukti_pembayaran')
                ->label('Bukti')
                ->height(40)
                ->width(40)
                ->circular(),

            TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->defaultSort('created_at', 'desc')
        ->filters([
            SelectFilter::make('status')
                ->label('Status')
                ->options([
                    'menunggu_persetujuan' => 'Menunggu Persetujuan',
                    'disetujui' => 'Disetujui',
                    'sedang_rental' => 'Sedang di Rental',
                    'selesai' => 'Selesai',
                    'dibatalkan' => 'Dibatalkan',
                ]),

            SelectFilter::make('jenis_pemakaian')
                ->label('Jenis Pemakaian')
                ->options([
                    'dalam_kota' => 'Dalam Kota',
                    'luar_kota' => 'Luar Kota',
                ]),

            Filter::make('tanggal_rental')
                ->form([
                    DatePicker::make('dari_tanggal')
                        ->label('Dari Tanggal'),
                    DatePicker::make('sampai_tanggal')
                        ->label('Sampai Tanggal'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['dari_tanggal'],
                            fn (Builder $query, $date): Builder => $query->whereDate('tanggal_rental', '>=', $date),
                        )
                        ->when(
                            $data['sampai_tanggal'],
                            fn (Builder $query, $date): Builder => $query->whereDate('tanggal_rental', '<=', $date),
                        );
                }),

            SelectFilter::make('denda')
                ->label('Denda')
                ->options([
                    1 => 'Ada Denda',
                    0 => 'Tidak Ada Denda',
                ]),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalDescription('Apakah Anda yakin ingin menghapus rental ini?'),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make()
                ->requiresConfirmation()
                ->modalDescription('Apakah Anda yakin ingin menghapus rental yang dipilih?'),
        ])
        ->emptyStateActions([
            Tables\Actions\CreateAction::make(),
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
            // 'view' => Pages\ViewRental::route('/{record}'),
            'edit' => Pages\EditRental::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
{
    return parent::getEloquentQuery()
        ->when(auth()->user()?->id !== 'admin', function ($query) {
            $query->where('user_id', auth()->id());
        });
}


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'menunggu_persetujuan')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() > 0 ? 'warning' : 'primary';
    }
}