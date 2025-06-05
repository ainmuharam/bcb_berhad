<?php
// Trigger the python script
$output = shell_exec('python3 /path/to/testCamera.py 2>&1');

// Display output from python
echo "<pre>$output</pre>";
?>
