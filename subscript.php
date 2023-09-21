<?php
// Your PHP code here
ini_set('display_errors', '0');



$symbol ='QQQ';
function getAllStockData($symbol) {
    // Initialize an array to store all data
    $allData = [];

    // Set the initial date to today
    $endDate = date('Y-m-d');

    // Fetch historical data in chunks
    while (true) {
        // Calculate the start date for the current chunk (e.g., 1 year ago)
       // $startDate = date('Y-m-d', strtotime("$endDate 1 year"));
        $startDate = date('Y-m-d', strtotime("$endDate -5 day"));

        // Construct the URL with the date range
        $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval=5m&period1=" . strtotime($startDate) . "&period2=" . strtotime($endDate);

        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check if cURL request was successful
        if ($response === false) {
            return null;
        }

        // Close cURL connection
        curl_close($ch);

        // Decode the JSON response
        $data = json_decode($response, true);

        // Extract the desired data (closing price)
        if (isset($data['chart']['result'][0]['timestamp'])) {
            $timestamps = $data['chart']['result'][0]['timestamp'];
            $close = $data['chart']['result'][0]['indicators']['quote'][0]['close'];

            // Combine data into an array of arrays and prepend it to $allData
            $chunkData = [];
            for ($i = 0; $i < count($timestamps); $i++) {
                $chunkData[] = [
                    'timestamp' => $timestamps[$i],
                    'close' => $close[$i],
                ];
            }
            $allData = array_merge($chunkData, $allData);

            // Set the new end date for the next chunk
            $endDate = date('Y-m-d', strtotime("$startDate -1 day"));
        } else {
            break; // No more data available
        }
    }

    return $allData;
}

function getLiveStockData($symbol){
    
}


print_r(getAllStockData($symbol));
// Get the stock data for the symbol
$stock_data = getAllStockData($symbol);

// Print the data and capture the output
ob_start();
print_r($stock_data);
$output = ob_get_clean();

// Write the output to the file
file_put_contents("stock_data.txt", $output);



?>