<?php
// Your PHP code here
ini_set('display_errors', '0');



$symbol ='QQQ';

//10-02 sso (spy 2x), 
// xle (energy), kre (banking),
//msft is good for 1 to 2 day trades, adbe, tm, gs (1 day), lmt (1 day), pdd 
function getYahooFinanceCloseData() {
    // Specify the URL of the Yahoo Finance API
    $url = 'https://query1.finance.yahoo.com/v8/finance/chart/adbe?interval=1d&range=3000d';

    //qqq, tlt, soxl, ewz


    // Fetch the JSON data from the URL
    $jsonData = file_get_contents($url);

    // Check if data retrieval was successful
    if ($jsonData === false) {
        return false; // Return false if there was an error fetching data
    }

    // Decode the JSON data
    $data = json_decode($jsonData, true);

    // Check if the JSON decoding was successful
    if ($data === null || !isset($data['chart']['result'][0]['indicators']['quote'][0]['close'])) {
        return false; // Return false if JSON decoding or data structure is not as expected
    }

    // Extract the close prices
    $closePrices = $data['chart']['result'][0]['indicators']['quote'][0]['close'];

    return $closePrices;
}

// Usage example
$closeData = getYahooFinanceCloseData();

if ($closeData !== false) {
    foreach ($closeData as $closePrice) {
        echo $closePrice . PHP_EOL;
    }
} else {
    echo "Failed to retrieve data from Yahoo Finance API." . PHP_EOL;
}




//print_r(getYahooFinanceCloseData());
// Get the stock data for the symbol
$stock_data = getYahooFinanceCloseData();

// Print the data and capture the output
ob_start();
//print_r($stock_data);
$output = ob_get_clean();
//echo $output;
// Write the output to the file
file_put_contents("stock_data.txt", $output);



?>