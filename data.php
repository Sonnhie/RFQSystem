<?php
/**
 * API endpoint for dashboard data
 * This file handles data requests for the dashboard
 */

// Set content type to JSON
header('Content-Type: application/json');

// Allow cross-origin requests (if needed)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request parameters
$type = isset($_GET['type']) ? $_GET['type'] : '';
$timeRange = isset($_GET['timeRange']) ? $_GET['timeRange'] : '24h';
$baseCurrency = isset($_GET['baseCurrency']) ? $_GET['baseCurrency'] : 'USD';

// Process request based on type
switch ($type) {
    case 'status':
        echo json_encode(getStatusData($timeRange));
        break;
    case 'exchange':
        echo json_encode(getExchangeRateData($baseCurrency, $timeRange));
        break;
    case 'summary':
        echo json_encode(getSummaryData());
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request type']);
        break;
}

/**
 * Get status data based on time range
 * 
 * @param string $timeRange Time range (1h, 24h, 7d, 30d, 90d)
 * @return array Status data
 */
function getStatusData($timeRange) {
    // In a real app, this would fetch data from a database
    // For now, we'll return mock data
    
    // Adjust data based on time range
    switch ($timeRange) {
        case '1h':
            return [
                'success' => 23,
                'error' => 4,
                'pending' => 8,
                'timeRange' => $timeRange,
                'timestamp' => time()
            ];
        case '7d':
            return [
                'success' => 412,
                'error' => 87,
                'pending' => 103,
                'timeRange' => $timeRange,
                'timestamp' => time()
            ];
        case '30d':
            return [
                'success' => 1845,
                'error' => 342,
                'pending' => 467,
                'timeRange' => $timeRange,
                'timestamp' => time()
            ];
        case '90d':
            return [
                'success' => 5234,
                'error' => 978,
                'pending' => 1245,
                'timeRange' => $timeRange,
                'timestamp' => time()
            ];
        case '24h':
        default:
            return [
                'success' => 67,
                'error' => 12,
                'pending' => 23,
                'timeRange' => $timeRange,
                'timestamp' => time()
            ];
    }
}

/**
 * Get exchange rate data based on base currency and time range
 * 
 * @param string $baseCurrency Base currency code
 * @param string $timeRange Time range (1h, 24h, 7d, 30d, 90d)
 * @return array Exchange rate data
 */
function getExchangeRateData($baseCurrency, $timeRange) {
    // Base exchange rates (against USD)
    $baseRates = [
        'USD' => 1.0,
        'EUR' => 0.92,
        'GBP' => 0.78,
        'JPY' => 151.67,
        'CAD' => 1.36,
        'AUD' => 1.51,
        'CHF' => 0.9,
        'CNY' => 7.23
    ];
    
    // Currency names
    $currencyNames = [
        'USD' => 'US Dollar',
        'EUR' => 'Euro',
        'GBP' => 'British Pound',
        'JPY' => 'Japanese Yen',
        'CAD' => 'Canadian Dollar',
        'AUD' => 'Australian Dollar',
        'CHF' => 'Swiss Franc',
        'CNY' => 'Chinese Yuan'
    ];
    
    // Calculate rates based on selected base currency
    $rates = [];
    $baseValue = $baseRates[$baseCurrency];
    
    foreach ($baseRates as $code => $rate) {
        // Skip base currency
        if ($code === $baseCurrency) continue;
        
        // Calculate rate against selected base currency
        $convertedRate = $rate / $baseValue;
        
        // Generate random change based on time range
        $changeMultiplier = 0.01; // Default for 24h
        
        switch ($timeRange) {
            case '1h':
                $changeMultiplier = 0.002;
                break;
            case '7d':
                $changeMultiplier = 0.02;
                break;
            case '30d':
                $changeMultiplier = 0.05;
                break;
            case '90d':
                $changeMultiplier = 0.08;
                break;
        }
        
        $change = (mt_rand(-100, 100) / 1000) * $changeMultiplier;
        
        // Add to rates array
        $rates[] = [
            'currency' => $currencyNames[$code],
            'rate' => $convertedRate,
            'change' => $change,
            'code' => $code
        ];
    }
    
    return [
        'baseCurrency' => $baseCurrency,
        'baseCurrencyName' => $currencyNames[$baseCurrency],
        'timeRange' => $timeRange,
        'rates' => $rates,
        'timestamp' => time()
    ];
}

/**
 * Get summary data for the dashboard
 * 
 * @return array Summary data
 */
function getSummaryData() {
    // Get status data for 24h
    $statusData = getStatusData('24h');
    
    // Calculate success rate
    $total = $statusData['success'] + $statusData['error'] + $statusData['pending'];
    $successRate = round(($statusData['success'] / $total) * 100);
    
    // Determine exchange rate volatility
    $volatility = 'Low'; // Default
    
    // In a real app, this would be calculated based on actual data
    $randomValue = mt_rand(0, 100);
    if ($randomValue > 80) {
        $volatility = 'High';
    } elseif ($randomValue > 50) {
        $volatility = 'Medium';
    }
    
    return [
        'successRate' => $successRate,
        'totalRequests' => $total,
        'exchangeRateVolatility' => $volatility,
        'timestamp' => time()
    ];
}
