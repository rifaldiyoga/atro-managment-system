<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessPartnerResource\Pages;
use App\Filament\Resources\BusinessPartnerResource\RelationManagers;
use App\Models\BusinessPartner;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Kode')
                    ->required(),

                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required(),

                Forms\Components\TextInput::make('phone')
                    ->label('Nomor Telepon')
                    ->tel(),


                Forms\Components\Select::make('partner_type')
                    ->label('Tipe Partner')
                    ->options([
                        'supplier' => 'Supplier',
                        'customer' => 'Customer',
                    ])
                    ->required()
                    ->native(false),


                Forms\Components\Textarea::make('address')
                    ->label('Alamat'),

                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi'),

                FileUpload::make('photo_url')
                    ->label('URL Foto'),


                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),



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
                Tables\Columns\TextColumn::make('partner_type')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
