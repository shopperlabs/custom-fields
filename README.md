# Custom Fields

## Introduction

Custom Fields is a powerful and flexible plugin for Filament that empowers developers to create dynamic, customizable
form fields for their resources. This package allows you to effortlessly add user-defined fields to your Filament
resources, enhancing the adaptability and functionality of your admin panel.

At its core, Custom Fields enables you to create a wide variety of field types, including text inputs, textareas, select
dropdowns, radio buttons, and more. These custom fields can be easily assigned to any resource, providing a tailored
data input experience for your users.

The package is designed to be intuitive for developers to implement and effortless for end-users to utilize. Custom
Fields integrates smoothly with Filament's existing form builder, ensuring a consistent and familiar experience
throughout your admin panel.

Enhance your Filament admin panel's capabilities and give your users the power of customization with Custom Fields.

### Demo

See all the functionality that Custom Fields has to offer.

[Visit the Demo](https://filament-custom-fields.manukminasyan.com)

### Video

Check out a short video of some of the powerful features included in Custom Fields

[Watch the video](https://www.youtube.com/watch?v=QnZ9qXwGwR0)

# Features

- Create custom fields with a wide variety of input types:
    - Text
    - Number
    - Link
    - Textarea
    - Currency
    - Date
    - Date and Time
    - Toggle
    - Toggle buttons
    - Select
    - Checkbox
    - Checkbox list
    - Radio
    - Rich editor
    - Markdown editor
    - Tags input
    - Color picker
    - Multi-select
- **(Coming Soon)** More field types to be added
- Easily assign custom fields to any Filament resource
- Set up validation rules for each custom field to ensure data integrity
- Dynamically render custom fields in Filament forms
- Store and retrieve custom field data seamlessly with your existing models
- **(New)** Custom Field Builder interface for easy field creation and management
- **(New)** Drag-and-drop interface for organizing custom fields within resources
- Developers can create Preset Custom Fields in code and deploy them for their clients
- Full support for dark mode
- Compatible with Filament's form layouts and responsive design
- Seamless integration with Filament's existing form validation and error handling

## Installation

Thank you for purchasing Custom Fields!

Below you'll find extensive documentation on installing and using this plugin. Of course, if you have any questions,
find a bug, need support, or have a feature request, please don't hesitate to reach out to me at
`customfieldsnext@gmail.com`.

```bash
composer require manukminasyan/filament-custom-fields
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-custom-fields-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-custom-fields-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-custom-fields-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$filamentAttribute = new ManukMinasyan\FilamentAttribute();
echo $filamentAttribute->echoPhrase('Hello, ManukMinasyan!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [manukminasyan](https://github.com/manukminasyan)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
