<?php
header("Content-Type: application/json");

function getExchangeRates() {
    $filePath = "exchangerates.csv"; // Path to CSV file
    if (!file_exists($filePath)) {
        echo json_encode(["result" => "error", "message" => "CSV file not found"]);
        exit;
    }

    $file = fopen($filePath, "r");
    if (!$file) {
        echo json_encode(["result" => "error", "message" => "Failed to open CSV file"]);
        exit;
    }

    $rates = [];
    $isFirstRow = true; // Skip header row

    while (($row = fgetcsv($file)) !== false) {
        if ($isFirstRow) {
            $isFirstRow = false;
            continue; // Skip header
        }
        if (isset($row[0], $row[1])) {
            $rates[$row[0]] = (float) $row[1]; // Convert currency (row[0]) and rate (row[1])
        }
    }

    fclose($file);

    echo json_encode([
        "result" => "success",
        "conversion_rates" => $rates
    ]);
}

getExchangeRates();
?>
