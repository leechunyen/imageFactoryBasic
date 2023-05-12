<?php
namespace imageFactoryBasic;

class imageFactoryBasic{
    private string $targetPath;
    private string $output_dir;
    private string $output_name;
    private bool $pass;
    
    // init
    public function __construct(){
        $this->outputDir = '';
        $this->outputName = '';
        $this->pass = true;
    }
    
    // set value
    public function fileTarget(string $ft = ''){
        // check file exist
        if(!file_exists($ft)||empty($ft)||is_dir($ft)||!is_file($ft)){
            $this->pass = false;
        }else{$this->targetPath = $ft;}
        return $this;
    }
    
    public function outputDir(string $dir){
        $this->outputDir = $dir;
        // if dir not exist create it
        if($this->pass){
            if(!file_exists($dir . '/')){
                shell_exec('mkdir -p ' . $dir . '/');
            }
        }
        return $this;
    }
    
    public function outputName($name){
        $this->outputName = $name;
        return $this;
    }
    // end set value
    
    // actual run function
    public function isSupported(){
        if(!$this->getImageType()['gdImg']){return false;}
        return true;
    }
    
    public function convertToWebp($compression_quality = 100){
        if(!$this->pass){return false;}
        $output_dir = $this->outputDir;
        $outputName = $this->outputName;
        $imageFromArr = $this->getImageType();
        
        if(!extension_loaded('gd')){return false;}
        
        // if output_dir not set use current dir
        if(empty($output_dir)){$output_dir = '.';}
        
        // set output file to
        $output_file =  $output_dir . '/';
        
        // if file name not set use default
        if($outputName <> ""){
            $output_file .= $outputName . '.webp';
        }else{
            $output_file .= pathinfo($this->targetPath, PATHINFO_FILENAME) . '.webp';
        }
        
        // start convert
        if (function_exists('imagewebp')) {
            // Save the image
            $result = imagewebp($imageFromArr['gdImg'], $output_file, $compression_quality);
            if (false === $result) {
                return false;
            }
            // Free up memory
            imagedestroy($imageFromArr['gdImg']);
            return $output_file;
        }
        return false;
    }
    
    public  function makeThumb( $width = 60,$height = 60){
        // ref https://a32.me/2012/06/scale-images-to-fit-fill-bounding-box-in-php-using-gd/
        if(!$this->pass){return false;}
        $outputName = $this->outputName;
        $outputDir = $this->outputDir;
        $imageFromArr = $this->getImageType();
        
        if(!extension_loaded('gd')){return false;}
        
        // get extension
        $type = pathinfo($this->targetPath, PATHINFO_EXTENSION);
        if(empty($type)){
            $type = $imageFromArr['gdExt'];
        }
        
        // set file name
        if(!empty($outputName)){
            if(!empty($type)){$outputName .= "." . $type;}
        }else{$outputName = basename($this->targetPath);}
        
        // if outputDir not set use current dir
        if(empty($outputDir)){$outputDir = '.';}

        // set path to save
        $outputTo = $outputDir.'/'.$outputName;
        
        $w = intval($width);
        $h = intval($height);
        $mode = 'fill';// 'fit' or 'fill'
        // if ($w <= 1 || $w >= 1000) $w = 100;
        // if ($h <= 1 || $h >= 1000) $h = 100;
        
        // Destination image with white background
        $tmp_img = imagecreatetruecolor($w, $h);
        imagefill($tmp_img, 0, 0, imagecolorallocate($tmp_img, 255, 255, 255));
        
        // All Magic is here
        $src_width = imagesx($imageFromArr['gdImg']);
        $src_height = imagesy($imageFromArr['gdImg']);
        
        $dst_width = imagesx($tmp_img);
        $dst_height = imagesy($tmp_img);
        
        // Try to match destination image by width
        $new_width = $dst_width;
        $new_height = round($new_width*($src_height/$src_width));
        $new_x = 0;
        $new_y = round(($dst_height-$new_height)/2);
        
        // FILL and FIT mode are mutually exclusive
        if ($mode =='fill')
            $next = $new_height < $dst_height; else $next = $new_height > $dst_height;
        
        // If match by width failed and destination image does not fit, try by height 
        if ($next) {
            $new_height = $dst_height;
            $new_width = round($new_height*($src_width/$src_height));
            $new_x = round(($dst_width - $new_width)/2);
            $new_y = 0;
        }
        
        // Copy image on right place
        imagecopyresampled($tmp_img, $imageFromArr['gdImg'] , $new_x, $new_y, 0, 0, $new_width, $new_height, $src_width, $src_height);
        
        // save file
        switch ($imageFromArr['typeNum']) {
            case '1': //IMAGETYPE_GIF
                imagegif($tmp_img,$outputTo);
                break;
            case '2': //IMAGETYPE_JPEG
                imagejpeg($tmp_img,$outputTo);
                break;
            case '3': //IMAGETYPE_PNG
                imagepng($tmp_img,$outputTo,0);
                break;
            case '6': // IMAGETYPE_BMP
                imagebmp($tmp_img,$outputTo);
                break;
            case '15': //IMAGETYPE_WBMP
              imagewbmp($tmp_img,$outputTo);
                break;
            case '16': //IMAGETYPE_XBM
                imagexbm($tmp_img,$outputTo);
                break;
            case '18': //IMAGETYPE_WEBP
                imagewebp($tmp_img,$outputTo,100);
                break;
            default:
                return false;
        }
        
        // clear tmp img
        imagedestroy($tmp_img);
        
        return $outputTo;
    }
    // end actual run function
    
    // private function
    private function getImageType(){
        // get file type
        $imgType = exif_imagetype($this->targetPath);
        $output['typeNum'] = $imgType;
        //https://www.php.net/manual/en/function.exif-imagetype.php
        //exif_imagetype($file);
        // 1    IMAGETYPE_GIF
        // 2    IMAGETYPE_JPEG
        // 3    IMAGETYPE_PNG
        // 6    IMAGETYPE_BMP
        // 15   IMAGETYPE_WBMP
        // 16   IMAGETYPE_XBM
        // 18   IMAGETYPE_WEBP
        
        // find type
        switch ($imgType) {
            case '1': //IMAGETYPE_GIF
                $output['gdImg'] = imagecreatefromgif($this->targetPath);
                $output['gdExt'] = 'gif';
                break;
            case '2': //IMAGETYPE_JPEG
                $output['gdImg'] = imagecreatefromjpeg($this->targetPath);
                $output['gdExt'] = 'jpeg';
                break;
            case '3': //IMAGETYPE_PNG
                $output['gdImg'] = imagecreatefrompng($this->targetPath);
                $output['gdExt'] = 'png';
                break;
            case '6': // IMAGETYPE_BMP
                $output['gdImg'] = imagecreatefrombmp($this->targetPath);
                $output['gdExt'] = 'bmp';
                break;
            case '15': //IMAGETYPE_WBMP
               $output['gdImg'] = imagecreatefromwbmp($this->targetPath);
               $output['gdExt'] = 'wbmp';
                break;
            case '16': //IMAGETYPE_XBM
                $output['gdImg'] = imagecreatefromxbm($this->targetPath);
                $output['gdExt'] = 'xbm';
                break;
            case '18': //IMAGETYPE_WEBP
                $output['gdImg'] = imagecreatefromwebp($this->targetPath);
                $output['gdExt'] = 'webp';
                break;
            default:
                $output['gdImg'] = false;
        }
        return $output;
    }
    // end private function
}