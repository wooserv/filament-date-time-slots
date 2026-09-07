<?php

namespace WooServ\FilamentDateTimeSlots\Forms\Components;

use Carbon\CarbonInterface;
use Carbon\Exceptions\InvalidFormatException;
use Closure;
use DateTime;
use Filament\Forms\Components\Concerns;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Support\Carbon;
use Illuminate\View\ComponentAttributeBag;

class DateTimeSlotPicker extends Field
{
    use Concerns\CanBeReadOnly;
    use Concerns\HasExtraInputAttributes;
    use HasExtraAlpineAttributes;

    protected string $view = 'date-time-slots::forms.components.date-time-slot-picker';

    protected ?int $firstDayOfWeek = null;

    protected string | Closure | null $format = null;

    protected CarbonInterface | string | Closure | null $maxDate = null;

    protected CarbonInterface | string | Closure | null $minDate = null;

    protected string | Closure | null $timezone = null;

    protected string | Closure | null $locale = null;

    /** @var array<DateTime | string> | Closure */
    protected array | Closure $disabledDates = [];

    /** @var array<string, mixed> | Closure */
    protected array | Closure $workingHours = [];

    /** @var array<string, array<string> | Closure> | Closure */
    protected array | Closure $availableSlots = [];

    /** @var array<string, array<string> | Closure> | Closure */
    protected array | Closure $blockedSlots = [];

    protected int | Closure $slotInterval = 30;

    protected int | Closure $minimumLeadTime = 0;

    protected bool | Closure $showBlockedSlots = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(static function (DateTimeSlotPicker $component, $state): void {
            if (blank($state)) {
                return;
            }

            if (! $state instanceof CarbonInterface) {
                try {
                    $state = Carbon::createFromFormat($component->getFormat(), (string) $state, config('app.timezone'));
                } catch (InvalidFormatException) {
                    try {
                        $state = Carbon::parse($state, config('app.timezone'));
                    } catch (InvalidFormatException) {
                        $component->state(null);

                        return;
                    }
                }
            }

            $component->state((string) $state->setTimezone($component->getTimezone()));
        });

        $this->dehydrateStateUsing(static function (DateTimeSlotPicker $component, $state) {
            if (blank($state)) {
                return null;
            }

            $state = $state instanceof CarbonInterface ? $state : Carbon::parse($state);
            $state->shiftTimezone($component->getTimezone());
            $state->setTimezone(config('app.timezone'));

            return $state->format($component->getFormat());
        });

        $this->rule('date', static fn (DateTimeSlotPicker $component): bool => $component->getFormat() !== '');
    }

    public function firstDayOfWeek(?int $day): static
    {
        $this->firstDayOfWeek = $day !== null && $day >= 0 && $day <= 7 ? $day : null;

        return $this;
    }

    public function format(string | Closure | null $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function maxDate(CarbonInterface | string | Closure | null $date): static
    {
        $this->maxDate = $date;
        $this->rule(static fn (DateTimeSlotPicker $component): string => "before_or_equal:{$component->getMaxDate()}", static fn (DateTimeSlotPicker $component): bool => (bool) $component->getMaxDate());

        return $this;
    }

    public function minDate(CarbonInterface | string | Closure | null $date): static
    {
        $this->minDate = $date;
        $this->rule(static fn (DateTimeSlotPicker $component): string => "after_or_equal:{$component->getMinDate()}", static fn (DateTimeSlotPicker $component): bool => (bool) $component->getMinDate());

        return $this;
    }

    /** @param array<DateTime | string> | Closure $dates */
    public function disabledDates(array | Closure $dates): static
    {
        $this->disabledDates = $dates;

        return $this;
    }

    /** @param array<string, mixed> | Closure $hours */
    public function workingHours(array | Closure $hours): static
    {
        $this->workingHours = $hours;

        return $this;
    }

    /** @param array<string, array<string> | Closure> | Closure $slots */
    public function availableSlots(array | Closure $slots): static
    {
        $this->availableSlots = $slots;

        return $this;
    }

    /** @param array<string, array<string> | Closure> | Closure $slots */
    public function blockedSlots(array | Closure $slots): static
    {
        $this->blockedSlots = $slots;

        return $this;
    }

    public function slotInterval(int | Closure $minutes): static
    {
        $this->slotInterval = $minutes;

        return $this;
    }

    /**
     * Require every selected slot to be at least this many minutes in the future.
     * This also disables dates before the earliest selectable date.
     */
    public function minimumLeadTime(int | Closure $minutes): static
    {
        $this->minimumLeadTime = $minutes;

        $this->rule(
            static fn (DateTimeSlotPicker $component): string => 'after_or_equal:' . now()->addMinutes($component->getMinimumLeadTime())->toDateTimeString(),
            static fn (DateTimeSlotPicker $component): bool => $component->getMinimumLeadTime() > 0,
        );

        return $this;
    }

    public function showBlockedSlots(bool | Closure $condition = true): static
    {
        $this->showBlockedSlots = $condition;

        return $this;
    }

    public function weekStartsOnMonday(): static
    {
        return $this->firstDayOfWeek(1);
    }

    public function weekStartsOnSunday(): static
    {
        return $this->firstDayOfWeek(7);
    }

    public function timezone(string | Closure | null $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function locale(string | Closure | null $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getFirstDayOfWeek(): int
    {
        return $this->firstDayOfWeek ?? 1;
    }

    public function getFormat(): string
    {
        return $this->evaluate($this->format) ?: 'Y-m-d H:i';
    }

    public function getMaxDate(): ?string
    {
        return $this->evaluate($this->maxDate);
    }

    public function getMinDate(): ?string
    {
        return $this->evaluate($this->minDate);
    }

    /** @return array<DateTime | string> */
    public function getDisabledDates(): array
    {
        return $this->evaluate($this->disabledDates);
    }

    /** @return array<string, mixed> */
    public function getWorkingHours(): array
    {
        return $this->evaluate($this->workingHours) ?: [];
    }

    /** @return array<string, array<string> | Closure> */
    public function getAvailableSlots(): array
    {
        return $this->evaluate($this->availableSlots) ?: [];
    }

    /** @return array<string, array<string> | Closure> */
    public function getBlockedSlots(): array
    {
        return $this->evaluate($this->blockedSlots) ?: [];
    }

    public function getSlotInterval(): int
    {
        return max(1, (int) $this->evaluate($this->slotInterval));
    }

    public function getMinimumLeadTime(): int
    {
        return max(0, (int) $this->evaluate($this->minimumLeadTime));
    }

    public function shouldShowBlockedSlots(): bool
    {
        return (bool) $this->evaluate($this->showBlockedSlots);
    }

    public function getTimezone(): string
    {
        return $this->evaluate($this->timezone) ?? FilamentTimezone::get();
    }

    public function getLocale(): string
    {
        return $this->evaluate($this->locale) ?? config('app.locale');
    }

    public function getExtraTriggerAttributeBag(): ComponentAttributeBag
    {
        return new ComponentAttributeBag;
    }
}
