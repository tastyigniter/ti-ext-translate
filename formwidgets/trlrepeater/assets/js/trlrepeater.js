+function ($) {
    "use strict";

    // TRLREPEATER CLASS DEFINITION
    // ============================

    var TRLRepeater = function (element, options) {
        this.options = options
        this.$el = $(element)
        this.$selector = $('[data-locale-dropdown]', this.$el)
        this.$activeLocale = $('[data-repeater-locale-active]', this.$el)
        this.activeLocale = options.localeDefault

        this.init()
    }

    TRLRepeater.DEFAULTS = {
        localeDefault: 'en',
        switchHandler: null
    }

    TRLRepeater.prototype.init = function () {
        this.$el.translatable()

        // this.checkEmptyItems()
        //
        // $(document).on('render', this.proxy(this.checkEmptyItems))

        this.$el.on('setLocale.ti.translatable', this.proxy(this.onSetLocale))

        // this.$el.one('dispose-control', this.proxy(this.dispose))
    }

    TRLRepeater.prototype.dispose = function () {

        $(document).off('render', this.proxy(this.checkEmptyItems))

        this.$el.off('setLocale.oc.multilingual', this.proxy(this.onSetLocale))

        this.$el.off('dispose-control', this.proxy(this.dispose))

        this.$el.removeData('oc.trlRepeater')

        this.$selector = null
        this.$locale = null
        this.locale = null
        this.$el = null

        this.options = null

        BaseProto.dispose.call(this)
    }

    TRLRepeater.prototype.checkEmptyItems = function () {
        var isEmpty = !$('ul.field-repeater-items > li', this.$el).length
        this.$el.toggleClass('is-empty', isEmpty)
    }

    TRLRepeater.prototype.onSetLocale = function (e, locale, localeValue) {
        var self = this,
            previousLocale = this.locale

        this.$el
        .addClass('loading-indicator-container size-form-field')
        .loadIndicator()

        this.locale = locale
        this.$locale.val(locale)

        this.$el.request(this.options.switchHandler, {
            data: {
                _repeater_previous_locale: previousLocale,
                _repeater_locale: locale
            },
            success: function (data) {
                self.$el.multiLingual('setLocaleValue', data.updateValue, data.updateLocale)
                self.$el.loadIndicator('hide')
                this.success(data)
            }
        })
    }

    // TRLREPEATER PLUGIN DEFINITION
    // ============================

    var old = $.fn.trlRepeater

    $.fn.trlRepeater = function (option) {
        var args = Array.prototype.slice.call(arguments, 1), result
        this.each(function () {
            var $this = $(this)
            var data = $this.data('ti.trlRepeater')
            var options = $.extend({}, TRLRepeater.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('ti.trlRepeater', (data = new TRLRepeater(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.trlRepeater.Constructor = TRLRepeater

    // TRLREPEATER NO CONFLICT
    // =================

    $.fn.trlRepeater.noConflict = function () {
        $.fn.trlRepeater = old
        return this
    }

    // TRLREPEATER DATA-API
    // ===============

    $(document).render(function () {
        $('[data-control="trlrepeater"]').trlRepeater()
    })

}(window.jQuery);
