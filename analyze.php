<?php

// Function to fetch Yahoo Finance data
function fetchData($symbol, $interval)
{
    // Define the Yahoo Finance URL with the symbol and interval
    $url = "https://query1.finance.yahoo.com/v7/finance/chart/$symbol?interval=$interval";

    // Fetch data from Yahoo Finance
    $data = file_get_contents($url);

    // Convert JSON data to PHP array
    $result = json_decode($data, true);

    return $result;
}

// Function to calculate KNN (K-Nearest Neighbors)
function calculateKNN($data, $k)
{
    // Implement your KNN algorithm here using the data array

    // Example: Find KNN based on closing prices
    $closePrices = $data['chart']['result'][0]['indicators']['quote'][0]['close'];
    $latestClosePrice = end($closePrices);

    // Calculate KNN based on your algorithm

    return $knnValue;
}

// Function to calculate 3-day EMA
function calculateEMA($data)
{
    // Implement your EMA calculation here using the data array

    // Example: Calculate 3-day EMA based on closing prices
    $closePrices = $data['chart']['result'][0]['indicators']['quote'][0]['close'];

    // Calculate EMA based on your algorithm

    return $emaValue;
}

// Main program
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $symbol = $_POST['symbol'];
    $interval = $_POST['interval'];
    $k = $_POST['k'];

    // Fetch data from Yahoo Finance
    $data = fetchData($symbol, $interval);

    // Calculate KNN
    $knnValue = calculateKNN($data, $k);

    // Calculate 3-day EMA
    $emaValue = calculateEMA($data);

    // Display results
    echo "Symbol: $symbol<br>";
    echo "Interval: $interval<br>";
    echo "KNN Value: $knnValue<br>";
    echo "3-Day EMA Value: $emaValue<br>";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Yahoo Finance Data</title>
</head>
<body>
    <h1>Yahoo Finance Data</h1>
    <form method="POST" action="">
        <label for="symbol">Symbol (e.g., AAPL):</label>
        <input type="text" name="symbol" required><br><br>

        <label for="interval">Interval:</label>
        <select name="interval" required>
            <option value="1m">1 Minute</option>
            <option value="2m">2 Minutes</option>
            <option value="5m">5 Minutes</option>
            <option value="30m">30 Minutes</option>
            <option value="1h">1 Hour</option>
        </select><br><br>

        <label for="k">K Value for KNN:</label>
        <input type="number" name="k" required><br><br>

        <input type="submit" value="Get Data">
    </form>
</body>
</html>
