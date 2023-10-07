import numpy as np
import matplotlib.pyplot as plt
import time
import subprocess
import re
import pandas as pd
import numpy as np
import csv





php_output = subprocess.check_output(["php", "subscript.php"], universal_newlines=True)


# Process the PHP output or do any other necessary operations
# For example, you can extract a value from the PHP output
value_from_php = (php_output.strip())


# Use regular expressions to find lines containing numeric values
numeric_lines = re.findall(r'\d+\.\d+', php_output)

# Join the numeric lines into a single string
filtered_string = '\n'.join(numeric_lines)

# Print the filtered string
#print(filtered_string)


# Split the filtered string into lines and convert them to floats
numeric_data = [float(line.strip()) for line in filtered_string.split('\n')]

# Create a DataFrame similar to the one returned by yf.download
df = pd.DataFrame({'Adj Close': numeric_data})

# Simulate the fetch_price_data function by returning the 'Adj Close' values as a NumPy array
price_data = df['Adj Close'].values



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
'''
# Plot the chart, KNN MA, 5-day EMA, and 9-day EMA on the same graph
plt.figure(figsize=(12, 6))
plt.plot(time, price_data, label=f' Chart', color='blue')
plt.plot(time[ma_len:], knn_ma, label=f'KNN MA ({ma_len}-Period)', color='orange')
if ema_start_index_5 > 0:
    plt.plot(time[ema_start_index_5:], ema_5[ema_start_index_5:], label=f'5-Day EMA', color='green')
#if ema_start_index_9 > 0:
#    plt.plot(time[ema_start_index_9:], ema_9[ema_start_index_9:], label=f'9-Day EMA', color='purple')
plt.xlabel('Time')
plt.ylabel('Price')
plt.title(f'{"QQQ"} {""} Chart with KNN MA and EMA')
plt.legend()
plt.grid(True)
plt.show()
'''

import csv

# Create a list to store the data along with highlighting information
highlighted_data = []

for i in range(len(time)):
    time_point = time[i]
    price_point = price_data[i]
    ema_point = ema_5[i] if i >= ema_start_index_5 else None
    knn_ma_point = knn_ma[i - ma_len] if i >= ma_len else None

    if ema_point is not None and knn_ma_point is not None:
        if ema_point > knn_ma_point:
            highlighted_data.append([time_point, price_point, ema_point, knn_ma_point, 'Green'])
        else:
            highlighted_data.append([time_point, price_point, ema_point, knn_ma_point, 'Red'])
    else:
        highlighted_data.append([time_point, price_point, ema_point, knn_ma_point, ''])

# Define the CSV file name
csv_file_name = 'output_data.csv'

# Write the data to the CSV file
with open(csv_file_name, mode='w', newline='') as file:
    writer = csv.writer(file)
    writer.writerow(['Time', 'Price', '5-Day EMA', 'KNN MA', 'Highlight'])
    writer.writerows(highlighted_data)

print(f'Data saved to {csv_file_name}')

position = 0


# Plot the chart, KNN MA, 5-day EMA, and 9-day EMA on the same graph
plt.figure(figsize=(12, 6))
plt.plot(time, price_data, label=f' Chart', color='blue')
plt.plot(time[ma_len:], knn_ma, label=f'KNN MA ({ma_len}-Period)', color='orange')
if ema_start_index_5 > 0:
    plt.plot(time[ema_start_index_5:], ema_5[ema_start_index_5:], label=f'5-Day EMA', color='green')

# Adding vertical lines with green for EMA above KNN and red for EMA below KNN
for i in range(ema_start_index_5 + 1, len(time)):
    if ema_5[i] > knn_ma[i - ma_len] and ema_5[i - 1] <= knn_ma[i - ma_len - 1]:
        

        plt.axvline(x=i, color='green', linestyle='-', alpha=0.7)
    elif ema_5[i] < knn_ma[i - ma_len] and ema_5[i - 1] >= knn_ma[i - ma_len - 1]:
        plt.axvline(x=i, color='red', linestyle='-', alpha=0.7)


# Adding red vertical lines at all data entry points
for entry_point in range(len(time)):
    plt.axvline(x=entry_point, color='blue', linestyle='--', alpha=0.2)

plt.xlabel('Time')
plt.ylabel('Price')
plt.title(f'{"QQQ"} {""} Chart with KNN MA and EMA')
plt.legend()
plt.grid(True)
#plt.show()




# Initialize variables
current_position = None  # None indicates no position
entry_price = 0
highest_price = 0  # Tracks the highest price during a green trend
lowest_price = float('inf')  # Tracks the lowest price during a red trend
total_profit = 0

# Iterate through the data
for row in highlighted_data:
    time_point, price_point, ema_point, knn_ma_point, highlight = row
    
    # Check if it's a green or red highlight
    if highlight == 'Green':
        if current_position == 'Short':
            # Close the short position at the current price
            profit_or_loss = entry_price - price_point
            total_profit += profit_or_loss
            current_position = None
            print(f"Short position closed at time {time_point}, {'Profit' if profit_or_loss > 0 else 'Loss'}: {abs(profit_or_loss):.2f}")

        if current_position is None:
            # Open a long position
            current_position = 'Long'
            entry_price = price_point
            highest_price = price_point
            print(f"Long position opened at time {time_point}, Entry Price: {entry_price:.2f}")

        # Update the highest price during the green trend
        highest_price = max(highest_price, price_point)

    elif highlight == 'Red':
        if current_position == 'Long':
            # Close the long position at the current price
            profit_or_loss = price_point - entry_price
            total_profit += profit_or_loss
            current_position = None
            print(f"Long position closed at time {time_point}, {'Profit' if profit_or_loss > 0 else 'Loss'}: {abs(profit_or_loss):.2f}")

        if current_position is None:
            # Open a short position
            current_position = 'Short'
            entry_price = price_point
            lowest_price = price_point
            print(f"Short position opened at time {time_point}, Entry Price: {entry_price:.2f}")

        # Update the lowest price during the red trend
        lowest_price = min(lowest_price, price_point)

# Print the total profit at the end (which includes both profits and losses)
print(f"Total Profit (including both profits and losses): {total_profit:.2f}")
