<?php namespace Igniter\Translate\FormWidgets;

use Admin\FormWidgets\Repeater;
use Igniter\Flame\Html\Helper as HtmlHelper;
use Request;

class TRLRepeater extends Repeater
{
    use \Igniter\Translate\FormWidgets\TRLBase;

    protected $defaultAlias = 'trlrepeater';

    public function __construct($controller, $formField, $configuration = [])
    {
        $this->parentPartialPath[] = '~/app/admin/formwidgets';
        $this->parentPartialPath[] = '~/app/admin/formwidgets/repeater';
        $this->parentAssetPath[] = '~/app/admin/formwidgets/repeater/assets';

        parent::__construct($controller, $formField, $configuration);
//        $this->partialPath[] = '~/app/admin/formwidgets';
    }

    public function initialize()
    {
        parent::initialize();

        $this->initLocale();
    }

    public function render()
    {
        $parentContent = $this->maskAsParent(function () {
            return parent::render();
        });

        if (!$this->isSupported)
            return $parentContent;

        $this->vars['repeater'] = $parentContent;

        return $this->makePartial('trlrepeater/trlrepeater');
    }

    public function prepareVars()
    {
        parent::prepareVars();
        $this->prepareLocaleVars();
    }

    public function loadAssets()
    {
        $this->maskAsParent(function () {
            parent::loadAssets();
        });

        if ($this->isSupported) {
            $this->loadLocaleAssets();
            $this->addJs('js/trlrepeater.js');
        }
    }

    protected function processItemDefinitions()
    {
        parent::processItemDefinitions();

        foreach ($this->itemDefinitions['fields'] as &$field) {
            $translatableFields = [
                'text',
                'textarea',
            ];

            if (in_array($field['type'], $translatableFields)) {
                $field['type'] = 'trl'.$field['type'];
                $field['hideLocaleSelector'] = TRUE;
            }
        }
    }

    protected function processSaveValue($value)
    {
        $value = parent::processSaveValue($value);

        if (!$this->isSupported)
            return $value;

        return $this->getLocaleSaveValue($value);
    }

    /**
     * Ensure that the current locale data is processed by the repeater instead of the original non-translated data
     * @return void
     */
    protected function reprocessLocaleItems($data)
    {
        $this->formWidgets = [];
        $this->formField->value = $data;

        $key = implode('.', HtmlHelper::nameToArray($this->formField->getName()));
        $requestData = Request::all();
        array_set($requestData, $key, $data);
        Request::merge($requestData);

        $this->processItems();
    }

    /**
     * Gets the active values from the selected locale.
     * @return array
     */
    protected function getPrimarySaveDataAsArray()
    {
        $data = post($this->formField->getName()) ?: [];

        return $this->processSaveValue($data);
    }

    /**
     * Returns the stored locale data as an array.
     * @return array
     */
    protected function getLocaleSaveDataAsArray($locale)
    {
        $saveData = array_get($this->getLocaleSaveData(), $locale, []);

        if (!is_array($saveData)) {
            $saveData = json_decode($saveData, TRUE);
        }

        return $saveData;
    }

    /**
     * Since the locker does always contain the latest values, this method
     * will take the save data from the repeater and merge it in to the
     * locker based on which ever locale is selected using an item map
     * @return void
     */
    protected function rewritePostValues()
    {
        /*
         * Get the selected locale at postback
         */
        $data = post('TRLRepeaterLocale');
        $fieldName = implode('.', HtmlHelper::nameToArray($this->fieldName));
        $locale = array_get($data, $fieldName);

        if (!$locale) {
            return;
        }

        /*
         * Splice the save data in to the locker data for selected locale
         */
        $data = $this->getPrimarySaveDataAsArray();
        $fieldName = 'RLTranslate.'.$locale.'.'.implode('.', HtmlHelper::nameToArray($this->fieldName));

        $requestData = Request::all();
        array_set($requestData, $fieldName, json_encode($data));
        Request::merge($requestData);
    }
}
