<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Tests\Unit\Filament\Forms\Components;

use Filament\Forms\Components\Component;
use ReflectionClass;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldValidationComponent;
use Relaticle\CustomFields\Tests\TestCase;

class CustomFieldValidationComponentTest extends TestCase
{
    /** @test */
    public function it_can_detect_parameter_index_from_state_path()
    {
        $component = new CustomFieldValidationComponent();
        
        // Use reflection to access private method
        $reflection = new ReflectionClass($component);
        $method = $reflection->getMethod('getParameterIndex');
        $method->setAccessible(true);
        
        // Create a mock component with various state paths
        $mockComponent = $this->createMock(Component::class);
        
        // Test index 0
        $mockComponent->method('getStatePath')->willReturn('validation_rules.0.parameters.0.value');
        $this->assertEquals(0, $method->invoke($component, $mockComponent));
        
        // Test index 1
        $mockComponent = $this->createMock(Component::class);
        $mockComponent->method('getStatePath')->willReturn('validation_rules.0.parameters.1.value');
        $this->assertEquals(1, $method->invoke($component, $mockComponent));
        
        // Test index 10
        $mockComponent = $this->createMock(Component::class);
        $mockComponent->method('getStatePath')->willReturn('validation_rules.0.parameters.10.value');
        $this->assertEquals(10, $method->invoke($component, $mockComponent));
        
        // Test with nested paths
        $mockComponent = $this->createMock(Component::class);
        $mockComponent->method('getStatePath')->willReturn('data.validation_rules.2.parameters.3.value');
        $this->assertEquals(3, $method->invoke($component, $mockComponent));
        
        // Test with no match (should return 0)
        $mockComponent = $this->createMock(Component::class);
        $mockComponent->method('getStatePath')->willReturn('some.other.path');
        $this->assertEquals(0, $method->invoke($component, $mockComponent));
    }
    
    /** @test */
    public function it_handles_complex_state_paths()
    {
        $component = new CustomFieldValidationComponent();
        
        // Use reflection to access private method
        $reflection = new ReflectionClass($component);
        $method = $reflection->getMethod('getParameterIndex');
        $method->setAccessible(true);
        
        // Test with more complex paths
        $mockComponent = $this->createMock(Component::class);
        $mockComponent->method('getStatePath')->willReturn('data.tabs.validation.validation_rules.5.parameters.2.value.something');
        $this->assertEquals(2, $method->invoke($component, $mockComponent));
        
        // Test multiple parameter patterns in path (should match the first one)
        $mockComponent = $this->createMock(Component::class);
        $mockComponent->method('getStatePath')->willReturn('parameters.7.value.nested.parameters.3.value');
        $this->assertEquals(7, $method->invoke($component, $mockComponent));
    }
}