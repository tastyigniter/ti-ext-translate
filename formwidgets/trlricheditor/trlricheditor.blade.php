<div
    id="{{ $this->getId('trl-control') }}"
    data-control="trlricheditor"
    data-textarea-element="#{{ $this->getId('textarea') }}"
    data-locale-default="{{ $defaultLocale->code }}"
    data-placeholder-field="#{{ $this->getId('textarea') }}"
    class="field-translatable field-translatable-richeditor size-{{ $size }}"
>

    <div class="dropdown">
        <button
            class="btn btn-default trl-btn"
            data-toggle="dropdown"
            data-locale-active
            type="button">
        </button>

        {!! $this->makeTRLPartial('trlbase/locale_selector') !!}
    </div>

    {!! $richeditor !!}

    {!! $this->makeTRLPartial('trlbase/locale_values') !!}
</div>
