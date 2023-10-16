<?php
include 'functions.php';


$symbol = "QQQ"; // Symbol for the QQQ ETF
$range = "1y";  // Data range for one year

// Create the Yahoo Finance URL
$url = "https://query1.finance.yahoo.com/v8/finance/chart/$symbol?interval=1d&range=$range";

// Fetch data from Yahoo Finance
$data = file_get_contents($url);
$data = json_decode($data, true);

// Extract historical price data
$timestamps = $data['chart']['result'][0]['timestamp'];
$opens = $data['chart']['result'][0]['indicators']['quote'][0]['open'];
$highs = $data['chart']['result'][0]['indicators']['quote'][0]['high'];
$lows = $data['chart']['result'][0]['indicators']['quote'][0]['low'];
$closes = $data['chart']['result'][0]['indicators']['quote'][0]['close'];




echo "<table border='1'>";
echo "<tr><th>Date</th><th>Open</th><th>High</th><th>Low</th><th>Close</th><th>RSI</th><th>EMA</th><th>CLOP</th><th>RSIEMA</th></tr>";

for ($i = 0; $i < count($timestamps); $i++) {
    $timestamp = $timestamps[$i];
    $date = date("Y-m-d", $timestamp);
    $open = $opens[$i];
    $high = $highs[$i];
    $low = $lows[$i];
    $close = $closes[$i];
    $rsi = calculateRSI($closes, 14);
    $ema = calculateEMA($rsi, 14);
    $clop = clop($open, $close);
    $rsiema = rsiema($rsi, $ema);

    echo "<tr>";
    echo "<td>$date</td>";
    echo "<td>$open</td>";
    echo "<td>$high</td>";
    echo "<td>$low</td>";
    echo "<td>$close</td>";
    echo "<td>$rsi[$i]</td>";
    echo "<td>$ema[$i]</td>";
    echo "<td>$clop</td>";
    echo "<td>$rsiema</td>";
    echo "</tr>";
}

echo "</table>";
?>
