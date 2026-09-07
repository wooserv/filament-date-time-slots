@php
    use Filament\Support\Enums\VerticalAlignment;
    use Filament\Support\Facades\FilamentAsset;
    use Filament\Support\Facades\FilamentView;
    use WooServ\FilamentDateTimeSlots\DateTimeSlotPickerServiceProvider;

    use function Filament\Support\prepare_inherited_attributes;

    $fieldWrapperView = $getFieldWrapperView();
    $extraAlpineAttributes = $getExtraAlpineAttributes();
    $extraAttributeBag = $getExtraAttributeBag();
    $id = $getId();
    $isDisabled = $isDisabled();
    $maxDate = $getMaxDate();
    $minDate = $getMinDate();
    $statePath = $getStatePath();
    $livewireKey = $getLivewireKey();
@endphp

<x-dynamic-component
    :component="$fieldWrapperView"
    :field="$field"
    :inline-label-vertical-alignment="VerticalAlignment::Center"
>
    <x-filament::input.wrapper
        :disabled="$isDisabled"
        :valid="! $errors->has($statePath)"
        :attributes="prepare_inherited_attributes($extraAttributeBag)->class(['fi-fo-inline-date-picker'])"
    >
        <div
            x-load
            x-load-src="{{ FilamentAsset::getAlpineComponentSrc('date-time-slot-picker', DateTimeSlotPickerServiceProvider::$assetPackageName) }}"
            x-data="dateTimeSlotPickerFormComponent({
                availableSlots: @js($getAvailableSlots()),
                blockedSlots: @js($getBlockedSlots()),
                disabledDates: @js($getDisabledDates()),
                firstDayOfWeek: {{ $getFirstDayOfWeek() }},
                locale: @js($getLocale()),
                maxDate: @js($getMaxDate()),
                minDate: @js($getMinDate()),
                minimumLeadTime: {{ $getMinimumLeadTime() }},
                showBlockedSlots: @js($shouldShowBlockedSlots()),
                slotInterval: {{ $getSlotInterval() }},
                state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
                workingHours: @js($getWorkingHours()),
            })"
            wire:ignore
            wire:key="{{ $livewireKey }}.{{
                substr(md5(serialize([
                    $isDisabled,
                    $maxDate,
                    $minDate,
                ])), 0, 64)
            }}"
            {{ $getExtraAlpineAttributeBag() }}
        >
            <input x-ref="maxDate" type="hidden" value="{{ $maxDate }}" />

            <input x-ref="minDate" type="hidden" value="{{ $minDate }}" />

            <input
                x-ref="disabledDates"
                type="hidden"
                value="{{ json_encode($getDisabledDates()) }}"
            />

            <div
                x-ref="panel"
                x-cloak
                wire:ignore
                wire:key="{{ $livewireKey }}.panel"
                @class([
                    'fi-fo-inline-date-picker-panel',
                ])
            >
                    <div class="fi-fo-inline-date-picker-panel-header">
                        <button
                            type="button"
                            x-on:click="focusPreviousMonth"
                            class="fi-fo-inline-date-picker-month-button"
                            aria-label="Previous month"
                        >
                            ‹
                        </button>

                        <div class="fi-fo-inline-date-picker-month-label">
                            <span x-text="focusedDate.format('MMMM')"></span>
                            <span x-text="focusedYear"></span>
                        </div>

                        <button
                            type="button"
                            x-on:click="focusNextMonth"
                            class="fi-fo-inline-date-picker-month-button"
                            aria-label="Next month"
                        >
                            ›
                        </button>
                    </div>

                    <div class="fi-fo-inline-date-picker-calendar-header">
                        <template
                            x-for="(day, index) in dayLabels"
                            x-bind:key="index"
                        >
                            <div
                                x-text="day"
                                class="fi-fo-inline-date-picker-calendar-header-day"
                            ></div>
                        </template>
                    </div>

                    <div role="grid" class="fi-fo-inline-date-picker-calendar">
                        <template
                            x-for="day in emptyDaysInFocusedMonth"
                            x-bind:key="day"
                        >
                            <div></div>
                        </template>

                        <template
                            x-for="day in daysInFocusedMonth"
                            x-bind:key="day"
                        >
                            <div
                                x-text="day"
                                x-on:click="selectDate(day)"
                                role="option"
                                x-bind:aria-selected="focusedDate.date() === day"
                                x-bind:class="{
                                    'fi-fo-inline-date-picker-calendar-day-today': dayIsToday(day),
                                    'fi-focused': focusedDate.date() === day,
                                    'fi-selected': dayIsSelected(day),
                                    'fi-disabled': dayIsDisabled(day),
                                }"
                                class="fi-fo-inline-date-picker-calendar-day"
                            ></div>
                        </template>
                    </div>

                <div class="fi-fo-inline-date-picker-slots">
                    <div class="fi-fo-inline-date-picker-slots-header">
                        <span x-text="focusedDate.format('dddd, D MMMM')"></span>
                        <div class="fi-fo-inline-date-picker-slots-meta">
                            <span x-text="slots.filter(slot => typeof slot === 'string' || !slot.blocked).length + ' available'"></span>
                            <div class="fi-fo-inline-date-picker-format-toggle" role="group" aria-label="Time format">
                                <button
                                    type="button"
                                    x-on:click="timeFormat = '12h'"
                                    x-bind:class="{ 'fi-selected': timeFormat === '12h' }"
                                >
                                    12h
                                </button>
                                <button
                                    type="button"
                                    x-on:click="timeFormat = '24h'"
                                    x-bind:class="{ 'fi-selected': timeFormat === '24h' }"
                                >
                                    24h
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="fi-fo-inline-date-picker-slot-grid" x-show="slots.length > 0">
                        <template x-for="slot in slots" x-bind:key="typeof slot === 'string' ? slot : slot.time">
                            <button
                                type="button"
                                x-on:click="typeof slot === 'string' || !slot.blocked ? selectSlot(typeof slot === 'string' ? slot : slot.time) : null"
                                x-text="formatSlot(typeof slot === 'string' ? slot : slot.time)"
                                x-bind:class="{ 'fi-selected': selectedDateKey === getDateKey(focusedDate) && selectedSlot === (typeof slot === 'string' ? slot : slot.time), 'fi-booked': typeof slot !== 'string' && slot.blocked }"
                                x-bind:disabled="typeof slot !== 'string' && slot.blocked"
                                class="fi-fo-inline-date-picker-slot"
                            ></button>
                        </template>
                    </div>

                    <div
                        x-show="slots.length === 0"
                        x-cloak
                        class="fi-fo-inline-date-picker-empty-slots"
                    >
                        No available time slots for this day.
                    </div>
                </div>
            </div>
        </div>
    </x-filament::input.wrapper>
</x-dynamic-component>
