<div
    id="<?= $this->getId('mlControl') ?>"
    class="field-translatable field-translatable-repeater dropdown"
    data-control="trlrepeater"
    data-locale-default="<?= e($defaultLocale->code) ?>"
    data-switch-handler="<?= $this->getEventHandler('onSelectItemLocale') ?>"
>

    <div id="<?= $this->getId('mlRepeater') ?>">
        <?= $repeater ?>
    </div>

    <button
        class="btn btn-default trl-btn"
        data-toggle="dropdown"
        data-locale-active
        type="button">
    </button>

    <input
        type="hidden"
        data-repeater-locale-active
        name="<?= $field->getName('RLTranslateRepeaterLocale') ?>"
        value="<?= e($defaultLocale->code) ?>"
    />

    <?= $this->makeTRLPartial('trlbase/locale_values') ?>

    <?= $this->makeTRLPartial('trlbase/locale_selector') ?>
</div>
