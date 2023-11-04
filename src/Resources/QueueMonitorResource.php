<?php

namespace Croustibat\FilamentJobsMonitor\Resources;

use Croustibat\FilamentJobsMonitor\FilamentJobsMonitorPlugin;
use Croustibat\FilamentJobsMonitor\Models\QueueMonitor;
use Croustibat\FilamentJobsMonitor\Resources\QueueMonitorResource\Pages;
use Croustibat\FilamentJobsMonitor\Resources\QueueMonitorResource\Widgets\QueueStatsOverview;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class QueueMonitorResource extends Resource
{
    protected static ?string $model = QueueMonitor::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('job_id')
                    ->label('Job ID')
                    ->required()
                    ->maxLength(255),
                TextInput::make('name')
                    ->label('Job Name')
                    ->maxLength(255)
                    ->formatStateUsing(fn(string $state): string => str($state)->explode("\\")->last()),
                TextInput::make('connection')
                    ->label(__('filament-jobs-monitor::translations.connection'))
                    ->maxLength(255)
                    ->formatStateUsing(fn(?string $state): string => $state ?? config('queue.default')),
                TextInput::make('queue')
                    ->label(__('filament-jobs-monitor::translations.queue'))
                    ->maxLength(255),
                TextInput::make('started_at')
                    ->label(__('filament-jobs-monitor::translations.started_at'))
                    ->formatStateUsing(fn(string $state): string => Carbon::parse($state)->toDateTimeString()),
                TextInput::make('finished_at')
                    ->label(__('filament-jobs-monitor::translations.finished_at'))
                    ->formatStateUsing(fn(string $state): string => Carbon::parse($state)->toDateTimeString()),
                TextInput::make('attempt')
                    ->required(),
                TextInput::make('failed')
                    ->label(__('filament-jobs-monitor::translations.status'))
                    ->formatStateUsing(fn(bool $state): string => $state ? "Failed" : "Success"),
                Textarea::make('payload')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->autosize(),
                Textarea::make('exception_message')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->autosize(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->label(__('filament-jobs-monitor::translations.status'))
                    ->sortable(['failed'])
                    ->formatStateUsing(fn(string $state): string => __("filament-jobs-monitor::translations.{$state}"))
                    ->color(fn(string $state): string => match ($state) {
                        'success' => 'success',
                        'running' => 'primary',
                        'failed' => 'danger',
                    }),
                TextColumn::make('name')
                    ->label(__('filament-jobs-monitor::translations.name'))
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => str($state)->explode("\\")->last())
                    ->description(fn(string $state): string => str($state)->explode("\\")->slice(0, -1)->implode('\\')),
                TextColumn::make('connection')
                    ->label(__('filament-jobs-monitor::translations.connection'))
                    ->sortable()
                    ->searchable()
                    ->default(config('queue.default')),
                TextColumn::make('queue')
                    ->label(__('filament-jobs-monitor::translations.queue'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('progress')
                    ->badge()
                    ->label(__('filament-jobs-monitor::translations.progress'))
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn(string $state) => "{$state}%")
                    ->color(fn(string $state): string => match (true) {
                        $state >= 70 => 'success',
                        $state >= 30 => 'primary',
                        default => 'danger',
                    }),
                TextColumn::make('started_at')
                    ->label(__('filament-jobs-monitor::translations.started_at'))
                    ->since()
                    ->sortable(),
                TextColumn::make('finished_at')
                    ->label(__('filament-jobs-monitor::translations.finished_at'))
                    ->since()
                    ->sortable(),
            ])
            ->poll()
            ->defaultSort('started_at', 'desc')
            ->actions([
                ViewAction::make()
                    ->button(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return FilamentJobsMonitorPlugin::get()->getNavigationCountBadge() ? number_format(static::getModel()::count()) : null;
    }

    public static function getModelLabel(): string
    {
        return FilamentJobsMonitorPlugin::get()->getLabel();
    }

    public static function getPluralModelLabel(): string
    {
        return FilamentJobsMonitorPlugin::get()->getPluralLabel();
    }

    public static function getNavigationLabel(): string
    {
        return Str::title(static::getPluralModelLabel()) ?? Str::title(static::getModelLabel());
    }

    public static function getNavigationGroup(): ?string
    {
        return FilamentJobsMonitorPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return FilamentJobsMonitorPlugin::get()->getNavigationSort();
    }

    public static function getBreadcrumb(): string
    {
        return FilamentJobsMonitorPlugin::get()->getBreadcrumb();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentJobsMonitorPlugin::get()->shouldRegisterNavigation();
    }

    public static function getNavigationIcon(): string
    {
        return FilamentJobsMonitorPlugin::get()->getNavigationIcon();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQueueMonitors::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            QueueStatsOverview::class,
        ];
    }
}
