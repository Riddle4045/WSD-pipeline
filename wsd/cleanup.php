<?php

return;

sleep(1);

$root = $_POST['root'];

unlink("$root/embedded.html");
unlink("$root/labeled_train_feat.int32");
shell_exec("rm -r $root/test_image_feat");
shell_exec("rm -r $root/kmeans");
shell_exec("rm -r $root/*.seed");
shell_exec("rm -r $root/*.classifier");
shell_exec("rm -r $root/*.train");

?>
