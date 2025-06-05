<?php
$output = shell_exec('which python3 2>&1');
echo "Python path: <pre>$output</pre>";

$output_version = shell_exec('python3 --version 2>&1');
echo "Python version: <pre>$output_version</pre>";
?>
