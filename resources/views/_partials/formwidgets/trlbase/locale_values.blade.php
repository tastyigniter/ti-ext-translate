@foreach ($locales as $code => $name)
    <input
        type="hidden"
        name="{{ $field->getName('TRLTranslate').'['.$code.']' }}"
        value="{{ $this->getLocaleValue($code) }}"
        data-locale-value="{{ $code }}"
        {!! $field->getAttributes() !!}
    />
@endforeach
