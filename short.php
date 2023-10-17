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
$largestProfit = 0;
$largestLoss = 0;
$tradeArray = array();

$y = 0;
$totalsells = 0;
$totalShortProfit = 0;
$totalLongProfit = 0;



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

    $stopLoss = 65;  //in basis points
    $stopLoss = ($stopLoss / 10000) + 1;

    //Short Position Entry
    if ($rsiema == "RED" and $clop == "RED"){
        if ($y == 0){
            $y = 1;
            $sell = $closes[$i + 0];
            $selldate = $date;
            $totalsells = $totalsells + 1;
            echo "<tr><td colspan='9'>SELL AT CLOSE PRICE: $sell, $date</td></tr>";
        } elseif ($y == 1){
            $y = 0;
            if($open > $sell * (1/$stopLoss)){ //if open is greater than stop loss, buy at open
                $buy = $open;
                echo "<tr><td colspan='9'>STOP LOSS HIT: $buy, $date</td></tr>";
            }elseif ($high > $sell * (1/$stopLoss)){
                $buy = $sell * (1/$stopLoss);
            }else {
                $buy = $close;
                echo "<tr><td colspan='9'>BUY AT CLOSE PRICE: $buy, $date</td></tr>";
            }
            $buydate = $date;
            $profit = $sell - $buy;
            $tradeArray[] = $profit;
            if ($profit > 0){
                $totalbuywins = $totalbuywins + 1;
            }


            if ($profit > $largestProfit){
                $largestProfit = $profit;
            }

            if ($profit < $largestLoss){
                $largestLoss = $profit;
            }

            $maxProfit = ($high - $buy)/$buy * 100;
            $maxLoss = ($low - $buy)/$buy * 100;
            $percent = $profit / $buy * 100;
            $totalprofit = $totalprofit + $profit;
            $totalShortProfit = $totalShortProfit + $profit;

            echo "<tr><td colspan='9'>SELL: $selldate BUY: $buydate PROFIT: $profit PERCENT: $percent MAX: $maxProfit MIN: $maxLoss</td></tr>";

            
        }

    } elseif ($rsiema == "GREEN" or $clop == "GREEN"){
        if ($y == 1){
            $y = 0;
            if ($high > $sell * (1/$stopLoss)){
                $buy = $open;
                echo "<tr><td colspan='9'>STOP LOSS HIT GREEN: $buy, $date</td></tr>";
            }else {
                $buy = $close;
                echo "<tr><td colspan='9'>BUY AT CLOSE PRICE: $buy, $date</td></tr>";
            }            
            $buydate = $date;
            $profit = $sell - $buy;
            $tradeArray[] = $profit;

            if ($profit > 0){
                $totalbuywins = $totalbuywins + 1;
            }

            if ($profit > $largestProfit){
                $largestProfit = $profit;
            }
            if ($profit < $largestLoss){
                $largestLoss = $profit;
            }

            $totalprofit = $totalprofit + $profit;
            $totalShortProfit = $totalShortProfit + $profit;

            $maxProfit = ($high - $buy)/$buy * 100;
            $maxLoss = ($low - $buy)/$buy * 100;
            $percent = $profit / $buy * 100;
            echo "<tr><td colspan='9'>SELL: $selldate BUY: $buydate PROFIT: $profit PERCENT: $percent MAX: $maxProfit MIN: $maxLoss</td></tr>";
        } else {
            $y = 0;
        }
    }




}

sort($tradeArray);
$median = $tradeArray[count($tradeArray)/2];

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
echo "<br><br>";
echo "Largest Profit: $largestProfit";
echo "<br><br>";
echo "Largest Loss: $largestLoss";
echo "<br><br>";
echo "Median: $median";
?>
