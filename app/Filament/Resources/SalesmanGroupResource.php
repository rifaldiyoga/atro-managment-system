<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesmanGroupResource\Pages;
use App\Models\SalesmanGroup;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;

class SalesmanGroupResource extends Resource
{
    protected static ?string $model = SalesmanGroup::class;

    protected static ?string $navigationGroup = 'Sales Management';
    protected static ?string $navigationLabel = 'Grup Salesmen';
    protected static ?string $pluralModelLabel = 'Grup Salesmen';
    protected static ?string $modelLabel = 'Grup Salesmen';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $slug = 'salesman-groups';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Section::make()
                ->columns(5)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)->columnSpan(3),
                ]),

        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('salesmen_count')
                    ->counts('salesmen')
                    ->label('Salesmen Count'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesmanGroups::route('/'),
            'create' => Pages\CreateSalesmanGroup::route('/create'),
            'edit' => Pages\EditSalesmanGroup::route('/{record}/edit'),
        ];
    }
}
