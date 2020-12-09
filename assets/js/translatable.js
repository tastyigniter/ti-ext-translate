+function ($) {
    "use strict";

    // TRANSLATABLE CLASS DEFINITION
    // ============================

    var Translatable = function (element, options) {
        this.options = options
        this.$el = $(element)
        this.$dropdown = $('[data-locale-dropdown]', this.$el)
        this.$placeholder = $(this.options.placeholderField)
        this.$activeButton = $('[data-locale-active]', this.$el)

        this.activeLocale = this.options.localeDefault
        this.$activeField = this.getLocaleElement(this.activeLocale)

        this.$activeButton.text(this.activeLocale)

        this.$dropdown.on('click', '[data-locale-switch]', this.$activeButton, this.onSwitchLocale.bind(this));

        this.bindLocaleInput()
    }

    Translatable.DEFAULTS = {
        localeDefault: 'en',
        placeholderField: null
    }

    Translatable.prototype.onSwitchLocale = function (event) {
        var currentLocale = event.data.text(),
            selectedLocale = $(event.currentTarget).data('locale-switch')

        if (selectedLocale !== currentLocale) {
            this.setLocale(selectedLocale)

            event.preventDefault()
            $('[data-locale-switch="' + selectedLocale + '"]').trigger('click')
        }
    }

    Translatable.prototype.bindLocaleInput = function () {
        var self = this

        this.$placeholder.on('input', function () {
            self.$activeField.val(this.value)
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
            this.getLocaleElement(locale).val(value)
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