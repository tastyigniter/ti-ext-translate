<div
    class="trltext-widget"
    id="{{ $this->getId() }}">
    {!! $this->makePartial('~/app/admin/widgets/form/field_'.$field->type, [
        'field' => $field,
    ]) !!}
</div>