+function ($) {
    "use strict";

    // TRLREPEATER CLASS DEFINITION
    // ============================

    var TRLRepeater = function (element, options) {
        this.options = options
        this.$el = $(element)
        this.$dropdown = $('[data-locale-dropdown]', this.$el)
        this.$activeButton = $('[data-locale-toggle]', this.$el)
        this.activeLocale = options.localeActive

        this.init()
    }

    TRLRepeater.DEFAULTS = {
        localeActive: 'en',
        translatableSelector: '[data-control=translatable-repeater]'
    }

    TRLRepeater.prototype.init = function () {
        this.$el.translatable()

        this.bindLocaleInput()

        this.$el.on('setLocale.ti.translatable', this.onSetLocale.bind(this))

        $(window).on('repeaterItemAdded', this.bindLocaleInput.bind(this))
    }

    TRLRepeater.prototype.dispose = function () {
        this.$el.off('setLocale.ti.translatable', this.onSetLocale.bind(this))

        this.$el.removeData('ti.trlRepeater')

        this.$dropdown = null
        this.$activeLocale = null
        this.activeLocale = null
        this.$el = null

        this.options = null
    }

    TRLRepeater.prototype.onSetLocale = function (event, locale, localeValue) {
        this.activeLocale = locale

        $(this.options.translatableSelector, this.$el).each(function () {
            var $el = $(this),
                $placeholder = $($el.data('placeholderField')),
                $activeField = $el.find('[data-locale-value="' + locale + '"]')

            $placeholder.val($activeField.val());
        })
    }

    TRLRepeater.prototype.bindLocaleInput = function () {
        var self = this

        $(this.options.translatableSelector, this.$el).each(function () {
            var $el = $(this),
                $placeholder = $($el.data('placeholderField'))

            if (!$el.hasClass('trl-loaded')) {
                $el.addClass('trl-loaded')

                $placeholder.on('input', function () {
                    $el.find('[data-locale-value="' + self.activeLocale + '"]').val(this.value)
                })
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

        $('[data-control="translatable-repeater"]').each(function () {
            var $el = $(this)

            $el.find('[data-locale-value]').each(function () {
                var $input = $(this),
                    $parent = $input.closest('[data-item-index]'),
                    inputName = $input.attr('name'),
                    postName = inputName.substr(0, inputName.indexOf('[')),
                    postNameLength = postName.length,
                    fieldName = $($el.data('placeholderField')).data('translatableRepeater')

                if ($input.data('ti.trlRepeaterValue')) return;
                $input.data('ti.trlRepeaterValue', true)
                $input.attr('name', inputName.slice(0, postNameLength)+'['+fieldName+']['+$parent.data('itemIndex')+']'+inputName.slice(postNameLength))
            })
        })
    })

}(window.jQuery);
