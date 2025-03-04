@if ($this->previewMode)
    <div class="form-control">{{ nl2br($field->value) }}</div>
@else
    <div
        id="{{ $this->getId('trl-control') }}"
        class="field-translatable field-translatable-textarea dropdown"
        data-control="{{ $field->getConfig('controlType', 'translatable') }}"
        data-locale-active="{{ $activeLocale->code }}"
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

        @if ($field->getConfig('controlType', 'translatable') == 'translatable')
            <button
                class="btn btn-default trl-btn{{ $field->getConfig('hideLocaleSelector', false) ? ' d-none' : '' }}"
                data-bs-toggle="dropdown"
                data-locale-toggle
                type="button"
            ></button>

            {!! $this->makeTRLPartial('trlbase/locale_selector') !!}
        @endif

        {!! $this->makeTRLPartial('trlbase/locale_values') !!}
    </div>
@endif
