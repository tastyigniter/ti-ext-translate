### Language Picker Component

Using the LocalePicker component a visitor can choose their preferred language. This component shows a simple dropdown,
which will change the language of the page depending on the selection.

Add the `Language Picker` component to your layout or page

```html
---
title: 'Home'
permalink: /

'[localePicker]':
---

@component('localePicker')
```

If the text on the page is translated, it will appear as whatever language the user selects. The dropdown is very simple
and customisable:

```html
[...]
---

<p>
    Switch language to:
    <a href="#" data-request="localePicker::onSwitchLocale" data-request-data="locale: 'en'">English</a>,
    <a href="#" data-request="localePicker::onSwitchLocale" data-request-data="locale: 'es'">Spanish</a>
</p>
```

### Model translation

Models can have their attributes translated by using the `Igniter\Translate\Actions\TranslatableModel` action and
specifying which attributes to translate in the class.

```php

class Category
{
    public $implement = [\Igniter\Translate\Actions\TranslatableModel::class];

    public function translatable() {
        return ['name'];
    }
}
```
