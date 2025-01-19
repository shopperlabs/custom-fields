<?php

namespace Relaticle\CustomFields\Filament\FormSchemas;

use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Relaticle\CustomFields\Enums\CustomFieldSectionType;
use Relaticle\CustomFields\Models\CustomFieldSection;
use Relaticle\CustomFields\Support\Utils;
use Filament\Forms;

class SectionForm implements FormInterface
{
    public static function schema(): array
    {
        return [
            Forms\Components\Grid::make(12)->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->maxLength(50)
                    ->unique(
                        table: CustomFieldSection::class,
                        column: 'name',
                        ignoreRecord: true,
                        modifyRuleUsing: function (Unique $rule, Forms\Get $get) {
                            return $rule->when(
                                Utils::isTenantEnabled(),
                                function (Unique $rule) {
                                    return $rule->where(
                                        config('custom-fields.column_names.tenant_foreign_key'),
                                        Filament::getTenant()?->id
                                    );
                                });
                        },
                    )
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $old, ?string $state): void {
                        $old ??= '';
                        $state ??= '';

                        if (($get('code') ?? '') !== Str::of($old)->slug('_')->toString()) {
                            return;
                        }

                        $set('code', Str::of($state)->slug('_')->toString());
                    })
                    ->columnSpan(6),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->alphaDash()
                    ->maxLength(50)
                    ->unique(
                        table: CustomFieldSection::class,
                        column: 'code',
                        ignoreRecord: true,
                        modifyRuleUsing: function (Unique $rule, Forms\Get $get) {
                            return $rule->when(
                                Utils::isTenantEnabled(),
                                function (Unique $rule) {
                                    return $rule->where(
                                        config('custom-fields.column_names.tenant_foreign_key'),
                                        Filament::getTenant()?->id
                                    );
                                });
                        },
                    )
                    ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                        $set('code', Str::of($state)->slug('_')->toString());
                    })
                    ->columnSpan(6),
                Forms\Components\Select::make('type')
                    ->reactive()
                    ->default(CustomFieldSectionType::SECTION->value)
                    ->options(CustomFieldSectionType::class)
                    ->required()
                    ->columnSpan(12),
                Forms\Components\Textarea::make('description')
                    ->visible(fn(Forms\Get $get): bool => $get('type') === CustomFieldSectionType::SECTION->value)
                    ->maxLength(255)
                    ->nullable()
                    ->columnSpan(12),
            ])
        ];
    }
}
