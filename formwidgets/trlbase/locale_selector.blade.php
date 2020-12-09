<div
    class="dropdown-menu dropdown-menu-right trl-dropdown-menu"
    data-locale-dropdown
    data-dropdown-title="@lang('igniter.translate::lang.text_select_label')"
>
    @foreach ($locales as $code => $name)
        <a
            class="dropdown-item"
            role="button"
            data-locale-switch="{{ $code }}"
        >{{ $name }}</a>
    @endforeach
</div>
