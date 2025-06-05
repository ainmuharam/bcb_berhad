<?php
$output = shell_exec('python3 testCamera.py 2>&1');

echo "<pre>$output</pre>";
?>
