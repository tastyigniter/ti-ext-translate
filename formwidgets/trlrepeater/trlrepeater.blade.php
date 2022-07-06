<div
    id="{{ $this->getId('trl-control') }}"
    class="field-translatable field-translatable-repeater"
    data-control="trlrepeater"
    data-locale-active="{{ $activeLocale->code }}"
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

    <div id="{{ $this->getId('trl-repeater') }}">
        {!! $repeater !!}
    </div>
</div>
