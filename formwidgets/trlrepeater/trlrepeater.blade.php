<div
    id="{{ $this->getId('trl-control') }}"
    class="field-translatable field-translatable-repeater"
    data-control="trlrepeater"
    data-locale-default="{{ $defaultLocale->code }}"
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

    <div id="{{ $this->getId('trl-repeater') }}">
        {!! $repeater !!}
    </div>
</div>
