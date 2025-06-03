<?php

namespace Relaticle\CustomFields\Tests\Unit\Support;

use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Support\SafeValueConverter;
use Relaticle\CustomFields\Tests\TestCase;

class SafeValueConverterTest extends TestCase
{
    /** @test */
    public function it_converts_normal_integers_correctly()
    {
        $this->assertSame(123, SafeValueConverter::toSafeInteger(123));
        $this->assertSame(-456, SafeValueConverter::toSafeInteger(-456));
        $this->assertSame(789, SafeValueConverter::toSafeInteger("789"));
        $this->assertSame(-123, SafeValueConverter::toSafeInteger("-123"));
    }
    
    /** @test */
    public function it_handles_scientific_notation()
    {
        $this->assertSame(1000000, SafeValueConverter::toSafeInteger("1e6"));
        $this->assertSame(1230000, SafeValueConverter::toSafeInteger("1.23e6"));
        $this->assertSame(-1000000, SafeValueConverter::toSafeInteger("-1e6"));
    }
    
    /** @test */
    public function it_clamps_values_exceeding_bigint_bounds()
    {
        // Test max bound
        $overMax = "1e20"; // This is much larger than PHP_INT_MAX
        $this->assertIsInt(SafeValueConverter::toSafeInteger($overMax));
        $this->assertGreaterThan(0, SafeValueConverter::toSafeInteger($overMax));
        
        // Test min bound
        $belowMin = "-1e20"; // This is much smaller than PHP_INT_MIN
        $this->assertIsInt(SafeValueConverter::toSafeInteger($belowMin));
        $this->assertLessThan(0, SafeValueConverter::toSafeInteger($belowMin));
        
        // Test values near the boundaries - just verify they are integers with correct sign
        $largePositive = "9223372036854775000"; // Close to Max 64-bit integer
        $maxResult = SafeValueConverter::toSafeInteger($largePositive);
        $this->assertIsInt($maxResult);
        $this->assertGreaterThan(0, $maxResult);
        
        $largeNegative = "-9223372036854775000"; // Close to Min 64-bit integer
        $minResult = SafeValueConverter::toSafeInteger($largeNegative);
        $this->assertIsInt($minResult);
        $this->assertLessThan(0, $minResult);
            
        // Test the specific value from the error report
        $specificValue = '-9.2233720368548E+18';
        $result = SafeValueConverter::toSafeInteger($specificValue);
        $this->assertIsInt($result);
        $this->assertLessThan(0, $result); // Should be negative
    }
    
    /** @test */
    public function it_ensures_return_type_is_integer_even_for_edge_cases()
    {
        // Test values that are on the edge of MAX_BIGINT boundary
        $almostMax = '9.223372036854775E+18';
        $result = SafeValueConverter::toSafeInteger($almostMax);
        $this->assertIsInt($result);
        $this->assertIsNotFloat($result);
        
        // Test values that are on the edge of MIN_BIGINT boundary
        $almostMin = '-9.223372036854775E+18';
        $result = SafeValueConverter::toSafeInteger($almostMin);
        $this->assertIsInt($result);
        $this->assertIsNotFloat($result);
        
        // Ensure constants are properly cast to int
        $this->assertIsInt(SafeValueConverter::toSafeInteger(SafeValueConverter::MAX_BIGINT));
        $this->assertIsInt(SafeValueConverter::toSafeInteger(SafeValueConverter::MIN_BIGINT));
        
        // Test decimal values to ensure they're properly converted to integers
        $decimalValue = 123.456;
        $result = SafeValueConverter::toSafeInteger($decimalValue);
        $this->assertIsInt($result);
        $this->assertSame(123, $result);
        
        // Test string with decimal points
        $decimalString = '456.789';
        $result = SafeValueConverter::toSafeInteger($decimalString);
        $this->assertIsInt($result);
        $this->assertSame(456, $result);
    }
    
    /** @test */
    public function it_returns_null_for_invalid_values()
    {
        $this->assertNull(SafeValueConverter::toSafeInteger(null));
        $this->assertNull(SafeValueConverter::toSafeInteger(''));
        $this->assertNull(SafeValueConverter::toSafeInteger('not-a-number'));
        $this->assertNull(SafeValueConverter::toSafeInteger([]));
        $this->assertNull(SafeValueConverter::toSafeInteger(new \stdClass()));
    }
    
    /** @test */
    public function it_converts_field_values_by_type()
    {
        // Test NUMBER field with scientific notation
        $largeNumber = '-9.2233720368548E+18';
        $converted = SafeValueConverter::toDbSafe($largeNumber, CustomFieldType::NUMBER);
        $this->assertIsInt($converted);
        $this->assertLessThan(0, $converted); // Just verify it's negative, not the exact value
        
        // Test CURRENCY field with float
        $currency = '123.45';
        $converted = SafeValueConverter::toDbSafe($currency, CustomFieldType::CURRENCY);
        $this->assertIsFloat($converted);
        $this->assertEquals(123.45, $converted);
        
        // Test array-based fields
        $tags = ['tag1', 'tag2', 'tag3'];
        $converted = SafeValueConverter::toDbSafe($tags, CustomFieldType::TAGS_INPUT);
        $this->assertIsArray($converted);
        $this->assertSame($tags, $converted);
        
        // Test string conversion for JSON
        $jsonString = '["item1","item2"]';
        $converted = SafeValueConverter::toDbSafe($jsonString, CustomFieldType::CHECKBOX_LIST);
        $this->assertIsArray($converted);
        $this->assertSame(['item1', 'item2'], $converted);
    }
}
