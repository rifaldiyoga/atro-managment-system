<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessPartnerResource\Pages;
use App\Filament\Resources\BusinessPartnerResource\RelationManagers;
use App\Models\BusinessPartner;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusinessPartnerResource extends Resource
{
    protected static ?string $model = BusinessPartner::class;

    protected static ?string $navigationGroup = 'Master';
    protected static ?string $navigationLabel = 'Mitra Bisnis';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $pluralModelLabel = 'Mitra Bisnis';
    protected static ?string $modelLabel = 'Mitra Bisnis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->columns(12)
                    ->schema([
                        Section::make()
                            ->schema([
                                Grid::make(4)->schema([
                                    Forms\Components\TextInput::make('code')
                                        ->label('Kode')
                                        ->disabled()
                                        ->default('AUTO')
                                        ->required()
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('name')
                                        ->label('Nama')
                                        ->required()
                                        ->columnSpan(3),


                                    Forms\Components\Select::make('partner_type')
                                        ->label('Tipe Partner')
                                        ->options([
                                            'supplier' => 'Supplier',
                                            'customer' => 'Customer',
                                        ])
                                        ->hidden(fn() => filled(request()->query('activeTab')))
                                        ->required()
                                        ->native(false)
                                        ->columnSpan(3),

                                    Forms\Components\TextInput::make('phone')
                                        ->label('Nomor Telepon')
                                        ->tel()
                                        ->columnSpan(3),



                                    Forms\Components\Textarea::make('address')
                                        ->label('Alamat')->columnSpan(4),

                                    Forms\Components\Textarea::make('description')
                                        ->label('Deskripsi')->columnSpan(4),

                                    Forms\Components\Toggle::make('is_active')
                                        ->label('Aktif')
                                        ->default(true)
                                        ->required(),
                                ])

                            ])
                            ->columns(2)
                            ->columnSpan(8),

                        // 🖼️ Panel Kanan: Upload Gambar
                        Section::make()
                            ->schema([
                                FileUpload::make('photo_url')
                                    ->label('URL Foto'),
                            ])
                            ->columnSpan(4),
                    ]),







            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // MorphToSelect::make('commentable')
                //     ->types([
                //         MorphToSelect\Type::make(BusinessPartner::class)
                //             ->getOptionLabelFromRecordUsing(fn(BusinessPartner $record): string => "{$record->name} - {$record->slug}"),
                //         MorphToSelect\Type::make(BusinessPartner::class)
                //             ->titleAttribute('title'),
                //     ]),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinessPartners::route('/'),
            'create' => Pages\CreateBusinessPartner::route('/create'),
            'edit' => Pages\EditBusinessPartner::route('/{record}/edit'),
        ];
    }
}
