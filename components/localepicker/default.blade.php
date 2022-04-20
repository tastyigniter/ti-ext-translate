{!! form_open([]) !!}
<select
    name="locale"
    data-request="{{ $__SELF__.'::onSwitchLocale'}}"
    class="form-select"
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
