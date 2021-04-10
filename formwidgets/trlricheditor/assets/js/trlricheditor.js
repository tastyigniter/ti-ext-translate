+function ($) {
    "use strict";

    // TRLRICHEDITOR CLASS DEFINITION
    // ============================

    var TRLRichEditor = function (element, options) {
        this.options = options
        this.$el = $(element)
        this.$richEditor = $('[data-control=rich-editor]:first', this.$el)
        this.richEditor = this.$richEditor.data('ti.richEditor');

        this.init()
    }

    TRLRichEditor.DEFAULTS = {
        localeActive: 'en',
        placeholderField: null
    }

    TRLRichEditor.prototype.init = function () {
        this.$el.translatable()

        this.$el.on('setLocale.ti.translatable', this.onSetLocale.bind(this))

        this.richEditor.$textarea.on('summernote.change', this.onChangeContent.bind(this))
    }

    TRLRichEditor.prototype.dispose = function () {
        this.$el.off('setLocale.ti.translatable', this.onSetLocale.bind(this))
        this.richEditor.$textarea.off('summernote.change', this.onChangeContent.bind(this))

        this.$el.removeData('ti.trlRichEditor')

        this.richEditor = null
        this.$richEditor = null
        this.$el = null
        this.options = null
    }

    TRLRichEditor.prototype.onSetLocale = function (e, locale, localeValue) {
        if (typeof localeValue === 'string' && this.$richEditor.data('ti.richEditor')) {
            this.richEditor.$textarea.summernote('code', localeValue);
        }
    }

    TRLRichEditor.prototype.onChangeContent = function (ev, value) {
        this.$el.translatable('setLocaleValue', this.richEditor.$textarea.summernote('code'))
    }

    var old = $.fn.trlRichEditor

    $.fn.trlRichEditor = function (option) {
        var args = Array.prototype.slice.call(arguments, 1), result

        this.each(function () {
            var $this = $(this)
            var data = $this.data('ti.trlRichEditor')
            var options = $.extend({}, TRLRichEditor.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('ti.trlRichEditor', (data = new TRLRichEditor(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.trlRichEditor.Constructor = TRLRichEditor;

    $.fn.trlRichEditor.noConflict = function () {
        $.fn.trlRichEditor = old
        return this
    }

    $(document).render(function () {
        $('[data-control="trlricheditor"]').trlRichEditor()
    })
}(window.jQuery);
