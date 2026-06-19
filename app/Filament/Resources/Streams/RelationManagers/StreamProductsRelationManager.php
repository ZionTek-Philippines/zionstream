<?php

namespace App\Filament\Resources\Streams\RelationManagers;

use App\Models\StreamProduct;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StreamProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'streamProducts';

    protected static ?string $title = 'Products';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('product_id')
                ->relationship('product', 'name')
                ->required()
                ->columnSpanFull(),
            TextInput::make('featured_price')
                ->label('Sale Price (optional — overrides product price)')
                ->numeric()
                ->prefix('₱'),
            TextInput::make('display_order')
                ->integer()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-s-signal')
                    ->falseIcon('heroicon-o-signal-slash')
                    ->trueColor('success'),
                TextColumn::make('product.name')
                    ->label('Product'),
                TextColumn::make('product.price')
                    ->money('PHP')
                    ->label('Base Price'),
                TextColumn::make('featured_price')
                    ->money('PHP')
                    ->label('Sale Price')
                    ->placeholder('—'),
                TextColumn::make('activated_at')
                    ->dateTime('g:i A')
                    ->label('Activated')
                    ->placeholder('—'),
            ])
            ->defaultSort('display_order')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('activate')
                    ->label('Set Active')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function (StreamProduct $record): void {
                        StreamProduct::where('stream_id', $record->stream_id)
                            ->update(['is_active' => false]);
                        $record->update(['is_active' => true, 'activated_at' => now()]);
                    })
                    ->hidden(fn(StreamProduct $record): bool => $record->is_active),
                Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-stop')
                    ->color('gray')
                    ->action(fn(StreamProduct $record) => $record->update(['is_active' => false]))
                    ->visible(fn(StreamProduct $record): bool => $record->is_active),
            ]);
    }
}
