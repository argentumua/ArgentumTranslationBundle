ArgentumTranslationBundle
=========================

[![Build Status](https://travis-ci.org/argentumua/ArgentumTranslationBundle.svg?branch=master)](https://travis-ci.org/argentumua/ArgentumTranslationBundle)
[![Coverage Status](https://img.shields.io/coveralls/argentumua/ArgentumTranslationBundle.svg)](https://coveralls.io/r/argentumua/ArgentumTranslationBundle)
[![Latest Stable Version](https://poser.pugx.org/argentum/translation-bundle/v/stable.svg)](https://packagist.org/packages/argentum/translation-bundle)
[![License](https://poser.pugx.org/argentum/translation-bundle/license.svg)](https://packagist.org/packages/argentum/translation-bundle)

ArgentumTranslationBundle provides a database storage for translations and GUI for editing them.

This bundle supports PHP 5.4+ and Symfony 2.3+.

The bundle combines the power of the following bundles:

- [doctrine/orm](https://packagist.org/packages/doctrine/orm)
- [knplabs/doctrine-behaviors](https://packagist.org/packages/knplabs/doctrine-behaviors)
- [a2lix/translation-form-bundle](https://packagist.org/packages/a2lix/translation-form-bundle)
- [sonata-project/admin-bundle](https://packagist.org/packages/sonata-project/admin-bundle)

Installation
------------

Install the bundle with composer:
```sh
composer require argentum/translation-bundle:dev-master
```

Register the bundle in ```app/AppKernel::registerBundles()```:
```php
$bundles = array(
    // ...
    new Argentum\TranslationBundle\ArgentumTranslationBundle(),
    // ...
);
```

Configuration
-------------

In the ```parameters.yml``` you should configure a list of locales:
```yml
parameters:
    locales:
        - ru
        - uk
        - en
```

Then in the ```config.yml``` you should add the following options:
```yml
a2lix_translation_form:
    locales: %locales%

argentum_translation:
    locales: %locales%
```

And finally, create translation tables in your database:
```sh
app/console doctrine:schema:update --force
```

Translation editor
-------------------

Now you can use GUI for editing the translations.

The bundle will automatically add additional items in the menu of your Sonata Admin.

The list of all domains is located at URL something like (depends on the base location of Sonata Admin in your project): ```/admin/argentum/translation/translationdomain/list```.

Loading order
-------------

When merging all translations together translations from files in all bundles will be loaded, and then all translations from the database will be loaded.
Thus, translations from the database can override any translation from the translation files in your project.

Proposed workflow
-----------------

Firstly, when developing your project you should use admin panel to create all necessary domains and add all necessary translations to them.

Secondly, when your bundle is ready, you should export all translations for the bundle domain into the bundle translation files and commit them to VCS.

And finally, when your project is started, you or your client can edit any translation without need to edit project files.

Cache
-----

All translations are cached in the cache files and will be loaded from the database only once after clearing the cache.

Export
------

This bundle allows you to export translations from the database for single domain or all domains in the specified directory for the specified locales using the specified format.

Export all translations to the directory:
```sh
app/console translation:export --dir=/path/to/translations
```

Export translations for domain "messages":
```sh
app/console translation:export --dir=/path/to/translations messages
```

Export translations for domain "messages" and locales "ru" and "uk" in xliff format:
```sh
app/console translation:export --dir=/path/to/translations --locales="ru,uk" --format=xlf messages
```

Import
------

This bundle allows you to import translations from files into the database.

Import all translations from the specified directory:
```sh
app/console translation:import --dir=/path/to/translations
```

Import translations from the directory only for the domain "messages":
```sh
app/console translation:import --dir=/path/to/translations messages
```

Import translations from the directory only for the domain "messages" and locales "ru" and "uk":
```sh
app/console translation:import --dir=/path/to/translations --locales="ru,uk" messages
```

Import translations from the specified bundles:
```sh
app/console translation:import --bundles="SonataAdminBundle, SonataUserBundle"
```

Import translations from the entire project including all bundles, Form and Validation
components, and local application translations:
```sh
app/console translation:import
```
