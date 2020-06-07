<?php if ($this->previewMode) { ?>
    <span class="form-control"><?= $field->value ? e($field->value) : '&nbsp;'; ?></span>
<?php } else { ?>
    <div
        id="<?= $this->getId(); ?>"
        class="field-translatable field-translatable-text dropdown"
        data-control="translatable"
        data-locale-default="<?= $defaultLocale->code; ?>"
        data-placeholder-field="#<?= $field->getId(); ?>"
    >
        <input
            type="text"
            id="<?= $field->getId(); ?>"
            class="form-control"
            name="<?= $field->getName(); ?>"
            value="<?= e($field->value); ?>"
            placeholder="<?= e(lang($field->placeholder)); ?>"
            autocomplete="off"
            <?= $field->hasAttribute('maxlength') ? '' : 'maxlength="255"'; ?>
            <?= $field->getAttributes(); ?>
        />

        <button
            class="btn btn-default trl-btn"
            data-toggle="dropdown"
            data-locale-active
            type="button"
        ></button>

        <?= $this->makeTRLPartial('trlbase/locale_values'); ?>

        <?= $this->makeTRLPartial('trlbase/locale_selector'); ?>
    </div>
<?php } ?>
