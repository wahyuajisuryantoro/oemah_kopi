<?php

namespace App\Filament\Clusters\ManajemenMenu\Resources;

use App\Filament\Clusters\ManajemenMenu;
use App\Filament\Clusters\ManajemenMenu\Resources\MenuKategoriResource\Pages;
use App\Filament\Clusters\ManajemenMenu\Resources\MenuKategoriResource\RelationManagers;
use App\Models\MenuKategori;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MenuKategoriResource extends Resource
{
    protected static ?string $model = MenuKategori::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = ManajemenMenu::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_kategori')
                    ->required()
                    ->maxLength(255),
                Textarea::make('deskripsi'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_kategori'),
                TextColumn::make('deskripsi')->limit(50),
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
            'index' => Pages\ListMenuKategoris::route('/'),
            'create' => Pages\CreateMenuKategori::route('/create'),
            'edit' => Pages\EditMenuKategori::route('/{record}/edit'),
        ];
    }
}
