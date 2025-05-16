<?php

// Simple test script to verify the validation rule behavior

require_once __DIR__ . '/../../vendor/autoload.php';

use Relaticle\CustomFields\Support\DatabaseFieldConstraints;

// Test case 1: User rule is stricter than system limit
function testUserRuleStricterThanSystemLimit()
{
    // Mock database constraints
    $dbConstraints = [
        'max' => 65535,
        'validator' => 'max',
    ];
    
    // User has a stricter max rule
    $userRules = ['max:100'];
    
    // Merge the rules
    $mergedRules = DatabaseFieldConstraints::mergeConstraintsWithRules($dbConstraints, $userRules);
    
    // The result should keep the user's stricter rule
    $passed = in_array('max:100', $mergedRules);
    
    echo "Test 1 (User rule stricter than system limit): " . ($passed ? "PASSED" : "FAILED") . "\n";
    if (!$passed) {
        echo "  Expected: max:100\n";
        echo "  Got: " . implode(', ', $mergedRules) . "\n";
    }
    
    return $passed;
}

// Test case 2: System limit applied when user rule exceeds database capability
function testSystemLimitAppliedWhenUserRuleExceeds()
{
    // Mock database constraints
    $dbConstraints = [
        'max' => 65535,
        'validator' => 'max',
    ];
    
    // User has a max rule that exceeds database capability
    $userRules = ['max:100000'];
    
    // Merge the rules
    $mergedRules = DatabaseFieldConstraints::mergeConstraintsWithRules($dbConstraints, $userRules);
    
    // The result should use the system limit
    $passed = in_array('max:65535', $mergedRules);
    
    echo "Test 2 (System limit applied when user rule exceeds): " . ($passed ? "PASSED" : "FAILED") . "\n";
    if (!$passed) {
        echo "  Expected: max:65535\n";
        echo "  Got: " . implode(', ', $mergedRules) . "\n";
    }
    
    return $passed;
}

// Run the tests
testUserRuleStricterThanSystemLimit();
testSystemLimitAppliedWhenUserRuleExceeds();
