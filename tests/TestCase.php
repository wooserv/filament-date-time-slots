<?php

namespace WooServ\FilamentDateTimeSlots\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use WooServ\FilamentDateTimeSlots\DateTimeSlotPickerServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            DateTimeSlotPickerServiceProvider::class,
        ];
    }
}
