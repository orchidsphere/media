<?php 
/**
 * Trieda MediaHandler pre spracovanie požiadavky na multimediálny súbor
 *  
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @copyright  2016 OrchidSphere
 * @link       http://orchidsphere.com/
 * @license    License here
 * @version    1.0.0
 */

class MediaHandler {
    
    /**
     * Metóda generuje multimediálny výstup vo formáte image/gif, image/jpeg, image/png
     * 
     * Nová verzia využívajúca knižnice Imagick aj GD
     * Návod ako nainštalovať Imagick knižnicu je v orchidcore/models/ImageModel
     * 
     * @param string $filename Názov súboru
     * @param string $size (image_bank, large, medium, small)
     */
    public function image_handle($filename, $size = NULL){
               
        $this->file_type($filename, $ftype, $ctype);
        header('Content-type: ' . $ctype);
        header('Cache-Control: max-age=86400');
        
        $wmflag  = 0;
        
        if(($fpath = $this->file_find($filename, $size)) !== FALSE){
	    
            $im = new \Imagick( $fpath );
	    
            switch ($size){
            	case 'large':
            		$wmflag = 2;
            		break;
            	case 'image_bank':
            		$wmflag = 1;
            		break;
            	default:
            		$wmflag = 0;
            		break;
            }
        }
        else{
            readfile(MEDIA_PATH_DEFAULT_IMAGE);
            return TRUE;
        }
        
        //** Watermark
        if(defined('MEDIA_IMAGE_WATERMARK') && $wmflag){
            
	    $watermarkPath = ( $wmflag == 2 ) ? MEDIA_IMAGE_WATERMARK_THUMB : MEDIA_IMAGE_WATERMARK ;
	    $this->_imageWatermark( $im, $watermarkPath );

        }
        
        //** Save
        empty($size)? $size = 'image_bank' : FALSE;
        $filepath = __DIR__ . '/files/' . $size . '/' . $filename;
        
        $this->_imageOutput( $im, $ftype, $filepath ); 
        
        echo $im;
    }
    
    
    private function _imageWatermark( &$im, $watermarkPath ){
	
	$watermark = new \Imagick( $watermarkPath );
	$im->compositeImage($watermark, imagick::COMPOSITE_OVER, (((($im->getImageWidth()) - ($watermark->getImageWidth())))/2), (((($im->getImageHeight()) - ($watermark->getImageHeight())))/2) );
	
	return null;
	
    }
    
    private function _imageOutput( &$im, $extension, $outputPath ){
	
	$im->setSamplingFactors(array('2x2', '1x1', '1x1'));
        $im->stripImage();
	
	/** @todo */
        if (array_search(strtolower($extension), ['jpg', 'jpeg', 'jpe', 'jif', 'jfif', 'jfi']) !== false) {
            $im->setcompression(\Imagick::COMPRESSION_JPEG);
            $im->setimageformat("jpg");
            $im->setimagecompressionquality(85);
        } elseif (strtolower($extension) == 'png') {
            $im->setimageformat("png");
            $im->setimagecompressionquality(0);
        } elseif (strtolower($extension) == 'gif') {
            $im->setimageformat("gif");
        }
	
	
	$im->writeImage( $outputPath );
	
	return null;
    }
    
    
    /**
     * Stará verzia využívajúca iba GD knižnicu.
     * Metóda generuje multimediálny výstup vo formáte image/gif, image/jpeg, image/png
     * 
     * @param string $filename Názov súboru
     * @param string $size (image_bank, large, medium, small)
     * @return bool TRUE pri uspešnom zobrazení obrázku (požadovaný alebo default), FALSE pri chybe
     */
    
    /*public function image_handle($filename, $size = NULL){
               
        $this->file_type($filename, $ftype, $ctype);
        header('Content-type: ' . $ctype);
        header('Cache-Control: max-age=86400');
        
        $wmflag  = 0;
        
        if(($fpath = $this->file_find($filename, $size)) !== FALSE){
            $im = $this->image_create($fpath, $ftype);
            switch ($size){
            	case 'large':
            		$wmflag = 2;
            		break;
            	case 'image_bank':
            		$wmflag = 1;
            		break;
            	default:
            		$wmflag = 0;
            		break;
            }
        }
        else{
            readfile(MEDIA_PATH_DEFAULT_IMAGE);
            return TRUE;
        }
        
        //** Watermark
        if(defined('MEDIA_IMAGE_WATERMARK') && $wmflag){
            
            if($wmflag == 2){
                $this->image_watermark($im, MEDIA_IMAGE_WATERMARK_THUMB);
            }
            else{
                $this->image_watermark($im, MEDIA_IMAGE_WATERMARK);
            }
        }
        
        //** Save
        empty($size)? $size = 'image_bank' : FALSE;
        $filepath = __DIR__ . '/files/' . $size . '/' . $filename;
        
        $this->image_output($im, $ftype, $filepath);
        imagedestroy($im); 
        
        return TRUE;
    }
    
    /**
     * Vhodnou funkciou vytvorí obrázok a vráti jeho resource
     * @return Resource
     */
    private function image_create($filepath, $filetype){
                
        switch(strtolower($filetype)) {
            case "gif": 
                    $im = @imagecreatefromgif($filepath);
                break;
            case "jpeg":
            case "jpg": 
            case "jpe": 
            case "jif": 
            case "jfif": 
            case "jfi": 
                    $im = @imagecreatefromjpeg($filepath);
                break;
            case "png": 
                    $im = @imagecreatefrompng($filepath);
                    imageAlphaBlending($im, true);
                    imageSaveAlpha($im, true);
                break;
            default:
                    $im = @imagecreatefrompng(MEDIA_PATH_DEFAULT_IMAGE);
                break;
        }
        
        return $im;
    }
    
    /**
     * Vhodnou funkciou pošle obrázok na output, zároveň uloží ako statický obsah
     * @param resource $im Zdroj obrázku
     * @param string $filetype Typ súboru
     */
    private function image_output(&$im, $filetype, $filepath){
        
        switch(strtolower($filetype)) {
            case "gif": 
                    @imagegif($im,$filepath);
                    imagegif($im);
                break;
            case "jpeg":
            case "jpg": 
                    @imagejpeg($im, $filepath, 85);
                    imagejpeg($im);
                break;
            case "png": 
            default:
                    @imagepng($im, $filepath, 9);
                    imagepng($im);
                break;
        }
    }
    
    /**
     * Na obrázok pridá vodoznak
     * @param resource $im Zdroj obrázku, na ktorý sa pridá vodoznak
     * @param string $wm_path Cesta ku PNG súboru s vodznakom
     */
    private function image_watermark(&$im, $wm_path){
                    
        $wm       = imagecreatefrompng($wm_path);
        $p_right  = 10;
        $p_bottom = 10;
        $im_sx    = imagesx($im);
        $im_sy    = imagesy($im);
        $wm_sx    = $im_sx - 20; //10px margin both sides
        $wm_sy    = (imagesy($wm) * $wm_sx / imagesx($wm)); //10px margin both sides
                
        imagecopyresampled($im, $wm, 
                   $im_sx - $wm_sx - $p_right, $im_sy - $wm_sy - $p_bottom, 
                   0, 0, 
                   $wm_sx , $wm_sy, 
                   imagesx($wm), imagesy($wm));
    }
    
    /**
     * Zmení veľkosť obrázka
     * @param resource $im
     * @param float $d_width Požadovaná šírka
     * @param float $d_height Požadovaná výška
     * @return resource Obrázok novej veľkosti
     */
    private function image_resize($im, $d_width, $d_height){
       
        $o_width  = imagesx($im);
        $o_height = imagesy($im);
        $d_width  = intval($d_width); 
        $d_height = intval($d_height); 
        $ratio    = $o_width / $o_height;
        
        if((empty($d_height) && $d_width < $o_width) || ($d_width > $d_height && $d_width < $o_width)){
            $n_width  = $d_width;
            $n_height = intval($n_width / $ratio);
        }
        elseif((empty($d_width) && $d_height < $o_height) || ($d_width <= $d_height && $d_height < $o_height)){
            $n_height = $d_height;
            $n_width  = intval($n_height * $ratio);
        }
        else{
            return $im;
        }
        $n_im = imagecreatetruecolor($n_width, $n_height);
        @imagealphablending($n_im, false); // turning off alpha blending 
        @imagesavealpha($n_im, true); // turning on alpha channel
        imagecopyresampled($n_im, $im, 0, 0, 0, 0, $n_width, $n_height, $o_width, $o_height);
        
        return $n_im;
    }
    
    /**
     * Vyhľadá súbor v zložke definovanej konštantou MEDIA_PATH_UPLOADS a podzložke definovanej parametrom
     * @param string $filename
     * @param string $subfolder
     * @return string | bool - absolútna cesta k súboru ak existuje | FALSE ak neexistuje
     */
    private function file_find($filename, $subfolder){
        
        if(file_exists(MEDIA_PATH_UPLOADS . $subfolder . '/' . $filename)){
            return MEDIA_PATH_UPLOADS . $subfolder . '/' . $filename;
        }
        
        return FALSE;
    }
    
    /**
     * Metóda zistuje typ súboru 
     * @param type $filename
     * @param string $ftype
     * @param string $ctype
     */
    private function file_type($filename, &$ftype, &$ctype){
        
        $ftype = strtolower(substr (strrchr(basename($filename), "."), 1));
 
        switch($ftype) {
            case "gif": 
                    $ctype="image/gif"; 
                break;
            case "jpeg":
            case "jpg": 
                    $ctype="image/jpg"; 
                break;
            case "png": 
            default:
                    $ctype="image/png"; 
                break;
        }
    }
}
