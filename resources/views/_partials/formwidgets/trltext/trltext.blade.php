@if ($this->previewMode)
    <span class="form-control">{{ $field->value ? $field->value : '&nbsp;' }}</span>
@else
    <div
        id="{{ $this->getId('trl-control') }}"
        class="field-translatable field-translatable-text dropdown"
        data-control="{{ $field->getConfig('controlType', 'translatable') }}"
        data-locale-active="{{ $activeLocale->code }}"
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

        @if ($field->getConfig('controlType', 'translatable') == 'translatable')
            <button
                class="btn btn-default trl-btn"
                data-bs-toggle="dropdown"
                data-locale-toggle
                type="button"
            ></button>

            {!! $this->makeTRLPartial('trlbase/locale_selector') !!}
        @endif

        {!! $this->makeTRLPartial('trlbase/locale_values') !!}
    </div>
@endif
