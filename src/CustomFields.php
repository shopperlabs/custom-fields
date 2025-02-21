<?php

declare(strict_types=1);

namespace Relaticle\CustomFields;

class CustomFields
{

    /**
     * The custom field model that should be used by Custom Fields.
     *
     * @var string
     */
    public static string $customFieldModel = 'Relaticle\\CustomFields\\Models\\CustomField';


    /**
     * Get the name of the custom field model used by the application.
     *
     * @return string
     */
    public static function customFieldModel(): string
    {
        return static::$customFieldModel;
    }

    /**
     * Get a new instance of the custom field model.
     *
     * @return mixed
     */
    public static function newCustomFieldModel(): mixed
    {
        $model = static::customFieldModel();

        return new $model;
    }

    /**
     * Specify the custom field model that should be used by Custom Fields.
     *
     * @param string $model
     * @return static
     */
    public static function useCustomFieldModel(string $model): static
    {
        static::$customFieldModel = $model;

        return new static;
    }

}
