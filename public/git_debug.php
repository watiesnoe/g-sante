<?php
$res = file_put_contents('/tmp/.gitconfig', "[safe]\n\tdirectory = *\n");
var_dump($res);
if ($res === false) {
    print_r(error_get_last());
}

if (file_exists('/tmp/.gitconfig')) {
    echo "/tmp/.gitconfig exists!\n";
} else {
    echo "/tmp/.gitconfig does not exist!\n";
}
