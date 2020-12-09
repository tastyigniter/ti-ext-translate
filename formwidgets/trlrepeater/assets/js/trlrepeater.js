+function ($) {
    "use strict";

    // TRLREPEATER CLASS DEFINITION
    // ============================

    var TRLRepeater = function (element, options) {
        this.options = options
        this.$el = $(element)
        this.$dropdown = $('[data-locale-dropdown]', this.$el)
        this.$activeButton = $('[data-repeater-locale-active]', this.$el)
        this.activeLocale = options.localeDefault

        this.$activeButton.text(this.activeLocale)

        // this.$dropdown.on('click', '[data-locale-switch]', this.$activeButton, this.onSwitchLocale.bind(this));

        // this.init()
    }

    TRLRepeater.DEFAULTS = {
        localeDefault: 'en',
        switchHandler: null
    }

    TRLRepeater.prototype.init = function () {
        // this.$el.translatable()

        // this.checkEmptyItems()
        //
        // $(document).on('render', this.checkEmptyItems.bind(this))

        // this.$el.on('setLocale.ti.translatable', this.onSetLocale.bind(this))

        // this.$el.one('dispose-control', this.dispose.bind(this))
    }

    TRLRepeater.prototype.dispose = function () {

        // $(document).off('render', this.checkEmptyItems.bind(this))
        //
        // this.$el.off('setLocale.ti.translatable', this.onSetLocale.bind(this))
        //
        // this.$el.off('dispose-control', this.dispose.bind(this))

        this.$el.removeData('ti.trlRepeater')

        this.$dropdown = null
        this.$activeLocale = null
        this.activeLocale = null
        this.$el = null

        this.options = null
    }

    TRLRepeater.prototype.checkEmptyItems = function () {
        var isEmpty = !$('ul.field-repeater-items > li', this.$el).length
        this.$el.toggleClass('is-empty', isEmpty)
    }

    TRLRepeater.prototype.onSwitchLocale = function (event) {
        // this.$el.translatable('onSwitchLocale', event)
        // var self = this,
        //     previousLocale = this.activeLocale
        //
        // this.$el
        //     .addClass('loading-indicator-container size-form-field')
        //     .progressIndicator()
        //
        // this.activeLocale = locale
        // this.$activeLocale.val(locale)
        //
        // this.$el.request(this.options.switchHandler, {
        //     data: {
        //         _repeater_previous_locale: previousLocale,
        //         _repeater_locale: locale
        //     },
        //     success: function (data) {
        //         self.$el.translatable('setLocaleValue', data.updateValue, data.updateLocale)
        //         self.$el.progressIndicator('hide')
        //         this.success(data)
        //     }
        // }).always(function () {
        //     self.$el.progressIndicator('hide')
        // })
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
