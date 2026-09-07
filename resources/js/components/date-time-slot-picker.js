import dayjs from 'dayjs/esm'
import advancedFormat from 'dayjs/plugin/advancedFormat'
import customParseFormat from 'dayjs/plugin/customParseFormat'
import localeData from 'dayjs/plugin/localeData'
import timezonePlugin from 'dayjs/plugin/timezone'
import utc from 'dayjs/plugin/utc'

dayjs.extend(advancedFormat)
dayjs.extend(customParseFormat)
dayjs.extend(localeData)
dayjs.extend(timezonePlugin)
dayjs.extend(utc)

window.dayjs = dayjs

export default function dateTimeSlotPickerFormComponent({
    availableSlots,
    blockedSlots,
    disabledDates,
    firstDayOfWeek,
    locale,
    maxDate,
    minDate,
    minimumLeadTime,
    showBlockedSlots,
    slotInterval,
    state,
    workingHours,
}) {
    const timezone = dayjs.tz.guess()
    const weekStart = firstDayOfWeek === 7 ? 0 : firstDayOfWeek

    return {
        availableSlots,
        blockedSlots,
        dayLabels: [],
        daysInFocusedMonth: [],
        disabledDates,
        emptyDaysInFocusedMonth: [],
        focusedDate: null,
        focusedMonth: null,
        focusedYear: null,
        isSelecting: false,
        locale,
        maxDate,
        minDate,
        minimumLeadTime,
        selectedDateKey: null,
        selectedSlot: null,
        showBlockedSlots,
        timeFormat: '24h',
        slotInterval,
        slots: [],
        state,
        workingHours,

        init() {
            dayjs.locale(locales[this.locale] ?? locales.en)

            const selectedDate = this.getSelectedDate()
            this.focusedDate = selectedDate ?? this.getMinDate() ?? dayjs().tz(timezone)
            this.selectedDateKey = selectedDate?.format('YYYY-MM-DD') ?? null
            this.selectedSlot = selectedDate?.format('HH:mm') ?? null

            this.setMonths()
            this.setDayLabels()

            this.$watch('focusedMonth', () => {
                this.focusedMonth = +this.focusedMonth
                if (this.focusedDate.month() !== this.focusedMonth) {
                    this.focusedDate = this.focusedDate.month(this.focusedMonth)
                }
            })

            this.$watch('focusedYear', () => {
                if (this.focusedYear?.length > 4) {
                    this.focusedYear = this.focusedYear.substring(0, 4)
                }

                if (!this.focusedYear || this.focusedYear.length !== 4) {
                    return
                }

                const year = Number(this.focusedYear)
                if (Number.isInteger(year) && this.focusedDate.year() !== year) {
                    this.focusedDate = this.focusedDate.year(year)
                }
            })

            this.$watch('focusedDate', () => {
                this.focusedMonth = this.focusedDate.month()
                this.focusedYear = this.focusedDate.year()
                this.setupDaysGrid()

                if (!this.selectedDateKey) {
                    this.selectedDateKey = this.getDateKey(this.focusedDate)
                }

                this.refreshSlots()
            })

            this.$watch('state', () => this.syncState())

            this.renderPanel()
        },

        dateIsDisabled(date) {
            const disabled = (this.disabledDates ?? []).some((value) => {
                const disabledDate = dayjs(value)
                return disabledDate.isValid() && disabledDate.isSame(date, 'day')
            })

            if (disabled) {
                return true
            }

            if (this.getMaxDate()?.isBefore(date, 'day')) {
                return true
            }

            if (this.getMinDate()?.isAfter(date, 'day')) {
                return true
            }

            return !this.getSlotsForDate(date).some((slot) =>
                typeof slot === 'string' || !slot.blocked,
            )
        },

        dayIsDisabled(day) {
            return this.dateIsDisabled(this.focusedDate.date(day))
        },

        dayIsSelected(day) {
            return this.selectedDateKey === this.focusedDate.date(day).format('YYYY-MM-DD')
        },

        dayIsToday(day) {
            return dayjs().tz(timezone).isSame(this.focusedDate.date(day), 'day')
        },

        focusPreviousMonth() {
            this.focusedDate = this.focusedDate.subtract(1, 'month')
        },

        focusNextMonth() {
            this.focusedDate = this.focusedDate.add(1, 'month')
        },

        getDateKey(date) {
            return date.format('YYYY-MM-DD')
        },

        getDayLabels() {
            const labels = dayjs.weekdaysShort()
            return [...labels.slice(weekStart), ...labels.slice(0, weekStart)]
        },

        getMaxDate() {
            const date = dayjs(this.maxDate)
            return date.isValid() ? date : null
        },

        getMinDate() {
            const date = dayjs(this.minDate)
            return date.isValid() ? date : null
        },

        getSelectedDate() {
            if (!this.state) {
                return null
            }

            const date = dayjs(this.state)
            return date.isValid() ? date : null
        },

        getSlotsForDate(date) {
            const key = this.getDateKey(date)
            const configuredSlots = this.availableSlots?.[key]
            const hours = this.getWorkingHoursForDate(date)
            let slots = Array.isArray(configuredSlots)
                ? configuredSlots
                : this.buildSlots(hours)

            const blocked = this.blockedSlots?.[key] ?? []

            const filteredSlots = slots
                .map((slot) => String(slot).slice(0, 5))
                .filter((slot) => this.showBlockedSlots || !blocked.includes(slot))
                .filter((slot) => !this.slotIsTooSoon(key, slot))

            if (!this.showBlockedSlots) {
                return filteredSlots
            }

            return filteredSlots.map((slot) => ({
                blocked: blocked.includes(slot),
                time: slot,
            }))
        },

        slotIsTooSoon(dateKey, slot) {
            if (!this.minimumLeadTime) {
                return false
            }

            const slotDate = dayjs.tz(`${dateKey} ${slot}`, 'YYYY-MM-DD HH:mm', timezone)
            const earliestDate = dayjs().tz(timezone).add(this.minimumLeadTime, 'minute')

            return slotDate.isBefore(earliestDate)
        },

        getWorkingHoursForDate(date) {
            const weekday = date.locale('en').format('dddd').toLowerCase()
            const value = this.workingHours?.[weekday] ?? this.workingHours?.[date.day()]

            if (Array.isArray(value) && value.length >= 2) {
                return { end: value[1], start: value[0] }
            }

            if (value && typeof value === 'object') {
                return { end: value.end, start: value.start }
            }

            return null
        },

        buildSlots(hours) {
            if (!hours?.start || !hours?.end) {
                return []
            }

            let cursor = dayjs(`2000-01-01 ${hours.start}`)
            const end = dayjs(`2000-01-01 ${hours.end}`)
            const slots = []

            while (cursor.isBefore(end)) {
                slots.push(cursor.format('HH:mm'))
                cursor = cursor.add(this.slotInterval, 'minute')
            }

            return slots
        },

        renderPanel() {
            this.focusedDate = this.getSelectedDate() ?? this.getMinDate() ?? dayjs().tz(timezone)
            this.setupDaysGrid()
            this.refreshSlots()
        },

        refreshSlots() {
            if (!this.focusedDate) {
                return
            }

            this.slots = this.getSlotsForDate(this.focusedDate)
        },

        formatSlot(slot) {
            if (this.timeFormat === '24h') {
                return slot
            }

            return dayjs(`2000-01-01 ${slot}`, 'YYYY-MM-DD HH:mm')
                .locale('en')
                .format('hh:mm A')
        },

        selectDate(day) {
            const date = this.focusedDate.date(day)

            if (this.dateIsDisabled(date)) {
                return
            }

            this.selectedDateKey = this.getDateKey(date)
            this.selectedSlot = this.getSelectedDate()?.isSame(date, 'day')
                ? this.getSelectedDate().format('HH:mm')
                : null
            this.focusedDate = date
            this.refreshSlots()
        },

        selectSlot(slot) {
            if (!this.selectedDateKey) {
                this.selectedDateKey = this.getDateKey(this.focusedDate)
            }

            this.selectedSlot = slot
            this.state = `${this.selectedDateKey} ${slot}`
        },

        setMonths() {
            this.months = dayjs.months()
        },

        setDayLabels() {
            this.dayLabels = this.getDayLabels()
        },

        setupDaysGrid() {
            const firstDay = this.focusedDate.startOf('month').day()
            const emptyDays = (firstDay - weekStart + 7) % 7

            this.emptyDaysInFocusedMonth = Array.from({ length: emptyDays }, (_, i) => i)
            this.daysInFocusedMonth = Array.from(
                { length: this.focusedDate.daysInMonth() },
                (_, i) => i + 1,
            )
        },

        syncState() {
            const date = this.getSelectedDate()
            if (!date) {
                this.selectedDateKey = null
                this.selectedSlot = null
                return
            }

            this.selectedDateKey = this.getDateKey(date)
            this.selectedSlot = date.format('HH:mm')
        },
    }
}

const locales = {
    ar: require('dayjs/locale/ar'),
    en: require('dayjs/locale/en'),
}
