+function ($) {
    "use strict";

    // TRLMARKDOWNEDITOR CLASS DEFINITION
    // ============================

    var TRLMarkdownEditor = function (element, options) {
        this.options = options
        this.$el = $(element)
        this.$markdownEditor = $('[data-control=markdowneditor]:first', this.$el)
        this.markdownEditor = this.$markdownEditor.data('ti.markdownEditor');

        this.init()
    }

    TRLMarkdownEditor.DEFAULTS = {
        localeActive: 'en',
        placeholderField: null
    }

    TRLMarkdownEditor.prototype.init = function () {
        this.$el.translatable()

        this.$el.on('setLocale.ti.translatable', this.onSetLocale.bind(this))

        this.markdownEditor.editor.codemirror.on('change', this.onChangeContent.bind(this))
    }

    TRLMarkdownEditor.prototype.dispose = function () {
        this.$el.off('setLocale.ti.translatable', this.onSetLocale.bind(this))
        this.markdownEditor.editor.codemirror.off('change', this.onChangeContent.bind(this))

        this.$el.removeData('ti.trlMarkdownEditor')

        this.markdownEditor = null
        this.$markdownEditor = null
        this.$el = null
        this.options = null
    }

    TRLMarkdownEditor.prototype.onSetLocale = function (e, locale, localeValue) {
        if (typeof localeValue === 'string' && this.$markdownEditor.data('ti.markdownEditor')) {
            this.markdownEditor.editor.codemirror.setValue(localeValue);
        }
    }

    TRLMarkdownEditor.prototype.onChangeContent = function (codeMirror, changeObj) {
        this.$el.translatable('setLocaleValue', codeMirror.getValue())
    }

    var old = $.fn.trlMarkdownEditor

    $.fn.trlMarkdownEditor = function (option) {
        var args = Array.prototype.slice.call(arguments, 1), result

        this.each(function () {
            var $this = $(this)
            var data = $this.data('ti.trlMarkdownEditor')
            var options = $.extend({}, TRLMarkdownEditor.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('ti.trlMarkdownEditor', (data = new TRLMarkdownEditor(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.trlMarkdownEditor.Constructor = TRLMarkdownEditor;

    $.fn.trlMarkdownEditor.noConflict = function () {
        $.fn.trlMarkdownEditor = old
        return this
    }

    $(document).render(function () {
        $('[data-control="trlmarkdowneditor"]').trlMarkdownEditor()
    })
}(window.jQuery);
