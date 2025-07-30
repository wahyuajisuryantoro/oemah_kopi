<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
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
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Pengguna';
    protected static ?string $clusterBreadcrumb = 'Manajemen Pengguna';
    protected static ?string $navigationLabel = 'Manajemen Pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create'),
                Select::make('role')
                    ->required()
                    ->options([
                        'owner' => 'Owner',
                        'kasir' => 'Kasir',
                        'dapur' => 'Dapur',
                    ]),
                Section::make('Detail Pengguna')
                    ->relationship('userDetail') 
                    ->schema([
                        TextInput::make('nama')->label('Nama Lengkap'),
                        FileUpload::make('foto')->label('Foto'),
                        TextInput::make('nomor_telepon')->label('Nomor Telepon'),
                        Textarea::make('alamat')->label('Alamat'),
                        DatePicker::make('tanggal_bergabung')->label('Tanggal Bergabung')->required(),
                        Toggle::make('aktif')->label('Status Aktif')->default(true),
                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama'),
                TextColumn::make('email'),
                TextColumn::make('role')->label('Peran/Jabatan'),
                TextColumn::make('userDetail.nomor_telepon')->label('Nomor Telp'),
                IconColumn::make('userDetail.aktif')->boolean()->label('Status'),
                TextColumn::make('created_at')->dateTime()->label('Tanggal Bergabung'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
