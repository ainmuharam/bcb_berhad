<?php
// Trigger the python script
$output = shell_exec('python3 /path/to/camera_script.py 2>&1');

// Display output from python
echo "<pre>$output</pre>";
?>
