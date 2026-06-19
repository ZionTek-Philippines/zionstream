<?php

namespace App\Filament\Resources\Streams\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StreamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('channel_id')
                ->relationship('channel', 'name')
                ->required()
                ->columnSpanFull(),
            TextInput::make('title')
                ->required()
                ->columnSpanFull(),
            Textarea::make('description')
                ->rows(2)
                ->columnSpanFull(),
            TextInput::make('agora_channel_name')
                ->label('Agora Channel Name')
                ->required()
                ->helperText('Must match the channel name used by the broadcaster.'),
            TextInput::make('agora_uid')
                ->label('Agora Host UID')
                ->numeric()
                ->helperText('Unique integer ID for the streamer/host.'),
            Select::make('status')
                ->options([
                    'scheduled' => 'Scheduled',
                    'live'      => 'Live',
                    'ended'     => 'Ended',
                ])
                ->default('scheduled')
                ->required(),
            TextInput::make('peak_viewer_count')
                ->label('Peak Viewers')
                ->numeric()
                ->default(0),
            DateTimePicker::make('scheduled_at'),
            DateTimePicker::make('started_at'),
            DateTimePicker::make('ended_at'),
        ]);
    }
}
