@if ($this->previewMode)
    <div class="form-control">{{ nl2br($field->value) }}</div>
@else
    <div
        id="{{ $this->getId('trl-control') }}"
        class="field-translatable field-translatable-textarea dropdown"
        data-control="translatable"
        data-locale-default="{{ $defaultLocale->code }}"
        data-placeholder-field="#{{ $field->getId('placeholderField') }}"
    >
        <textarea
            id="{{ $field->getId('placeholderField') }}"
            class="form-control field-textarea"
            name="{{ $field->getName() }}"
            placeholder="@lang($field->placeholder)"
            autocomplete="off"
            {!! $field->getAttributes() !!}
        >{{ $field->value }}</textarea>

        <button
            class="btn btn-default trl-btn{{ $field->getConfig('hideLocaleSelector', FALSE) ? ' d-none' : '' }}"
            data-toggle="dropdown"
            data-locale-active
            type="button"
        ></button>

        {!! $this->makeTRLPartial('trlbase/locale_values') !!}

        {!! $this->makeTRLPartial('trlbase/locale_selector') !!}
    </div>
@endif
