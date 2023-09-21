import yfinance as yf
import numpy as np
import matplotlib.pyplot as plt
import mplfinance as mpf
from datetime import date, timedelta, datetime
import pandas_datareader as web
import yfinance as yf
import pandas_datareader as pdr
import requests
import json
from bs4 import BeautifulSoup
import requests
from selenium import webdriver
from selenium.webdriver.common.by import By
import time
import subprocess

def fetch_price_data(symbol, start_date, end_date, interval):
    # Fetch historical price data from Yahoo Finance with the specified interval
    df = yf.download(symbol, start=start_date, end=end_date, interval=interval)
    return df['Adj Close'].values



php_output = subprocess.check_output(["php", "subscript.php"], universal_newlines=True)


# Process the PHP output or do any other necessary operations
# For example, you can extract a value from the PHP output
value_from_php = (php_output.strip())


# Use the value or perform further actions
print("\n")
print("Value from PHP:", value_from_php)
print("\n")


def calculate_knn_ma(price_values, ma_len):
    knn_ma = [np.mean(price_values[i-ma_len:i]) for i in range(ma_len, len(price_values))]
    return knn_ma

def calculate_ema(price_values, ema_len):
    ema = np.zeros(len(price_values))
    ema[ema_len-1] = np.mean(price_values[:ema_len])
    multiplier = 2 / (ema_len + 1)
    
    for i in range(ema_len, len(price_values)):
        ema[i] = (price_values[i] - ema[i-1]) * multiplier + ema[i-1]

    

    return ema




def calculate_knn_prediction(price_values, ma_len, num_closest_values=3, smoothing_period=50):
    def mean_of_k_closest(value, target, num_closest):
        closest_values = []
        for i in range(len(value)):
            distances = [abs(target[i] - v) for v in closest_values]
            if len(distances) < num_closest or min(distances) < min(distances):
                closest_values.append(value[i])
            if len(distances) >= num_closest:
                max_dist_index = distances.index(max(distances))
                if distances[max_dist_index] > min(distances):
                    closest_values[max_dist_index] = value[i]
        return sum(closest_values) / len(closest_values)

    knn_ma = [mean_of_k_closest(price_values[i-ma_len:i], price_values[i-ma_len:i], num_closest_values)
              for i in range(ma_len, len(price_values))]

    if len(knn_ma) < smoothing_period:
        return []

    knn_smoothed = np.convolve(knn_ma, np.ones(smoothing_period) / smoothing_period, mode='valid')

    def knn_prediction(price, knn_ma, knn_smoothed):
        pos_count = 0
        neg_count = 0
        min_distance = 1e10
        nearest_index = 0
        
        # Check if there are enough elements in knn_ma and knn_smoothed
        if len(knn_ma) < 2 or len(knn_smoothed) < 2:
            return 0  # Return 0 for neutral if there aren't enough elements
        
        for j in range(1, min(10, len(knn_ma))):
            distance = np.sqrt((knn_ma[j] - price) ** 2)
            if distance < min_distance:
                min_distance = distance
                nearest_index = j
                
                # Check if there are enough elements to compare
                if nearest_index >= 1:
                    if knn_smoothed[nearest_index] > knn_smoothed[nearest_index - 1]:
                        pos_count += 1
                    if knn_smoothed[nearest_index] < knn_smoothed[nearest_index - 1]:
                        neg_count += 1
        
        return 1 if pos_count > neg_count else -1

    knn_predictions = [knn_prediction(price_values[i], knn_ma[i - smoothing_period:i], knn_smoothed[i - smoothing_period:i])
                       for i in range(smoothing_period, len(price_values))]

    return knn_predictions








# Example usage:
symbol = 'QQQ'  # Replace with the stock symbol you want to fetch data for

# Get the current date
current_date = date.today()

# Specify the chart interval (1m, 2m, 5m, 15m, 30m, 1h, 1d)
chart_interval = '15m'  # Change this to the desired interval

if chart_interval == '1m':
    # Include the current day's data
    start_date = current_date - timedelta(days=7)
    end_date = current_date
elif chart_interval == '2m' or chart_interval == '5m' or chart_interval == '15m' or chart_interval == '30m':
    # Include the current day's data
    start_date = current_date - timedelta(days=55)
    end_date = current_date
elif chart_interval == '1h':
    # Include the current day's data
    start_date = current_date - timedelta(days=730)
    end_date = current_date
else:
    print('Invalid chart interval entered. Defaulting to 1 day.')
    chart_interval = '1d'
    # Include the current day's data
    start_date = current_date - timedelta(days=5000)
    end_date = current_date

# Fetch price data with the specified interval
price_data = fetch_price_data(symbol, start_date, end_date, chart_interval)

# Calculate KNN moving average with a specified MA length
ma_len = 5
knn_ma = calculate_knn_ma(price_data, ma_len)

# Calculate 5-period Exponential Moving Average (EMA)
ema_len_5 = 5
ema_5 = calculate_ema(price_data, ema_len_5)

# Calculate 9-day Exponential Moving Average (EMA)
ema_len_9 = 9
ema_9 = calculate_ema(price_data, ema_len_9)

# Create a time array for x-axis
time = np.arange(len(price_data))

# Find the index where EMA first becomes non-zero
ema_start_index_5 = np.argmax(ema_5 != 0)
ema_start_index_9 = np.argmax(ema_9 != 0)

# Plot the chart, KNN MA, 5-day EMA, and 9-day EMA on the same graph
plt.figure(figsize=(12, 6))
plt.plot(time, price_data, label=f'{chart_interval} Chart', color='blue')
plt.plot(time[ma_len:], knn_ma, label=f'KNN MA ({ma_len}-Period)', color='orange')
if ema_start_index_5 > 0:
    plt.plot(time[ema_start_index_5:], ema_5[ema_start_index_5:], label=f'5-Day EMA', color='green')
#if ema_start_index_9 > 0:
#    plt.plot(time[ema_start_index_9:], ema_9[ema_start_index_9:], label=f'9-Day EMA', color='purple')
plt.xlabel('Time')
plt.ylabel('Price')
plt.title(f'{symbol} {chart_interval} Chart with KNN MA and EMA')
plt.legend()
plt.grid(True)
plt.show()

#Instead of just using a ema as a stop loss we should use the rate of the how the slope value is decreasing in volatility.