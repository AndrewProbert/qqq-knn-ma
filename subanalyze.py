import subprocess
import json
import numpy as np
import sys

# Run the PHP script and capture its output
php_script = "path/to/your/php/script.php"  # Replace with the actual path to your PHP script
output = subprocess.check_output(["php", php_script], universal_newlines=True)

# Parse the JSON data
result_array = json.loads(output)