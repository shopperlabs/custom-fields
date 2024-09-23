# Custom Fields

## Table of Contents

1. [Introduction](#introduction)
2. [Demo](#demo)
3. [Video](#video)
4. [Features](#features)
5. [Installation](#installation)
6. [Setting up Custom Fields](#setting-up-custom-fields)
    - [Step 1: Publish and Run Migrations](#step-1-publish-and-run-migrations)
    - [Step 2: Publish the Configuration File (Optional)](#step-2-publish-the-configuration-file-optional)
    - [Step 3: Integrating Custom Fields Plugin](#step-3-integrating-custom-fields-plugin)
    - [Step 4: Adding Custom Fields to Forms](#step-4-adding-custom-fields-to-forms)
    - [Step 5: Displaying Custom Fields in Table Views](#step-5-displaying-custom-fields-in-table-views)
    - [Step 6: Setting Up the Model](#step-6-setting-up-the-model)
7. [Configuration](#configuration)
8. [Support](#support)
9. [Changelog](#changelog)
10. [Contributing](#contributing)
11. [Credits](#credits)
12. [License](#license)
13. [Code Distribution](#code-distribution)
14. [Questions?](#questions)

---

## Introduction

**Custom Fields** is a powerful and flexible plugin for [Filament](https://filamentphp.com/) that enables developers to
create dynamic, customizable form fields for their resources. This package allows you to effortlessly add user-defined
fields to your Filament resources, enhancing the adaptability and functionality of your admin panel.

With Custom Fields, you can define a wide variety of field types, including text inputs, textareas, select dropdowns,
radio buttons, and more. These custom fields can be assigned to any resource, providing a tailored data input experience
for your users. The plugin integrates smoothly with Filament's existing form builder, ensuring a consistent and familiar
experience throughout your admin panel.

---

## Demo

Experience the full functionality of Custom Fields in action.

[**Visit the Demo**](https://custom-fields.relaticle.com)

---

## Video

Watch a short demonstration showcasing some of the powerful features included in Custom Fields.

[**Watch the Video**](https://www.youtube.com/watch?v=QnZ9qXwGwR0)

---

## Features

- **Wide Variety of Field Types**:
    - Text, Number, Link, Textarea, Currency, Date, Date and Time, Toggle, Toggle Buttons, Select, Checkbox, Checkbox
      List, Radio, Rich Editor, Markdown Editor, Tags Input, Color Picker, Multi-select.
    - **Coming Soon**: Additional field types to be added.

- **Dynamic Field Management**:
    - Easily create, edit, and delete custom fields.
    - Assign custom fields to any Filament resource.
    - Organize custom fields using a drag-and-drop interface.

- **Validation and Data Integrity**:
    - Define validation rules for each custom field.
    - Ensure data integrity and prevent invalid data submissions.

- **Seamless Integration with Filament**:
    - Dynamically render custom fields in Filament forms.
    - Display custom fields in table views automatically.
    - Full support for dark mode and responsive design.

- **Advanced Features**:
    - Custom Field Builder interface for easy field creation and management.
    - Developers can create preset custom fields in code and deploy them for clients.
    - Compatible with Filament's form layouts and validation.
    - Support for multi-tenancy and tenant awareness.

---

## Installation

Thank you for purchasing **Custom Fields**! This guide provides step-by-step instructions on installing and configuring
the plugin.

### Requirements

- **PHP**: 8.0 or higher
- **Laravel**: 9.x or higher
- **Filament**: 3.x

### Activating your license on AnyStack

Custom Fields uses AnyStack to handle payment, licensing, and distribution.

During the purchasing process, AnyStack will provide you with a license key. You will also be asked by AnyStack to
activate your license by providing a domain. This is usually the domain of where your final project will live. You’ll
use this same domain to install locally and in production. Once you have provide a domain, your license key will be
activated and you can proceed with installing with composer below.

Tip: If you missed this step, or if you need to add additional domains for other projects, you can access the activation
page by going to Transactions in your AnyStack account and then clicking View details on the Custom Fields product.

Tip: You will need both your license key and your domain to authenticate when you install the package with composer.

### Installing with Composer

To install Custom Fields you'll need to add the package to your composer.json file:

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "https://filament-filter-sets.composer.sh"
        }
    ]
}
```

Once the repository has been added to your composer.json file, you can install Custom Fields like any other composer
package using the composer require command:

```bash
composer require relaticle/custom-fields
```

Next, you will be prompted to provide your username and password.

```bash
Loading composer repositories with package information
Authentication required (custom-fields.composer.sh):
Username: [licensee-email]
Password: [license-key]
```

Your username will be your email address and the password will is your license key, followed by a colon (:), followed by the domain you are activating. For example, let's say we have the following email and license activation:

- Contact email: myname@example.com
- License key: 9f3a2e1d-5b7c-4f86-a9d0-3e1c2b4a5f8e
- Activation fingerprint: my_domain.com

- You will need to enter the above information as follows when prompted for your credentials:

```bash
Loading composer repositories with package information
Authentication required (custom-fields.composer.sh):
Username: myname@example.com
Password: 9f3a2e1d-5b7c-4f86-a9d0-3e1c2b4a5f8e:my_domain.com
```

The license key and fingerprint should be separated by a colon (:).

> Tip: If you get a 402 error, most likely you forgot to add the colon and fingerprint.


---

## Setting up Custom Fields

This section will guide you through setting up and using the Custom Fields plugin in your Filament admin panel.

### Step 1: Publish and Run Migrations

Publish the migration files:

```bash
php artisan vendor:publish --tag="custom-fields-migrations"
```

Run the migrations:

```bash
php artisan migrate
```

### Step 2: Publish the Configuration File (Optional)

If you wish to customize the configuration, publish the config file:

```bash
php artisan vendor:publish --tag="custom-fields-config"
```

This will create a `custom-fields.php` file in your `config` directory.

### Step 3: Integrating Custom Fields Plugin

In order to use Custom Fields, you need to register the plugin with your Filament panel. This is done in
your `PanelProvider` or wherever you are configuring your Filament panel.

```php
use Relaticle\CustomFields\CustomFieldsPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other panel configurations
        ->plugins([
            CustomFieldsPlugin::make(),
        ]);
}
```

### Step 4: Adding Custom Fields to Forms

To include custom fields in your resource forms, you'll use the `CustomFieldsComponent`. This component dynamically adds
all the custom fields associated with the resource.

In your resource class (e.g., `CompanyResource`), modify the `form()` method:

```php
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent;
use Filament\Forms;
use Filament\Resources\Form;

class CompanyResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Your existing form fields
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email(),

                // Add the CustomFieldsComponent
                CustomFieldsComponent::make()
                    ->columns(1), // You can adjust the number of columns
            ]);
    }

    // ...
}
```

### Step 5: Displaying Custom Fields in Table Views

To display custom fields in your table views, you need to use the `InteractsWithCustomFields` trait in your
resource's `ListRecords` page.

In your `ListCompanies` class, add the trait:

```php
use Relaticle\CustomFields\Filament\Tables\Concerns\InteractsWithCustomFields;
use Filament\Resources\Pages\ListRecords;

class ListCompanies extends ListRecords
{
    use InteractsWithCustomFields;

    // ...
}
```

This trait automatically includes all custom fields in the table view for the resource.

### Step 6: Setting Up the Model

Your model needs to implement the `HasCustomFields` interface and use the `UsesCustomFields` trait to work with custom
fields.

In your `Company` model, add the following:

```php
use Relaticle\CustomFields\Models\Contracts\HasCustomFields;
use Relaticle\CustomFields\Models\Concerns\UsesCustomFields;
use Illuminate\Database\Eloquent\Model;

class Company extends Model implements HasCustomFields
{
    use UsesCustomFields;

    // ... your existing model code
}
```

By following these steps, your model will be fully equipped to handle custom fields, and you will have successfully set
up custom fields in your Filament panel, enabling dynamic and flexible data management.

---

## Configuration

The configuration file (`config/custom-fields.php`) allows you to customize various aspects of the plugin. Below is the
default configuration:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Entity Resources Configuration
    |--------------------------------------------------------------------------
    |
    | This section controls which Filament resources are allowed or disallowed
    | to have custom fields. You can specify allowed resources, disallowed
    | resources, or leave them empty to use default behavior.
    |
    */
    'allowed_entity_resources' => [
        // App\Filament\Resources\UserResource::class,
    ],

    'disallowed_entity_resources' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Lookup Resources Configuration
    |--------------------------------------------------------------------------
    |
    | Define which Filament resources can be used as lookups. You can specify
    | allowed resources, disallowed resources, or leave them empty to use
    | default behavior.
    |
    */
    'allowed_lookup_resources' => [
        //
    ],

    'disallowed_lookup_resources' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Table Configuration
    |--------------------------------------------------------------------------
    |
    | This section allows you to customize the behavior of resource tables,
    | such as enabling column toggling and setting default visibility.
    |
    */
    'resource' => [
        'table' => [
            'columns_toggleable' => [
                'enabled' => true,
                'hidden_by_default' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Awareness Configuration
    |--------------------------------------------------------------------------
    |
    | When enabled, this feature implements multi-tenancy using the specified
    | tenant foreign key. Enable this before running migrations to automatically
    | register the tenant foreign key.
    |
    */
    'tenant_aware' => false,

    /*
    |--------------------------------------------------------------------------
    | Database Table Names
    |--------------------------------------------------------------------------
    |
    | You can specify custom table names for the package's database tables here.
    | These tables will be used to store custom fields, their values, and options.
    |
    */
    'table_names' => [
        'custom_fields' => 'custom_fields',
        'custom_field_values' => 'custom_field_values',
        'custom_field_options' => 'custom_field_options',
    ],

    /*
    |--------------------------------------------------------------------------
    | Column Names
    |--------------------------------------------------------------------------
    |
    | Here you can customize the names of specific columns used by the package.
    | For example, you can change the name of the tenant foreign key if needed.
    |
    */
    'column_names' => [
        'tenant_foreign_key' => 'tenant_id',
    ],
];
```

---

## Support

Need assistance? Encountered a bug? Have a feature request? We’re here to help!

- **Email**: [`customfieldsnext@gmail.com`](mailto:customfieldsnext@gmail.com)
- **Bug Reports & Feature Requests**: [GitHub Issues](https://github.com/your-repo/issues) *(Replace with your actual
  repository link)*

---

## Changelog

Stay updated with the latest changes, improvements, and bug fixes.

[**View Changelog**](CHANGELOG.md)

---

## Contributing

We welcome contributions from the community! If you hold an active license, you can access the private repository to
contribute.

---

## Credits

- **[ManukMinasyan](https://github.com/ManukMinasyan)**

---

## License

### Single License

The **Single License** grants permission to use Custom Fields in a single project hosted on one domain or subdomain.
Suitable for personal websites or projects for a single client.

- **Usage**: Single project on one domain/subdomain
- **Users**: Up to 5 Employees and Contractors
- **Support & Updates**: Included for one year from the date of purchase
- **Renewal**: Discounted renewal available after expiration to continue receiving updates and new features

*Note*: For SaaS applications or multiple projects, please refer to our other licensing options.

### Unlimited License

The **Unlimited License** allows the use of Custom Fields on unlimited domains and subdomains, including SaaS
applications.

- **Usage**: Unlimited projects on multiple domains/subdomains
- **Users**: Up to 25 Employees and Contractors
- **Support & Updates**: Included for one year from the date of purchase
- **Renewal**: Discounted renewal available after expiration to continue receiving updates and new features

### Lifetime License

The **Lifetime License** offers the same benefits as the Unlimited License with lifetime updates.

- **Usage**: Unlimited projects on multiple domains/subdomains
- **Users**: Up to 25 Employees and Contractors
- **Support & Updates**: Lifetime updates and bug fixes
- **Renewal**: No renewal needed

---

## Code Distribution

**Custom Fields** licenses strictly prohibit the public distribution of its source code. You may not:

- Build applications using Custom Fields and distribute them publicly via open-source repositories, hosting platforms,
  or any other code distribution platforms.

Violating this policy may result in license termination and potential legal action.

---

## Questions?

Unsure which license best fits your needs or have other questions? We're here to help!

- **Email Us**: [`customfieldsnext@gmail.com`](mailto:customfieldsnext@gmail.com)
- **Visit Our [Support Page](https://custom-fields.relaticle.com/support)** *(Replace with your actual support link)*

Feel free to reach out with your queries, and we'll get back to you promptly.

---

## Thank You

Thank you for choosing **Custom Fields**! We are committed to providing you with the tools and support needed to create
dynamic and customizable applications.
