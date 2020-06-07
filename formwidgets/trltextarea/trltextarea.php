<?php if ($this->previewMode) { ?>
    <div class="form-control"><?= nl2br(e($field->value)); ?></div>
<?php } else { ?>
    <div
        id="<?= $this->getId() ?>"
        class="field-translatable field-translatable-textarea dropdown"
        data-control="translatable"
        data-locale-default="<?= $defaultLocale->code; ?>"
        data-placeholder-field="#<?= $field->getId(); ?>"
    >
        <textarea
            id="<?= $field->getId(); ?>"
            class="form-control field-textarea"
            name="<?= $field->getName(); ?>"
            placeholder="<?= e(lang($field->placeholder)); ?>"
            autocomplete="off"
            <?= $field->getAttributes(); ?>
        ><?= e($field->value); ?></textarea>

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