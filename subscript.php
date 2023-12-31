<?php
// Your PHP code here
ini_set('display_errors', '0');


$symbol ='qqq';
$range = '100d';

/*
$filename = "symbols.txt";
$symbol = file_get_contents($filename);

// Remove all spaces (including spaces, tabs, and line breaks)
$symbol = preg_replace('/\s+/', '', $symbol);
*/


function getYahooFinanceCloseData($symbol, $range) {
    // Specify the URL of the Yahoo Finance API
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/$symbol?interval=1d&range=$range";


    /* 1 Day Trades
    ETF: SPY, QQQ, DIA, TQQQ, USO

    EQUITIES: MSFT, GOOGL, GS, JPM, PDD, LLY, IBM

    OTHER: AAPL, NFLX, ADBE, TM, MCD, ASML

    */

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
$closeData = getYahooFinanceCloseData($symbol, $range);

if ($closeData !== false) {
    foreach ($closeData as $closePrice) {
        echo $closePrice . PHP_EOL;
    }
} else {
    echo "Failed to retrieve data from Yahoo Finance API." . PHP_EOL;
}




//print_r(getYahooFinanceCloseData());
// Get the stock data for the symbol
$stock_data = getYahooFinanceCloseData($symbol, $range);

// Print the data and capture the output
ob_start();
//print_r($stock_data);
$output = ob_get_clean();
//echo $output;
// Write the output to the file
file_put_contents("stock_data.txt", $output);



?>