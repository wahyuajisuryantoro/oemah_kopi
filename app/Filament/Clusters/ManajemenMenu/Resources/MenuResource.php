<?php

namespace App\Filament\Clusters\ManajemenMenu\Resources;

use App\Filament\Clusters\ManajemenMenu;
use App\Filament\Clusters\ManajemenMenu\Resources\MenuResource\Pages;
use App\Filament\Clusters\ManajemenMenu\Resources\MenuResource\RelationManagers;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = ManajemenMenu::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_menu')
                    ->required()
                    ->maxLength(255),
                Textarea::make('deskripsi'),
                TextInput::make('harga_jual')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('diskon')
                    ->numeric()
                    ->suffix('%'),
                TextInput::make('waktu_masak')
                    ->required()
                    ->numeric()
                    ->suffix('menit'),
                Toggle::make('tersedia')
                    ->required(),
                Select::make('id_kategori')
                    ->relationship('kategori', 'nama_kategori')
                    ->required(),
                FileUpload::make('gambar_url'),
                TextInput::make('stok')
                    ->required()
                    ->numeric(),
                TextInput::make('harga_modal')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_menu'),
                TextColumn::make('harga_jual')
                    ->money('idr'),
                TextColumn::make('kategori.nama_kategori'),
                IconColumn::make('tersedia')
                    ->boolean(),
                TextColumn::make('stok'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'view' => Pages\ViewMenu::route('/{record}'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
