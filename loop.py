import os 
import subprocess
import numpy
import csv
import time
symbols = ["gs"]




with open("results.csv", "w") as f:
    f.write("")

for symbol in symbols:

    with open("symbols.txt", "w") as f:
        f.write(symbol + "\n")

        with open("results.csv", "a") as f:
            result = subprocess.run(["python", "knn.py"], capture_output=True)
            f.write(symbol + "," + str(result.stdout) + "\n")



