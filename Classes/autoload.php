<?php

$self = __FILE__;
if(!isset($scanDir)){
    $scanDir = __DIR__;
}

$files = glob("{$scanDir}/*", GLOB_BRACE);
foreach($files as $file){
    if(!file_exists($file)){
        continue;
    }

    if(is_dir($file)){
        $scanDir = $file;
        require $self;
        continue;
    }

    $fileName = basename($file);
    if(!preg_match('/\.class\.php$/i', $fileName)){
        continue;
    }

    require $file;
}