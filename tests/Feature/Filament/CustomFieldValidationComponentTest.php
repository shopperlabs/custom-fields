<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Tests\Feature\Filament;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Component;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Testing\TestsForms;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Enums\CustomFieldValidationRule;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldValidationComponent;
use Relaticle\CustomFields\Tests\TestCase;

class CustomFieldValidationComponentTest extends TestCase
{
    use TestsForms;

    /** @test */
    public function it_adds_validation_rules_to_parameter_fields()
    {
        $component = $this->getComponentContainer([
            'type' => CustomFieldType::TEXT->value,
            'validation_rules' => [
                [
                    'name' => CustomFieldValidationRule::MIN->value,
                    'parameters' => [
                        ['value' => '5'],
                    ],
                ],
            ],
        ]);
        
        $this->assertContains('required', $component->getState()['validation_rules'][0]['parameters'][0]['value']->getRules());
        $this->assertContains('numeric', $component->getState()['validation_rules'][0]['parameters'][0]['value']->getRules());
    }
    
    /** @test */
    public function it_displays_help_text_for_parameters()
    {
        $component = $this->getComponentContainer([
            'type' => CustomFieldType::TEXT->value,
            'validation_rules' => [
                [
                    'name' => CustomFieldValidationRule::MIN->value,
                    'parameters' => [
                        ['value' => '5'],
                    ],
                ],
            ],
        ]);
        
        $this->assertEquals(
            __('custom-fields::custom-fields.validation.parameter_help.min'),
            $component->getState()['validation_rules'][0]['parameters'][0]['value']->getHint()
        );
    }
    
    /** @test */
    public function it_validates_between_rule_parameters_correctly()
    {
        $component = $this->getComponentContainer([
            'type' => CustomFieldType::NUMERIC->value,
            'validation_rules' => [
                [
                    'name' => CustomFieldValidationRule::BETWEEN->value,
                    'parameters' => [
                        ['value' => '10'], // min
                        ['value' => '5'],  // max (should fail validation)
                    ],
                ],
            ],
        ]);
        
        $validationRules = $component->getState()['validation_rules'][0]['parameters'][1]['value']->getRules();
        
        // Max value should include gte:parameters.0.value rule
        $containsGteRule = false;
        foreach ($validationRules as $rule) {
            if (is_string($rule) && $rule === 'gte:parameters.0.value') {
                $containsGteRule = true;
                break;
            }
        }
        
        $this->assertTrue($containsGteRule, 'Second parameter should validate that it is >= the first parameter');
    }
    
    /** @test */
    public function it_normalizes_parameter_values()
    {
        $component = $this->getComponentContainer([
            'type' => CustomFieldType::NUMERIC->value,
            'validation_rules' => [
                [
                    'name' => CustomFieldValidationRule::MIN->value,
                    'parameters' => [
                        ['value' => '10.0'],
                    ],
                ],
            ],
        ]);
        
        // Access the internal state of the component
        $data = $component->getState(shouldCallHooks: true);
        
        // Verify that the value has been normalized
        $this->assertEquals('10', $data['validation_rules'][0]['parameters'][0]['value']);
    }
    
    /** @test */
    public function it_provides_suggestions_for_parameters()
    {
        $component = $this->getComponentContainer([
            'type' => CustomFieldType::DATE->value,
            'validation_rules' => [
                [
                    'name' => CustomFieldValidationRule::DATE_FORMAT->value,
                    'parameters' => [
                        ['value' => ''],
                    ],
                ],
            ],
        ]);
        
        $datalist = $component->getState()['validation_rules'][0]['parameters'][0]['value']->getDatalist();
        
        $this->assertNotEmpty($datalist);
        $this->assertContains('Y-m-d', $datalist);
    }
    
    /** @test */
    public function it_clears_parameters_when_rule_changes()
    {
        $livewire = $this->mountFormComponentAction(
            CustomFieldValidationComponent::make(),
            [
                'type' => CustomFieldType::TEXT->value,
                'validation_rules' => [
                    [
                        'name' => CustomFieldValidationRule::MIN->value,
                        'parameters' => [
                            ['value' => '5'],
                        ],
                    ],
                ],
            ]
        );
        
        $this->assertCount(1, $livewire->get('data.validation_rules.0.parameters'));
        
        // Change the rule
        $livewire->set('data.validation_rules.0.name', CustomFieldValidationRule::REGEX->value);
        
        // Parameters should be reset
        $this->assertCount(0, $livewire->get('data.validation_rules.0.parameters'));
    }
    
    private function getComponentContainer($data): ComponentContainer
    {
        $fieldValidationComponent = CustomFieldValidationComponent::make();
        
        $container = $this->getMockBuilder(ComponentContainer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRawState', 'getState'])
            ->getMock();
        
        $container->method('getRawState')
            ->willReturn($data);
            
        $container->method('getState')
            ->willReturn(function ($key = null) use ($data) {
                if ($key === null) {
                    return $data;
                }
                
                return $data[$key] ?? null;
            });
        
        // Create mocks for parameter components with proper state paths
        $this->mockParameterComponents($data, $fieldValidationComponent);
        
        $fieldValidationComponent->container($container);
        
        return $container;
    }
    
    /**
     * Mock parameter components with appropriate state paths
     */
    private function mockParameterComponents(array $data, CustomFieldValidationComponent $component): void
    {
        if (empty($data['validation_rules'])) {
            return;
        }
        
        foreach ($data['validation_rules'] as $ruleIndex => $rule) {
            if (empty($rule['parameters'])) {
                continue;
            }
            
            foreach ($rule['parameters'] as $paramIndex => $parameter) {
                $paramComponent = $this->getMockBuilder(Component::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods(['getStatePath'])
                    ->getMock();
                
                $paramComponent->method('getStatePath')
                    ->willReturn("validation_rules.{$ruleIndex}.parameters.{$paramIndex}.value");
                
                // Since we can't directly set the component instance, we attach the mock
                // to our parameter value in the state
                $data['validation_rules'][$ruleIndex]['parameters'][$paramIndex]['value'] = $paramComponent;
            }
        }
    }
}