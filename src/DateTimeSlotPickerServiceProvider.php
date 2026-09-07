<?php

namespace WooServ\FilamentDateTimeSlots;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DateTimeSlotPickerServiceProvider extends PackageServiceProvider
{
    public static string $name = 'date-time-slots';

    public static string $viewNamespace = 'date-time-slots';

    public static string $assetPackageName = 'date-time-slots';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews(static::$viewNamespace);
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            assets: $this->getAssets(),
            package: static::$assetPackageName,
        );
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            AlpineComponent::make('date-time-slot-picker', __DIR__ . '/../resources/dist/components/date-time-slot-picker.js'),
            Css::make('date-time-slot-picker', __DIR__ . '/../resources/dist/components/date-time-slot-picker.css'),
        ];
    }
}
