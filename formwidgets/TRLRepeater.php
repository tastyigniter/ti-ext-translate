<?php namespace Igniter\Translate\FormWidgets;

use Admin\FormWidgets\Repeater;
use ApplicationException;
use October\Rain\Html\Helper as HtmlHelper;
use Request;
use System\Models\Languages_model;

class TRLRepeater extends Repeater
{
    use \Igniter\Translate\FormWidgets\TRLBase;

    protected $defaultAlias = 'trlrepeater';

    public function __construct($controller, $formField, $configuration = [])
    {
        parent::__construct($controller, $formField, $configuration);

        $this->partialPath[] = '~/app/admin/formwidgets';
    }

    public function initialize()
    {
        parent::initialize();

        $this->parentViewPath = '~/app/admin/formwidgets/repeater';
        $this->parentAssetPath = '~/app/admin/formwidgets/repeater/assets';

        $this->initLocale();
    }

    public function render()
    {
        $this->isSupported = Languages_model::supportsLocale();

        $parentContent = $this->runAsParent(function () {
            return parent::render();
        });

        if (!$this->isSupported) {
            return $parentContent;
        }

        $this->vars['repeater'] = $parentContent;

        return $this->makePartial('trlrepeater/trlrepeater');
    }

    public function prepareVars()
    {
        parent::prepareVars();
        $this->prepareLocaleVars();
    }

    protected function processsItemDefinitions()
    {
        parent::processItemDefinitions();

        foreach ($this->itemDefinitions['fields'] as &$field) {
            $translatableFields = [
                'text',
                'textarea',
            ];

            if (in_array($field['type'], $translatableFields))
                $field['type'] = 'trl'.$field['type'];
        }
    }

    /**
     * Returns an array of translated values for this field
     * @return array
     */
    public function getSaveValue($value)
    {
        $this->rewritePostValues();

        return $this->getLocaleSaveValue(is_array($value) ? array_values($value) : $value);
    }

    public function loadAssets()
    {
        $this->runAsParent(function () {
            parent::loadAssets();
        });

        if (Languages_model::supportsLocale()) {
            $this->loadLocaleAssets();
            $this->addJs('js/trlrepeater.js');
        }
    }

    public function onAddItem()
    {
        $this->actAsParent();

        return parent::onAddItem();
    }

    public function onSwitchItemLocale()
    {
        if (!$locale = post('_repeater_locale')) {
            throw new ApplicationException('Unable to find a repeater locale for: '.$locale);
        }

        // Store previous value
        $previousLocale = post('_repeater_previous_locale');
        $previousValue = $this->getPrimarySaveDataAsArray();

        // Update widget to show form for switched locale
        $lockerData = $this->getLocaleSaveDataAsArray($locale) ?: [];
        $this->reprocessLocaleItems($lockerData);

        foreach ($this->formWidgets as $key => $widget) {
            $value = array_shift($lockerData);
            if (!$value) {
                unset($this->formWidgets[$key]);
            }
            else {
                $widget->setFormValues($value);
            }
        }

        $this->actAsParent();
        $parentContent = parent::render();
        $this->actAsParent(FALSE);

        return [
            '#'.$this->getId('mlRepeater') => $parentContent,
            'updateValue' => json_encode($previousValue),
            'updateLocale' => $previousLocale,
        ];
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
        $data = post('RLTranslateRepeaterLocale');
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
