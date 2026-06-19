<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    // ->description('Below are the user information')
                    ->columns(['sm' => 1, 'md' => 12, 'xl' => 12])
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(190)
                            ->columnSpan(['sm' => 12, 'md' => 4, 'xl' => 4]),

                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->maxLength(190)
                            ->columnSpan(['sm' => 12, 'md' => 4, 'xl' => 4]),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(191)
                            ->default(fn(callable $get) => strtolower(preg_replace('/\s+/', '_', trim($get('name') ?? ''))) . '@zionstream.com')
                            ->rules(function ($record) {
                                return [
                                    Rule::unique('users', 'email')->ignore($record?->id),
                                ];
                            })
                            // ->rules(['unique:users,email'])
                            ->columnSpan(['sm' => 12, 'md' => 4, 'xl' => 4]),

                        TextInput::make('password')
                            ->password()
                            ->maxLength(190)
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->columnSpan(['sm' => 12, 'md' => 4, 'xl' => 4]),



                    ]),



                Section::make('Roles')
                    ->description("You can assign multiple role to each user.")
                    ->schema([

                        Select::make('roles')
                            ->label('')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }
}
