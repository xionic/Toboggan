
<pre>
<?php

$descriptorspec = array(
   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
   2 => array("pipe", "w") // stderr is a file to write to
);

$cwd = '/tmp';

$process = proc_open('bash', $descriptorspec, $pipes, $cwd);

stream_set_blocking($pipes[2], 0);

var_dump(stream_get_meta_data($pipes[1]));

fwrite($pipes[0], "echo testing output string \n");

var_dump(stream_get_meta_data($pipes[1]));

$out1 = fread($pipes[1],256);
$out2 = fread($pipes[2],256);

var_dump($out1);
var_dump($out2);

var_dump(stream_get_meta_data($pipes[1]));

fclose($pipes[0]);
fclose($pipes[1]);
fclose($pipes[2]);

proc_close($process);

?>
</pre>
