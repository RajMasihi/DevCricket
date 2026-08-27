<?php

/**
 * Test script for over threshold functionality
 * This tests the over parsing and threshold logic
 */

require_once __DIR__ . '/vendor/autoload.php';

// Mock the over parsing logic
function parseOverValue(string $overString): float
{
    if (empty($overString) || $overString === '--') {
        return 0.0;
    }
    
    // Handle formats like "6.2", "6.3", "6.4", "15.1", etc.
    $parts = explode('.', $overString);
    if (count($parts) === 2) {
        $overs = (float)$parts[0];
        $balls = (float)$parts[1];
        // Convert balls to decimal (e.g., 6.2 = 6.33, 6.3 = 6.5, 6.4 = 6.67)
        return $overs + ($balls / 6);
    }
    
    return (float)$overString;
}

function calculateThresholdLevel(float $maxOvers): string
{
    // Thresholds based on decimal over values:
    // 6.2 overs = 6.33 decimal, 6.3 overs = 6.5 decimal, 6.4 overs = 6.67 decimal
    // Using >= for lower bounds to properly categorize exact threshold values
    if ($maxOvers >= 6.67) {
        return 'critical'; // 6.4+ overs (6.67 decimal)
    } elseif ($maxOvers >= 6.5) {
        return 'high';     // 6.3+ overs (6.5 decimal)
    } elseif ($maxOvers >= 6.33) {
        return 'medium';   // 6.2+ overs (6.33 decimal)
    }
    
    return 'normal';
}

// Test cases
$testCases = [
    '5.0' => ['expected' => 5.0, 'threshold' => 'normal'],
    '6.0' => ['expected' => 6.0, 'threshold' => 'normal'],
    '6.1' => ['expected' => 6.17, 'threshold' => 'normal'],
    '6.2' => ['expected' => 6.33, 'threshold' => 'medium'], // 6.2 overs = 6.33 decimal (> 6.17)
    '6.3' => ['expected' => 6.5, 'threshold' => 'high'],     // 6.3 overs = 6.5 decimal (> 6.33)
    '6.4' => ['expected' => 6.67, 'threshold' => 'critical'], // 6.4 overs = 6.67 decimal (> 6.5)
    '6.5' => ['expected' => 6.83, 'threshold' => 'critical'],
    '10.0' => ['expected' => 10.0, 'threshold' => 'critical'],
    '15.3' => ['expected' => 15.5, 'threshold' => 'critical'],
    '--' => ['expected' => 0.0, 'threshold' => 'normal'],
];

echo "Over Threshold Test Results:\n";
echo "============================\n\n";

$passed = 0;
$failed = 0;

foreach ($testCases as $input => $expected) {
    $parsed = parseOverValue($input);
    $threshold = calculateThresholdLevel($parsed);
    
    // Allow small floating point differences
    $diff = abs($parsed - $expected['expected']);
    $parsePassed = $diff < 0.01;
    $thresholdPassed = $threshold === $expected['threshold'];
    
    if ($parsePassed && $thresholdPassed) {
        $passed++;
        echo "✓ PASS: '{$input}' -> {$parsed} (threshold: {$threshold})\n";
    } else {
        $failed++;
        echo "✗ FAIL: '{$input}' -> Expected: {$expected['expected']}, Got: {$parsed} | Expected threshold: {$expected['threshold']}, Got: {$threshold}\n";
    }
}

echo "\n============================\n";
echo "Results: {$passed} passed, {$failed} failed\n";

if ($failed === 0) {
    echo "All tests passed! ✓\n";
    exit(0);
} else {
    echo "Some tests failed! ✗\n";
    exit(1);
}