{!! form_open([
    'class' =>'locale-picker-form'
]) !!}
<select
    name="locale"
    data-request="{{ $__SELF__.'::onSwitchLocale'}}"
    class="form-select locale-picker"
    autocomplete="off"
>
    @foreach($__SELF__->locales as $code => $name)
        <option
            value="{{ $code }}"
            {{ $code == $__SELF__->activeLocale ? 'selected' : '' }}
        >{{ $name }}</option>
    @endforeach
</select>
{!! form_close() !!}
