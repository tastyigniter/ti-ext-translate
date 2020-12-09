@if ($this->previewMode)
    <span class="form-control">{{ $field->value ? $field->value : '&nbsp;' }}</span>
@else
    <div
        id="{{ $this->getId('trl-control') }}"
        class="field-translatable field-translatable-text dropdown"
        data-control="translatable"
        data-locale-default="{{ $defaultLocale->code }}"
        data-placeholder-field="#{{ $field->getId('placeholderField') }}"
    >
        <input
            type="text"
            id="{{ $field->getId('placeholderField') }}"
            class="form-control"
            name="{{ $field->getName() }}"
            value="{{ $field->value }}"
            placeholder="@lang($field->placeholder)"
            autocomplete="off"
            {!! $field->hasAttribute('maxlength') ? '' : 'maxlength="255"' !!}
            {!! $field->getAttributes() !!}
        />

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
