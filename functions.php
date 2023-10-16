<?php 
function calculateRSI($data, $period = 14) {
    $rsi = [];
    $count = count($data);

    for ($i = 0; $i < $count; $i++) {
        if ($i < $period) {
            $rsi[] = null; // RSI is not defined for the first $period - 1 data points
        } else {
            $avgGain = 0;
            $avgLoss = 0;

            for ($j = $i - $period + 1; $j <= $i; $j++) {
                $priceDiff = $data[$j] - $data[$j - 1];
                if ($priceDiff > 0) {
                    $avgGain += $priceDiff;
                } else {
                    $avgLoss += abs($priceDiff);
                }
            }

            $avgGain /= $period;
            $avgLoss /= $period;

            if ($avgLoss == 0) {
                $rsi[] = 100;
            } else {
                $rs = $avgGain / $avgLoss;
                $rsi[] = 100 - (100 / (1 + $rs));
            }
        }
    }

    return $rsi;
}


function calculateEMA($data, $period) {
    $multiplier = 2 / ($period + 1);
    $ema = [];

    foreach ($data as $index => $value) {
        if ($index === 0) {
            $ema[] = $value; // The first EMA value is the same as the input value
        } else {
            $ema[] = ($value - $ema[$index - 1]) * $multiplier + $ema[$index - 1];
        }
    }

    return $ema;
}

function clop ($open, $close) {
    if ($open < $close) {
        return "GREEN";
    } else {
        return "RED";
    }
}

function rsiema ($rsi, $ema) {
    if ($rsi > $ema) {
        return "GREEN";
    } else {
        return "RED";
    }
   
}



?>