<div
    id="{{ $this->getId('trl-control') }}"
    data-control="trlricheditor"
    data-textarea-element="#{{ $this->getId('textarea') }}"
    data-locale-active="{{ $activeLocale->code }}"
    data-placeholder-field="#{{ $this->getId('textarea') }}"
    class="field-translatable field-translatable-richeditor size-{{ $size }}"
>

    <div class="dropdown">
        <button
            class="btn btn-default trl-btn"
            data-bs-toggle="dropdown"
            data-locale-toggle
            type="button">
        </button>

        {!! $this->makeTRLPartial('trlbase/locale_selector') !!}
    </div>

    {!! $richeditor !!}

    {!! $this->makeTRLPartial('trlbase/locale_values') !!}
</div>
