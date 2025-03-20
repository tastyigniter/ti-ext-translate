---
title: "Translate"
section: "extensions"
sortOrder: 120
---

## Installation

You can install the extension via composer using the following command:

```bash
composer require tastyigniter/ti-ext-translate:"^4.0" -W
```

Run the database migrations to create the required tables:
  
```bash
php artisan igniter:up
```

## Usage

### Configuring default locale

The `setLocale` method on the `translator.localization` service is used to set the language of your TastyIgniter application. The `getLocale` method on the `translator.localization` service is used to get the current language. You will typically set the default locale within a middleware.

Here is an example of setting the default locale to `fr`:

```php
resolve('translator.localization')->setLocale('fr');
```

For more information on making your site multilingual, see the [Localization section of the TastyIgniter documentation](https://tastyigniter.com/docs/advanced/localization).

### Switching locales

Visitors as well as administrators can choose their preferred language, which will change the language of the frontend or admin pages depending on the selection.

#### Admin area

In the admin area, you can switch the language by selecting the desired language from the dropdown on the staff profile page. Navigate to `Manage > Staff members > Edit Staff` and select the desired language from the `Language` dropdown. The admin area will be displayed in the selected language.

The `Igniter\Flame\Translation\Middleware\Localization` middleware is used to set the language of the admin area. The middleware checks if the staff member has a language set in their profile and sets the language accordingly.

#### Frontend

To allow visitors to switch the language, you can [create a component](https://tastyigniter.com/docs/customize/components) to show a dropdown with the available languages. The component will change the language of the page depending on the selection. Here is an example creating a language picker [livewire component](https://tastyigniter.com/docs/customize/components#livewire-components):

```php
use Igniter\System\Models\Language;
use Livewire\Component;

class LocalePicker extends Component
{
    public function render()
    {
        return view('localepicker', [
            'locales' => Language::listSupported(),
            'activeLocale' => resolve('translator.localization')->getLocale(),
        ]);
    }

    public function onSwitchLocale($locale)
    {
        resolve('translator.localization')->setLocale($locale);

        return redirect()->back();
    }
}
```

The `listSupported` method on the `Language` model returns an array of supported languages, where the key is the language code and the value is the language name. The `getLocale` method on the `translator.localization` service is used to get the current language. The `setLocale` method on the `translator.localization` service is used to change the language of the page.

The component view file `localepicker.blade.php`:

```blade
<div>
    <select wire:change="onSwitchLocale($event.target.value)">
        @foreach ($locales as $code => $name)
            <option value="{{ $code }}" {{ $code == $activeLocale ? 'selected' : '' }}>
                {{ $name }}
            </option>
        @endforeach
    </select>
</div>
```

When a visitor selects a language from the dropdown, the `onSwitchLocale` method is called, which changes the language of the page and redirects back to the same page.

The `Igniter\Flame\Translation\Middleware\Localization` middleware is used to set the language of the frontend. The middleware checks does the following checks:

- If the visitor has a language set in the first part of the URL, it sets the language accordingly.
- If the visitor has a language set in their browser, it sets the language accordingly.
- If the visitor has a language set in their session, it sets the language accordingly.
- If the visitor does not have a language set in the above options, it sets the default language.

### Model translation

Models can have their attributes translated by using the `Igniter\Translate\Actions\TranslatableModel` action and specifying which attributes to translate using the `translatable` method. Here's how you can configure a model to have translatable attributes:

```php
use Igniter\Translate\Actions\TranslatableModel;
use Igniter\Flame\Database\Model;

class Category extends Model
{
    public array $implement = [TranslatableModel::class];

    public function translatable() {
        return ['name'];
    }
}
```

You can also translate models on other extensions by adding extending the model class within your extension class `boot` method. Here is an example of how to translate the `Category` model from the `Igniter.Cart` extension:

```php
use Igniter\Cart\Models\Category;
use Igniter\Translate\Actions\TranslatableModel;

public function boot()
{
    Category::extend(function ($model) {
        $model->implement[] = TranslatableModel::class;
        $model->addDynamicMethod('translatable', function () {
            return ['name', 'description'];
        });
    });
}
```

The `addDynamicMethod` method is used to add a new method to the model class.

### Setting translations

You can store translations for the active locale by setting the model attribute value using the model property. Here is an example of setting the `name` attribute of a `Category` model to a french translation, assuming the active locale is set to `fr`:

```php
$category = Category::find(1);
$category->name = 'Nom de la catégorie';
$category->save();
```

You can store translations for multiple locales when creating a model.

```php
$category = Category::create([
    'name' => [
        'en' => 'Category Name',
        'fr' => 'Nom de la catégorie',
    ],
]);
```

To store the translation for a specific locale, you can use the `setLocale` method on the `translator.localization` service.

```php
resolve('translator.localization')->setLocale('fr');

$category = Category::find(1);
$category->name = 'Nom de la catégorie';
$category->save();
```

You can store translations for multiple locales when saving a model.

```php
$category = Category::find(1);

$category->name = [
    'en' => 'Category Name',
    'fr' => 'Nom de la catégorie',
];

$category->save();
```

### Retrieving translations

You can retrieve translations for the active locale by accessing the model attribute value. Here is an example of retrieving the `name` attribute of a `Category` model, assuming the active locale is set to `fr`:

```php
$category = Category::find(1);
echo $category->name;
```

You can retrieve translations for a specific locale by using the `getAttributeTranslatedValue` method on the model instance.

```php
$category = Category::find(1);

echo $category->getAttributeTranslatedValue('name', 'fr');
```

### Form widgets

#### Translatable text

The `trltext` form widget is used to create a text input field that can store translations for multiple locales.

```php
'my_field' => [
    'label' => 'Name',
    'type' => 'trltext',
],
```

The options for the `trltext` form widget type are the same as the `text` [form field type](https://tastyigniter.com/docs/extend/forms#text).

#### Translatable textarea

The `trltextarea` form widget is used to create a textarea input field that can store translations for multiple locales.

```php
'my_field' => [
    'label' => 'Description',
    'type' => 'trltextarea',
],
```

The options for the `trltextarea` form widget type are the same as the `textarea` form field type](<https://tastyigniter.com/docs/extend/forms#textarea>).

#### Translatable rich editor

The `trlricheditor` form widget is used to create a rich text editor input field that can store translations for multiple locales.

```php
'my_field' => [
    'label' => 'Content',
    'type' => 'trlricheditor',
],
```

The options for the `trlricheditor` form widget type are the same as the `richeditor` [form widget type](https://tastyigniter.com/docs/extend/forms#rich-editor--wysiwyg).

#### Translatable markdown editor

The `trlmarkdowneditor` form widget is used to create a markdown editor input field that can store translations for multiple locales.

```php
'my_field' => [
    'label' => 'Content',
    'type' => 'trlmarkdowneditor',
],
```

The options for the `trlmarkdowneditor` form widget type are the same as the `markdowneditor` [form widget type](https://tastyigniter.com/docs/extend/forms#markdown-editor).

#### Translatable repeater

The `trlrepeater` form widget is used to create a repeater input field that can store translations for multiple locales.

```php
'my_field' => [
    'label' => 'Items',
    'type' => 'trlrepeater',
],
```

The options for the `trlrepeater` form widget type are the same as the [`repeater` form widget type](https://tastyigniter.com/docs/extend/forms#repeater).

