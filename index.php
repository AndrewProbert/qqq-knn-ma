<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Price Analysis</title>
    <!-- Add CSS and JavaScript libraries as needed -->
</head>
<body>
    <h1>Stock Price Analysis</h1>
    <a href = "analyze.php">Analyze</a>

    <form method="POST" action="analyze.php">
        <label for="symbol">Stock Symbol:</label>
        <input type="text" id="symbol" name="symbol" required>
        <br>
        <label for="interval">Chart Interval:</label>
        <select id="interval" name="interval">
            <option value="1m">1 Minute</option>
            <option value="5m">5 Minutes</option>
            <!-- Add other interval options as needed -->
        </select>
        <br>
        <input type="submit" value="Analyze">
    </form>
</body>
</html>
