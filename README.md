# Filament Date Time Slots

[![Compatibility](https://github.com/wooserv/filament-date-time-slots/actions/workflows/compatibility.yml/badge.svg)](https://github.com/wooserv/filament-date-time-slots/actions/workflows/compatibility.yml)
[![Latest Version](https://img.shields.io/packagist/v/wooserv/filament-date-time-slots.svg)](https://packagist.org/packages/wooserv/filament-date-time-slots)
[![Total Downloads](https://img.shields.io/packagist/dt/wooserv/filament-date-time-slots.svg)](https://packagist.org/packages/wooserv/filament-date-time-slots)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A polished date and time slot picker for Filament forms. Build booking, appointment, reservation, task, and scheduling workflows with configurable calendars, working hours, availability rules, blocked slots, timezones, and responsive layouts.

<p align="center">
    <img class="filament-hidden" src=".github/assets/filament-date-time-slots.png" alt="Filament Date Time Slots">
</p>

## Live Demo

Try the component in a real booking experience:

**[Live Demo](https://booking.wooserv.com)** · **[Demo Source](https://github.com/wooserv/filament-date-time-slots-demo)**

The demo application also includes a complete example of using the picker outside a Filament admin panel with a standalone Livewire component.

## Why use it?

- Calendar-based date selection with configurable minimum and maximum dates.
- Generate slots from weekly working hours or provide exact slots for each date.
- Mark booked slots as unavailable, with an option to show them in the picker.
- Configure slot intervals and minimum lead time for advance booking rules.
- Disable past dates and past slots automatically.
- Support 12-hour and 24-hour display formats without changing the stored value.
- Work with Filament timezones, custom locales, RTL, and dark mode.
- Responsive horizontal layout with an automatic vertical fallback on narrow containers.
- Independently scrollable time slots for long schedules.
- Store one predictable datetime value without requiring a separate appointment table.

## Requirements

- PHP 8.2 or higher
- Laravel 11, 12, or 13
- Filament 3.2, 4, or 5

Supported framework combinations are tested continuously in CI.

## Installation

Install the package with Composer:

```bash
composer require wooserv/filament-date-time-slots
```

Laravel package discovery registers the service provider automatically. Compiled JavaScript and CSS are included in the package, so Node.js is not required for normal usage.

Views can be published when you need to customize the package view:

```bash
php artisan vendor:publish --tag="date-time-slots-views"
```

## Basic usage

Add the picker to any Filament form schema:

```php
use WooServ\FilamentDateTimeSlots\Forms\Components\DateTimeSlotPicker;

DateTimeSlotPicker::make('due_at')
    ->label('Due at')
    ->format('Y-m-d H:i')
    ->minDate(now())
    ->minimumLeadTime(30)
    ->slotInterval(30)
    ->workingHours([
        'sunday' => ['09:00', '17:00'],
        'monday' => ['09:00', '17:00'],
    ])
    ->blockedSlots([
        '2026-09-07' => ['10:30', '12:00'],
    ])
    ->required();
```

The selected value is stored using the format passed to `format()`. The 12-hour/24-hour display switch only changes presentation; it does not change the stored value.

## Exact availability

When your application already knows which slots are available for each date, use `availableSlots()` instead of generating slots from working hours:

```php
DateTimeSlotPicker::make('due_at')
    ->availableSlots([
        '2026-09-07' => ['09:00', '09:30', '11:00'],
    ]);
```

`availableSlots()` takes priority over generated working-hour slots for matching dates. Use `blockedSlots()` to mark booked times as unavailable. By default, blocked slots are hidden; call `showBlockedSlots()` to display them in the picker.

## Configuration

```php
DateTimeSlotPicker::make('due_at')
    ->format('Y-m-d H:i')
    ->firstDayOfWeek(1)
    ->weekStartsOnMonday()
    ->minDate(now())
    ->maxDate(now()->addMonths(3))
    ->disabledDates(['2026-09-18'])
    ->timezone('Africa/Cairo')
    ->locale('en')
    ->slotInterval(30)
    ->minimumLeadTime(60)
    ->showBlockedSlots();
```

### Available methods

| Method | Purpose |
| --- | --- |
| `format()` | Set the stored datetime format. Defaults to `Y-m-d H:i`. |
| `workingHours()` | Generate slots from weekday start/end times. |
| `availableSlots()` | Provide exact available times keyed by date. |
| `blockedSlots()` | Mark specific date/time slots as booked or unavailable. |
| `showBlockedSlots()` | Display blocked slots instead of hiding them. |
| `slotInterval()` | Set the generated slot interval in minutes. |
| `minimumLeadTime()` | Require selections to be a number of minutes in the future. |
| `minDate()` / `maxDate()` | Set selectable calendar boundaries. |
| `disabledDates()` | Disable specific dates. |
| `timezone()` / `locale()` | Override the active timezone or locale. |
| `weekStartsOnMonday()` / `weekStartsOnSunday()` | Set the first day of the week. |
| `vertical()` | Force the vertical layout at every width. |

Closures are supported by configuration methods where dynamic values are useful.

## Responsive layouts

The picker uses a horizontal layout by default when its own container is at least `48rem` wide. On smaller containers, including mobile layouts, it automatically switches to a vertical layout. This container-aware behavior also keeps narrow Filament grid columns usable on large screens.

Force the vertical layout when needed:

```php
DateTimeSlotPicker::make('due_at')
    ->vertical();
```

Time slots have an independent scroll area in both layouts, so a large number of slots does not expand the entire form.

## Using outside a Filament panel

`DateTimeSlotPicker` can also be used in standalone Livewire components that use Filament Forms. This makes it suitable for public-facing booking flows as well as Filament admin forms.

```php
<?php

namespace App\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use WooServ\FilamentDateTimeSlots\Forms\Components\DateTimeSlotPicker;

class BookingForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DateTimeSlotPicker::make('starts_at')
                    ->label('Choose a time')
                    ->workingHours([
                        'sunday' => ['09:00', '17:00'],
                        'monday' => ['09:00', '17:00'],
                    ])
                    ->minimumLeadTime(30)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Create the booking using $data['starts_at'].
    }
}
```

See the [demo application](https://github.com/wooserv/filament-date-time-slots-demo) for a complete public-facing implementation, or try it in the [live demo](https://booking.wooserv.com).

## Testing

```bash
composer install
composer test
```

Format the PHP source with the package coding rules:

```bash
composer format
```

The test suite uses PHPUnit and Orchestra Testbench. CI tests the supported Filament and Laravel combinations rather than relying on one locally installed framework version.

## Building assets

Distributable assets are committed to `resources/dist`. Rebuild them only when changing the frontend source:

```bash
npm install
npm run build
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for a complete history of changes.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for contribution guidelines.

## Security

Please review [our security policy](.github/SECURITY.md) to report security vulnerabilities.

## Credits

Maintained by [WooServ, LLC](https://wooserv.com):

- [WooServ](https://github.com/wooserv)
- [Hamada Habib](https://github.com/ihfbib)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). See the [License File](LICENSE) for more information.
