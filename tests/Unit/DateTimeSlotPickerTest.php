<?php

namespace WooServ\FilamentDateTimeSlots\Tests\Unit;

use WooServ\FilamentDateTimeSlots\Forms\Components\DateTimeSlotPicker;
use WooServ\FilamentDateTimeSlots\Tests\TestCase;

final class DateTimeSlotPickerTest extends TestCase
{
    public function test_it_has_safe_slot_defaults(): void
    {
        $picker = DateTimeSlotPicker::make('due_at');

        $this->assertSame('Y-m-d H:i', $picker->getFormat());
        $this->assertSame(30, $picker->getSlotInterval());
        $this->assertSame(0, $picker->getMinimumLeadTime());
        $this->assertSame(1, $picker->getFirstDayOfWeek());
        $this->assertFalse($picker->shouldShowBlockedSlots());
        $this->assertTrue($picker->isHorizontal());
    }

    public function test_it_uses_horizontal_layout_by_default(): void
    {
        $picker = DateTimeSlotPicker::make('due_at');

        $this->assertTrue($picker->isHorizontal());
    }

    public function test_it_supports_vertical_layout(): void
    {
        $picker = DateTimeSlotPicker::make('due_at')->vertical();

        $this->assertFalse($picker->isHorizontal());
    }

    public function test_it_exposes_slot_configuration_and_supports_closures(): void
    {
        $picker = DateTimeSlotPicker::make('due_at')
            ->format('Y-m-d H:i')
            ->slotInterval(fn (): int => 15)
            ->minimumLeadTime(fn (): int => 30)
            ->workingHours(fn (): array => [
                'monday' => ['09:00', '17:00'],
            ])
            ->availableSlots(fn (): array => [
                '2026-09-07' => ['09:00', '09:15'],
            ])
            ->blockedSlots(fn (): array => [
                '2026-09-07' => ['09:15'],
            ])
            ->showBlockedSlots()
            ->weekStartsOnSunday();

        $this->assertSame(15, $picker->getSlotInterval());
        $this->assertSame(30, $picker->getMinimumLeadTime());
        $this->assertSame(['monday' => ['09:00', '17:00']], $picker->getWorkingHours());
        $this->assertSame(['2026-09-07' => ['09:00', '09:15']], $picker->getAvailableSlots());
        $this->assertSame(['2026-09-07' => ['09:15']], $picker->getBlockedSlots());
        $this->assertSame(7, $picker->getFirstDayOfWeek());
        $this->assertTrue($picker->shouldShowBlockedSlots());
    }

    public function test_it_normalizes_invalid_slot_settings(): void
    {
        $picker = DateTimeSlotPicker::make('due_at')
            ->slotInterval(0)
            ->minimumLeadTime(-15)
            ->firstDayOfWeek(99);

        $this->assertSame(1, $picker->getSlotInterval());
        $this->assertSame(0, $picker->getMinimumLeadTime());
        $this->assertSame(1, $picker->getFirstDayOfWeek());
    }

    public function test_it_supports_date_boundaries_and_disabled_dates(): void
    {
        $picker = DateTimeSlotPicker::make('due_at')
            ->minDate('2026-09-01')
            ->maxDate('2026-09-30')
            ->disabledDates(['2026-09-10']);

        $this->assertSame('2026-09-01', $picker->getMinDate());
        $this->assertSame('2026-09-30', $picker->getMaxDate());
        $this->assertSame(['2026-09-10'], $picker->getDisabledDates());
    }
}
