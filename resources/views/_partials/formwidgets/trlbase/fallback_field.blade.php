<div
    class="trltext-widget"
    id="{{ $this->getId() }}">
    {!! $this->makePartial('widgets.form.field_'.$field->type, [
        'field' => $field,
    ]) !!}
</div>
