<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use ManukMinasyan\FilamentCustomField\Enums\AttributeEntityTypeEnum;
use ManukMinasyan\FilamentCustomField\Enums\AttributeLookupTypeEnum;
use ManukMinasyan\FilamentCustomField\Enums\CustomFieldType;
use ManukMinasyan\FilamentCustomField\Models\CustomField;

/**
 * @extends Factory<CustomField>
 */
final class AttributeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<CustomField>
     */
    protected $model = CustomField::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->word(),
            'name' => $this->faker->name(),
            'type' => $this->faker->randomElement(CustomFieldType::cases()),
            'lookup_type' => $this->faker->randomElement(AttributeLookupTypeEnum::cases()),
            'entity_type' => $this->faker->randomElement(AttributeEntityTypeEnum::cases()),
            'sort_order' => $this->faker->randomNumber(),
            'validation' => $this->faker->word(),
            'is_required' => $this->faker->boolean(),
            'is_unique' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
