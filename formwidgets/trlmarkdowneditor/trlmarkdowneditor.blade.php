<div
    id="{{ $this->getId('trl-control') }}"
    data-control="trlmarkdowneditor"
    data-textarea-element="#{{ $this->getId('textarea') }}"
    data-locale-default="{{ $defaultLocale->code }}"
    data-placeholder-field="#{{ $this->getId('textarea') }}"
    class="field-translatable field-translatable-markdowneditor dropdown size-{{ $size }}"
>

    {!! $markdowneditor !!}

    <button
        class="btn btn-default trl-btn"
        data-toggle="dropdown"
        data-locale-active
        type="button">
    </button>

    {!! $this->makeTRLPartial('trlbase/locale_values') !!}

    {!! $this->makeTRLPartial('trlbase/locale_selector') !!}
</div>
