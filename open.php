<?php
include 'functions.php';


$symbol = "qqq"; // Symbol for the QQQ ETF
$range = "100d";  // Data range for one year

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

$x = 0;
$totalbuys = 0;
$totalbuywins = 0;
$totalprofit = 0;





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
    $rsiema = rsiema($rsi[$i], $ema[$i]);


    

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

    if ($rsiema == "GREEN" and $clop == "GREEN"){
        if ($x == 0){
            $x = 1;
            $buy = $close;
            $buydate = $date;
            $totalbuys = $totalbuys + 1;
        } elseif ($x == 1){
            $x = 0;

            if ($open < $close * (1/1.005)){ // Sell if open is less than 0.3% above close
                $sell = $open;
            }elseif ($low < $close * (1/1.005)){ // Sell if low is less than 0.3% above close
                $sell = $close * (1/1.005);
            }elseif ($high > $close * 1.01){ // Sell if high is greater than 0.75% above close
                $sell = $close * 1.01;
            }else{ // Sell at close
                $sell = $close;
            }

            $selldate = $date;
            $profit = $sell - $buy;
            if ($profit > 0){
                $totalbuywins = $totalbuywins + 1;
            }
            $percent = $profit / $buy * 100;
            $totalprofit = $totalprofit + $profit;
            echo "<tr><td colspan='9'>BUY: $buydate SELL: $selldate PROFIT: $profit PERCENT: $percent</td></tr>";
        }
    } elseif ($rsiema == "RED"  or $clop == "RED"){
        if ($x == 1){
            $x = 0;
            if ($open < $close * (1/1.005)){ // Sell if open is less than 0.3% above close
                $sell = $open;
            }elseif ($low < $close * (1/1.005)){ // Sell if low is less than 0.3% above close
                $sell = $close * (1/1.005);
            }elseif ($high > $close * 1.01){ // Sell if high is greater than 0.75% above close
                $sell = $close * 1.01;
            }else{ // Sell at close
                $sell = $close;
            }
            $selldate = $date;
            $profit = $sell - $buy;
            if ($profit > 0){
                $totalbuywins = $totalbuywins + 1;
            }
            $totalprofit = $totalprofit + $profit;
            $percent = $profit / $buy * 100;
            echo "<tr><td colspan='9'>BUY: $buydate SELL: $selldate PROFIT: $profit PERCENT: $percent</td></tr>";
        } else {
            $x = 0;
        }
    }
}

echo "</table>";
echo "<br><br>";
echo "Total Profit: $totalprofit";
echo "<br><br>";
echo "Total Buys: $totalbuys";
echo "<br><br>";
echo "Total Buy Wins: $totalbuywins";
echo "<br><br>";
echo "Total Buy Losses: " . ($totalbuys - $totalbuywins);
echo "<br><br>";
echo "Percent Buy Wins: " . $totalbuywins / $totalbuys * 100;
?>
