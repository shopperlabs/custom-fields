<?php

declare(strict_types=1);

namespace Relaticle\CustomFields;

class CustomFields
{
    /**
     * The custom field model that should be used by Custom Fields.
     */
    public static string $customFieldModel = 'Relaticle\\CustomFields\\Models\\CustomField';

    /**
     * The custom field value model that should be used by Custom Fields.
     */
    public static string $valueModel = 'Relaticle\\CustomFields\\Models\\CustomFieldValue';

    /**
     * The custom field option model that should be used by Custom Fields.
     */
    public static string $optionModel = 'Relaticle\\CustomFields\\Models\\CustomFieldOption';

    /**
     * The custom field section model that should be used by Custom Fields.
     */
    public static string $sectionModel = 'Relaticle\\CustomFields\\Models\\CustomFieldSection';

    /**
     * Get the name of the custom field model used by the application.
     */
    public static function customFieldModel(): string
    {
        return static::$customFieldModel;
    }

    /**
     * Get a new instance of the custom field model.
     */
    public static function newCustomFieldModel(): mixed
    {
        $model = static::customFieldModel();

        return new $model;
    }

    /**
     * Specify the custom field model that should be used by Custom Fields.
     */
    public static function useCustomFieldModel(string $model): static
    {
        static::$customFieldModel = $model;

        return new static;
    }

    /**
     * Get the name of the custom field value model used by the application.
     */
    public static function valueModel(): string
    {
        return static::$valueModel;
    }

    /**
     * Get a new instance of the custom field value model.
     */
    public static function newValueModel(): mixed
    {
        $model = static::valueModel();

        return new $model;
    }

    /**
     * Specify the custom field value model that should be used by Custom Fields.
     */
    public static function useValueModel(string $model): static
    {
        static::$valueModel = $model;

        return new static;
    }

    /**
     * Get the name of the custom field option model used by the application.
     */
    public static function optionModel(): string
    {
        return static::$optionModel;
    }

    /**
     * Get a new instance of the custom field option model.
     */
    public static function newOptionModel(): mixed
    {
        $model = static::optionModel();

        return new $model;
    }

    /**
     * Specify the custom field option model that should be used by Custom Fields.
     */
    public static function useOptionModel(string $model): static
    {
        static::$optionModel = $model;

        return new static;
    }

    /**
     * Get the name of the custom field section model used by the application.
     */
    public static function sectionModel(): string
    {
        return static::$sectionModel;
    }

    /**
     * Get a new instance of the custom field section model.
     */
    public static function newSectionModel(): mixed
    {
        $model = static::sectionModel();

        return new $model;
    }

    /**
     * Specify the custom field section model that should be used by Custom Fields.
     */
    public static function useSectionModel(string $model): static
    {
        static::$sectionModel = $model;

        return new static;
    }
}
