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

    $openrsi = calculateRSI($opens, 14);
    $openema = calculateEMA($openrsi, 14);
    $openrsiema = rsiema($openrsi[$i], $openema[$i]);
    

    


    

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

    //Long Position Entry
    if ($rsiema == "GREEN" and $clop == "GREEN"){
        if ($x == 0){
            $x = 1;
            $buy = $closes[$i + 0];
            $buydate = $date;
            $totalbuys = $totalbuys + 1;
            echo "<tr><td colspan='9'>BUY AT CLOSE PRICE: $buy, $date</td></tr>";
        } elseif ($x == 1){
            $x = 0;
            if($open < $buy * (1/$stopLoss)){ //if open is less than stop loss, sell at open
                $sell = $open;
                echo "<tr><td colspan='9'>STOP LOSS HIT: $sell, $date</td></tr>";
            }elseif ($low < $buy * (1/$stopLoss)){
                $sell = $buy * (1/$stopLoss);
            }else {
                $sell = $close;
                echo "<tr><td colspan='9'>SELL AT CLOSE PRICE: $sell, $date</td></tr>";
            }
            $selldate = $date;
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
            $totalLongProfit = $totalLongProfit + $profit;
            echo "<tr><td colspan='9'>BUY: $buydate SELL: $selldate PROFIT: $profit PERCENT: $percent MAX: $maxProfit MIN: $maxLoss</td></tr>";
        }
    } elseif ($openrsiema == "RED"  or $clop == "RED"){
        if ($x == 1){
            $x = 0;
            if ($open < $buy * (1/$stopLoss)){
                $sell = $open;
                echo "<tr><td colspan='9'>STOP LOSS HIT RED: $sell, $date</td></tr>";
                
            
            }else {
                $sell = $close;
                echo "<tr><td colspan='9'>SELL AT CLOSE PRICE: $sell, $date</td></tr>";
            }            
            $selldate = $date;
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
            $totalLongProfit = $totalLongProfit + $profit;

            $maxProfit = ($high - $buy)/$buy * 100;
            $maxLoss = ($low - $buy)/$buy * 100;
            $percent = $profit / $buy * 100;
            echo "<tr><td colspan='9'>BUY: $buydate SELL: $selldate PROFIT: $profit PERCENT: $percent MAX: $maxProfit MIN: $maxLoss</td></tr>";
        } else {
            $x = 0;
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
