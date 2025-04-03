<?php
function getExchangeRates() {
    $filePath = "exchangerates.csv"; // Path to your CSV file
    $file = fopen($filePath, "r");

    $rates = [];
    $isFirstRow = true; // Flag to skip the header row

    while (($row = fgetcsv($file)) !== false) {
        if ($isFirstRow) {
            $isFirstRow = false;
            continue; // Skip the header row
        }
        $rates[$row[0]] = (float) $row[1]; // Convert first column (Currency) and second column (Rate)
    }

    fclose($file);
    echo json_encode(["conversion_rates" => $rates]);
}

getExchangeRates();
?>
