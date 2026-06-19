<?php

namespace App\Filament\Resources\Streams\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatMessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'chatMessages';

    protected static ?string $title = 'Live Chat';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime('g:i:s A')
                    ->label('Time')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User'),
                TextColumn::make('content')
                    ->label('Message')
                    ->wrap()
                    ->limit(120),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50])
            ->poll('5s');
    }
}
