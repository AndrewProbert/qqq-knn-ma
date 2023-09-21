<?php

function fetchYahooFinanceData($symbol, $interval, $range)
{
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval={$interval}&range={$range}";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

$symbol = "AAPL"; // Replace with the symbol of the stock you want to fetch
$intervals = ["1m", "2m", "15m", "30m", "1h", "1d"];
$range = "30d"; // You can adjust the range as needed

$resultArray = []; // Initialize an array to store the data


// Define K value for KNN-MA
$kValue = 10; // You can adjust this as needed

foreach ($intervals as $interval) {
    $data = fetchYahooFinanceData($symbol, $interval, $range);
    
    if ($data) {
        $jsonData = json_decode($data, true);
        
        if (isset($jsonData["chart"]["result"][0]["timestamp"])) {
            $timestamps = $jsonData["chart"]["result"][0]["timestamp"];
            $candleData = $jsonData["chart"]["result"][0]["indicators"]["quote"][0];
            
            // Extract closing prices
            $closingPrices = $candleData["close"];
            
            // Apply KNN-MA to closing prices
            $maValues = knnMovingAverage($closingPrices, $kValue);
            
            // Loop through the timestamps and extract the data
            for ($i = $kValue; $i < count($timestamps); $i++) {
                $timestamp = $timestamps[$i];
                $price = $candleData["close"][$i];
                $volume = $candleData["volume"][$i];
                $date = date("Y-m-d H:i:s", $timestamp);
                $ma = $maValues[$i - $kValue]; // Corresponding KNN-MA value
                $resultArray[] = [
                    "Interval" => $interval,
                    "Date" => $date,
                    "Price" => $price,
                    "Volume" => $volume,
                    "KNN_MA" => $ma, // Include KNN-MA in the result
                ];
            }
        }
    }
}


function knnMovingAverage($data, $k)
{
    $numDataPoints = count($data);
    $maValues = [];

    for ($i = $k; $i < $numDataPoints; $i++) {
        // Calculate the moving average for each data point
        $sum = 0;
        for ($j = $i - $k; $j < $i; $j++) {
            $sum += $data[$j];
        }
        $ma = $sum / $k;
        $maValues[] = $ma;
    }

    return $maValues;
}

print_r($resultArray);
?>

