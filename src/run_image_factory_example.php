<?php
require './imageFactoryBasic.php';

// import class
use imageFactoryBasic\imageFactoryBasic;

// create object
$imageFactoryBasicObj = new imageFactoryBasic();

// source image path
$fileTarget = 'a.png';

// check is image supported
if(!$imageFactoryBasicObj->fileTarget($fileTarget)->isSupported()){
    
    echo "Not supported file";
    
}else{

    // crop image
    $tmpPth1 = $imageFactoryBasicObj->fileTarget($fileTarget) // set source image path
    ->outputDir('/tmp') // set output destination directory (if not set default is current directory)
    ->outputName('after-crop-center') // set the output file name (name only without extension) (if not set default is same as source)
    ->makeThumb(500,1000); // set image size(in pixel) to crop center then execute
    
    // show image path
    echo $tmpPth1;

    // convert image to webp
    $tmpPth2 = $imageFactoryBasicObj->fileTarget($fileTarget) // set source image path
    ->outputDir('/tmp') // set output destination directory (if not set default is current directory)
    ->outputName('convert-to-webp') // set the output file name (name only without extension) (if not set default is same as source)
    ->convertToWebp(80); // set output image quality then execute

    // show image path
    echo $tmpPth2;

    // delete image file
    unlink($tmpPth1);
    unlink($tmpPth2);

    // get image width and height
    $imgSize = $imageFactoryBasicObj->fileTarget($fileTarget)->getSize();
    echo "Width: " . $imgSize['width'] . " height: " . $imgSize['height'];
}