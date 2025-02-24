# Background Jobs monitoring like Horizon for all drivers for FilamentPHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/croustibat/filament-jobs-monitor.svg?style=flat-square)](https://packagist.org/packages/croustibat/filament-jobs-monitor)
[![Total Downloads](https://img.shields.io/packagist/dt/croustibat/filament-jobs-monitor.svg?style=flat-square)](https://packagist.org/packages/croustibat/filament-jobs-monitor)

This is a package to monitor background jobs for FilamentPHP. It is inspired by Laravel Horizon and is compatible with all drivers.

<img width="800" alt="Screenshot 2023-09-13 at 23 18 44" src="https://github.com/croustibat/filament-jobs-monitor/assets/1169456/a5d5a088-865f-4265-bd37-13dce213f529">
<img width="800" alt="Screenshot 2023-09-13 at 23 18 23" src="https://github.com/croustibat/filament-jobs-monitor/assets/1169456/e0a7730e-f459-4d99-8a40-5f35337385eb">


## Installation

Check your filamentPHP version before installing:

| Version | FilamentPHP | PHP                     |
|---------|-------------|-------------------------|
| 1.*     | 2.*         |       8.1.*             |
| 2.*     | 3.*         |    8.1.* \| 8.2.*       |


Install the package via composer:

```bash
composer require croustibat/filament-jobs-monitor
```

Publish and run the migrations using:

```bash
php artisan vendor:publish --tag="filament-jobs-monitor-migrations"
php artisan migrate
```

## Usage

### Configuration

The global plugin config can be published using the command below:

```bash
php artisan vendor:publish --tag="filament-jobs-monitor-config"
```

This is the content of the published config file:

```php
return [
    'resources' => [
        'enabled' => true,
        'label' => 'Job',
        'plural_label' => 'Jobs',
        'navigation_group' => 'Settings',
        'navigation_icon' => 'heroicon-o-cpu-chip',
        'navigation_sort' => null,
        'navigation_count_badge' => false,
        'resource' => Croustibat\FilamentJobsMonitor\Resources\QueueMonitorResource::class,
    ],
    'pruning' => [
        'enabled' => true,
        'retention_days' => 7,
    ],
];
```

### Using Filament Panels

If you are using Filament Panels, you can register the Plugin to your Panel configuration. This will register the plugin's resources as well as allow you to set configuration using optional chainable methods.

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            \Croustibat\FilamentJobsMonitor\FilamentJobsMonitorPlugin::make()
                ->label('Job')
                ->pluralLabel('Jobs')
                ->enableNavigation(true)
                ->navigationIcon('heroicon-o-cpu-chip')
                ->navigationGroup('Settings')
                ->navigationSort(5)
                ->navigationCountBadge(true)
                ->enablePruning(true)
                ->pruningRetention(7)
                ->resource(\App\Filament\Resources\CustomJobMonitorResource::class)
        ]);
}
```

## Usage

Just run a Background Job and go to the route `/admin/queue-monitors` to see the jobs.

## Example

Go to [example](./examples/) folder to see a Job example file.

Then you can call your Job with the following code:

```php
    public static function table(Table $table): Table
    {
        return $table

        // rest of your code
        ...

        ->bulkActions([
            BulkAction::make('export-jobs')
            ->label('Background Export')
            ->icon('heroicon-o-cog')
            ->action(function (Collection $records) {
                UsersCsvExportJob::dispatch($records, 'users.csv');
                Notification::make()
                    ->title('Export is ready')
                    ->body('Your export is ready. You can download it from the exports page.')
                    ->success()
                    ->seconds(5)
                    ->icon('heroicon-o-inbox-in')
                    ->send();
            })
        ])
    }
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Croustibat](https://github.com/croustibat)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
