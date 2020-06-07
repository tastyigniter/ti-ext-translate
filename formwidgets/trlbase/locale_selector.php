<div
    class="dropdown-menu dropdown-menu-right trl-dropdown-menu"
    data-locale-dropdown
    data-dropdown-title="<?= e(lang('igniter.translate::lang.text_select_label')) ?>">
    <?php foreach ($locales as $code => $name) { ?>
        <a
            class="dropdown-item"
            role="button"
            data-locale-select="<?= $code ?>"
        ><?= $name ?></a>
    <?php } ?>
</div>
