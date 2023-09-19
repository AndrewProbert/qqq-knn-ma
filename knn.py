import yfinance as yf
import numpy as np
import matplotlib.pyplot as plt
import mplfinance as mpf
import pandas as pd


def fetch_price_data(symbol, start_date, end_date, timeframe):
    # Fetch historical price data from Yahoo Finance with the specified timeframe
    df = yf.download(symbol, start=start_date, end=end_date, interval=timeframe)
    return df['Adj Close'].values

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
            if j > nearest_index:
                continue  # Skip if j is greater than nearest_index
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

    knn_predictions = [knn_prediction(price_values[i], knn_ma, knn_smoothed)  # Removed indexing
                       for i in range(smoothing_period, len(price_values))]

    return knn_predictions

# Example usage:
symbol = 'QQQ'  # Replace with the stock symbol you want to fetch data for
start_date = '2023-08-01'  # Replace with your desired start date
end_date = '2023-09-16'  # Replace with your desired end date

# Select the desired timeframe (1m, 5m, 15m, 30m, 45m, 1h, 2h, 4h, 1d)
timeframe = '15m'  # Replace with the desired timeframe

# Fetch candlestick data
candle_data = fetch_price_data(symbol, start_date, end_date, timeframe)

# Adjust the parameters as needed
ma_len = 5
num_closest_values = 3
smoothing_period = 50

# Use the 'Adj Close' column as a Pandas Series
price_data = candle_data['Adj Close']

predictions = calculate_knn_prediction(price_data, ma_len, num_closest_values, smoothing_period)

# Calculate 5-period Exponential Moving Average (EMA)
ema_len = 5
ema = calculate_ema(price_data, ema_len)

# Create a time array for x-axis
time = np.arange(len(price_data))

# Find the index where EMA first becomes non-zero
ema_start_index = np.argmax(ema != 0)

# Convert candle_data to DataFrame for mplfinance
candle_data_df = pd.DataFrame({'Date': candle_data.index,
                               'Open': candle_data['Open'],
                               'High': candle_data['High'],
                               'Low': candle_data['Low'],
                               'Close': candle_data['Adj Close'],
                               'Volume': candle_data['Volume']})

# Plot the candlestick chart, KNN MA, and EMA
fig, axes = mpf.plot(candle_data_df, type='candle', style='charles', returnfig=True)
# Align KNN MA with candle data
knn_ma_aligned = [np.nan] * len(price_data) + knn_ma
axes[0].plot(time, knn_ma_aligned, label=f'KNN MA ({ma_len}-Period)', color='orange')
if ema_start_index > 0:
    axes[0].plot(time[ema_start_index:], ema[ema_start_index:], label=f'5-Period EMA', color='green')
axes[0].set_title(f'{symbol} Candlestick Chart with KNN MA and 5-Period EMA')
axes[0].legend()
plt.show()