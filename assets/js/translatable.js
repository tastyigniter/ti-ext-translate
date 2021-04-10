+function ($) {
    "use strict";

    // TRANSLATABLE CLASS DEFINITION
    // ============================

    var Translatable = function (element, options) {
        this.options = options
        this.$el = $(element)
        this.$dropdown = $('[data-locale-dropdown]', this.$el)
        this.$activeButton = $('[data-locale-toggle]', this.$el)
        this.$placeholder = $(this.options.placeholderField)

        this.activeLocale = this.options.localeActive
        this.$activeField = this.getLocaleElement(this.activeLocale)

        this.$activeButton.text(this.activeLocale)

        this.toggleActiveLocale(this.activeLocale)

        this.$dropdown.on('click', '[data-locale-switch]', this.$activeButton, this.onSwitchLocale.bind(this));

        this.bindLocaleInput()
    }

    Translatable.DEFAULTS = {
        localeActive: 'en',
        placeholderField: null
    }

    Translatable.prototype.onSwitchLocale = function (event) {
        var currentLocale = event.data.text(),
            selectedLocale = $(event.currentTarget).data('locale-switch')

        this.toggleActiveLocale(selectedLocale)

        if (selectedLocale !== currentLocale) {
            this.setLocale(selectedLocale)
        }

        if (event.ctrlKey || event.metaKey) {
            event.preventDefault()
            this.$el.closest('form').find('[data-locale-switch="' + selectedLocale + '"]').trigger('click')
        }
    }

    Translatable.prototype.bindLocaleInput = function () {
        var self = this

        this.$placeholder.on('input', function () {
            self.$activeField.val(this.value)
        })
    }

    Translatable.prototype.toggleActiveLocale = function (locale) {
        this.$dropdown.find('[data-locale-switch]').each(function () {
            $(this).removeClass('active')

            if ($(this).data('localeSwitch') === locale)
                $(this).addClass('active')
        })
    }

    Translatable.prototype.getLocaleElement = function (locale) {
        var el = this.$el.find('[data-locale-value="' + locale + '"]')
        return el.length ? el : null
    }

    Translatable.prototype.getLocaleValue = function (locale) {
        var value = this.getLocaleElement(locale)
        return value ? value.val() : null
    }

    Translatable.prototype.setLocaleValue = function (value, locale) {
        if (locale) {
            var el = this.getLocaleElement(locale)
            el ? el.val(value) : null
        } else {
            this.$activeField.val(value)
        }
    }

    Translatable.prototype.setLocale = function (locale) {
        this.activeLocale = locale
        this.$activeField = this.getLocaleElement(locale)

        this.$activeButton.text(locale)
        this.$placeholder.val(this.getLocaleValue(locale))

        this.$el.trigger('setLocale.ti.translatable', [locale, this.getLocaleValue(locale)])
    }

    // TRANSLATABLE PLUGIN DEFINITION
    // ============================

    var old = $.fn.translatable

    $.fn.translatable = function (option) {
        var args = Array.prototype.slice.call(arguments, 1), result
        this.each(function () {
            var $this = $(this)
            var data = $this.data('ti.translatable')
            var options = $.extend({}, Translatable.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('ti.translatable', (data = new Translatable(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.translatable.Constructor = Translatable

    // TRANSLATABLE NO CONFLICT
    // =================

    $.fn.translatable.noConflict = function () {
        $.fn.translatable = old
        return this
    }

    // TRANSLATABLE DATA-API
    // ===============
    $(document).render(function () {
        $('[data-control="translatable"]').translatable();
    })

}(window.jQuery);
