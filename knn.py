import yfinance as yf
import numpy as np
import matplotlib.pyplot as plt
import mplfinance as mpf

def fetch_price_data(symbol, start_date, end_date):
    # Fetch historical price data from Yahoo Finance
    df = yf.download(symbol, start=start_date, end=end_date)
    return df['Adj Close'].values

def fetch_candle_data(symbol, start_date, end_date, timeframe):
   # Fetch historical price data from Yahoo Finance with the specified timeframe
    df = yf.download(symbol, start=start_date, end=end_date, interval=timeframe)
    return df

def fetch_5_minute_data(symbol, start_date, end_date):
    # Fetch historical 5-minute price data from Yahoo Finance
    df = yf.download(symbol, start=start_date, end=end_date, interval='5m')
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
start_date = '2023-08-01'  # Replace with your desired start date
end_date = '2023-09-16'  # Replace with your desired end date
price_data = fetch_price_data(symbol, start_date, end_date)



# Adjust the parameters as needed
ma_len = 5
num_closest_values = 3
smoothing_period = 50

predictions = calculate_knn_prediction(price_data, ma_len, num_closest_values, smoothing_period)

# Now, 'predictions' contains your KNN trend predictions for the specified stock.
# Fetch 5-minute chart data
price_data_5min = fetch_5_minute_data(symbol, start_date, end_date)

# Calculate KNN moving average with a specified MA length
ma_len = 5
knn_ma = calculate_knn_ma(price_data_5min, ma_len)

# Calculate 5-period Exponential Moving Average (EMA)
ema_len = 5
ema = calculate_ema(price_data_5min, ema_len)

# Create a time array for x-axis (assuming one data point every 5 minutes)
time = np.arange(len(price_data_5min))

# Find the index where EMA first becomes non-zero
ema_start_index = np.argmax(ema != 0)

# Plot the 5-minute chart and KNN MA on the same graph
plt.figure(figsize=(12, 6))
plt.plot(time, price_data_5min, label='5-Minute Chart', color='blue')
plt.plot(time[ma_len:], knn_ma, label=f'KNN MA ({ma_len}-Period)', color='orange')
if ema_start_index > 0:
    plt.plot(time[ema_start_index:], ema[ema_start_index:], label=f'5-Period EMA', color='green')
plt.xlabel('Time')
plt.ylabel('Price')
plt.title(f'{symbol} 5-Minute Chart with KNN MA')
plt.legend()
plt.grid(True)
plt.show()