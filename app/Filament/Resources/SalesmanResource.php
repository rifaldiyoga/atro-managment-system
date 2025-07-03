<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesmanResource\Pages;
use App\Models\Salesman;
use App\Models\SalesmanGroup;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;

class SalesmanResource extends Resource
{
    protected static ?string $model = Salesman::class;

    protected static ?string $navigationGroup = 'Sales Management';

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Salesmen';
    protected static ?string $pluralModelLabel = 'Salesmen';
    protected static ?string $modelLabel = 'Salesmen';

    protected static ?string $slug = 'salesmen';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Section::make()
                ->columns(5)
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->disabled()
                        ->default('AUTO')
                        ->columnSpan(2),

                    Placeholder::make('')->content(''),

                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)->columnSpan(3),



                    Forms\Components\TextInput::make('phone')
                        ->maxLength(255)->columnSpan(3),

                    Forms\Components\Select::make('salesman_group_id')
                        ->label('Salesman Group')
                        ->relationship('salesmanGroup', 'name')
                        ->nullable()->columnSpan(3),
                ])

        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('salesmanGroup.name')->label('Group')->sortable(),
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
            'index' => Pages\ListSalesmen::route('/'),
            'create' => Pages\CreateSalesman::route('/create'),
            'edit' => Pages\EditSalesman::route('/{record}/edit'),
        ];
    }
}
