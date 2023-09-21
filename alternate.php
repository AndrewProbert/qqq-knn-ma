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

foreach ($intervals as $interval) {
    $data = fetchYahooFinanceData($symbol, $interval, $range);
    
    if ($data) {
        $jsonData = json_decode($data, true);
        
        if (isset($jsonData["chart"]["result"][0]["timestamp"])) {
            $timestamps = $jsonData["chart"]["result"][0]["timestamp"];
            $candleData = $jsonData["chart"]["result"][0]["indicators"]["quote"][0];
            
            // Loop through the timestamps and extract the data
            for ($i = 0; $i < count($timestamps); $i++) {
                $timestamp = $timestamps[$i];
                $price = $candleData["close"][$i];
                $volume = $candleData["volume"][$i];
                $date = date("Y-m-d H:i:s", $timestamp);
                
                $resultArray[] = [
                    "Interval" => $interval,
                    "Date" => $date,
                    "Price" => $price,
                    "Volume" => $volume,
                ];
            }
        }
    }
}




// Print the resulting array
//print_r($resultArray);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candlestick Chart</title>
    <script src="https://code.highcharts.com/highcharts.js"></script>
</head>
<body>
    <div id="candlestick-chart" style="height: 400px; width: 800px;"></div>
    
    <script src="https://code.highcharts.com/modules/stock.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    
    <script>
        // PHP array containing the candlestick data
        var candlestickData = <?php echo json_encode($resultArray); ?>;
        
        // Create an array for Highcharts
        var ohlcData = [];
        var volumeData = [];
        
        for (var i = 0; i < candlestickData.length; i++) {
            var date = new Date(candlestickData[i].Date).getTime();
            var price = parseFloat(candlestickData[i].Price);
            var volume = parseInt(candlestickData[i].Volume);
            
            ohlcData.push([date, candlestickData[i].Open, candlestickData[i].High, candlestickData[i].Low, price]);
            
            volumeData.push([date, volume]);
        }
        
        Highcharts.stockChart('candlestick-chart', {
            rangeSelector: {
                selected: 1
            },
            
            title: {
                text: 'Candlestick Chart'
            },
            
            yAxis: [{
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'OHLC'
                },
                height: '60%',
                lineWidth: 2
            }, {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Volume'
                },
                top: '65%',
                height: '35%',
                offset: 0,
                lineWidth: 2
            }],
            
            series: [{
                type: 'candlestick',
                name: 'Candlestick',
                data: ohlcData,
                dataGrouping: {
                    units: [['day', [1]]]
                }
            }, {
                type: 'column',
                name: 'Volume',
                data: volumeData,
                yAxis: 1,
                dataGrouping: {
                    units: [['day', [1]]]
                }
            }]
        });
    </script>
</body>
</html>
