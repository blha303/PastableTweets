<?php
$handle = fopen("cache/test.txt", 'w');
if($handle === FALSE)
    echo "\nfailed";
else
    echo "\nsuccess";
fclose($handle);
