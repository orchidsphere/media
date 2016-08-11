<?php
/*
ini_set('display_errors',1);  
error_reporting(E_ALL);
*/
define('MEDIA_PATH_UPLOADS', dirname(__FILE__) . '/../../_mackovia/uploads/image_bank/image_bank/' );
define('MEDIA_URL_UPLOADS', 'http://www.mackoviahracky.sk/uploads/image_bank/' );
define('CONFIG_ROOT', dirname(__FILE__) . '/../../_mackovia/config.root.php');

require_once 'include/download.access.php';

$dh  = new Downloadhandler();
$tkn = filter_input( INPUT_GET, 'token', FILTER_SANITIZE_ENCODED );
$psw = filter_input( INPUT_GET, 'access', FILTER_SANITIZE_ENCODED );

if( !in_array($psw, $access)){
    echo "An error occured during your request. You are not allowed to download.";
    exit();
}
elseif( !empty($tkn) ){
    $dh->getproduct($tkn);
}
else{
    $dh->listall($psw);
}

class Downloadhandler {
    
    private $db = NULL;
    
    public function __construct() {
        
        require CONFIG_ROOT;
        
        $this->db = new Mysqlhandler();
        $this->db->newConnection( 
                $configs['db']['db_host'], 
                $configs['db']['db_user'], 
                $configs['db']['db_pass'], 
                $configs['db']['db_name']
            );
    }
    
    public function listall( $access_psw ){
        
        $queryStr = 'SELECT CONCAT(p.artno, " ", pl.`name`) as `name`, pl.`id_product` FROM product p LEFT JOIN product_lang pl ON p.id = pl.id_product AND pl.id_shop = 1 ORDER BY p.id DESC';
        $products = $this->db->executeQuery($queryStr);
        
        echo
            '<!DOCTYPE html><head><meta content="text/html; charset=utf-8" http-equiv="Content-Type"/></head><body>';
            
        foreach ( $products as $product ){
            echo
                '<a href="?access=' . $access_psw . '&token=' . $product->id_product . '" title="Stiahnúť všetky fotky produktu">',
                    $product->name,
                '</a><br />';
        }
    }
    
    public function getproduct( $id_product ){
    
        $queryStr = 
                'SELECT 
                    i.`fileurl`, pl.`url`, pl.`id_product` 
                FROM image i
                LEFT JOIN product_lang pl on i.id_product = pl.id_product 
                WHERE i.id_product = ' . $id_product . ' AND pl.id_shop = 1';
        
        $images = $this->db->executeQuery($queryStr);
        
        echo
            '<!DOCTYPE html><head><meta content="text/html; charset=utf-8" http-equiv="Content-Type"/></head><body style="float:left">';
        
        foreach ( $images as $image ){
            
            $file = array_pop(( explode('/', $image->fileurl) ));
            if( file_exists(MEDIA_PATH_UPLOADS . $file) ){
                echo '<img src="' . MEDIA_URL_UPLOADS . $file . '" alt="Obrázok sa načítava..." style="max-height: 400px; width: auto"/><hr/>';   
            }
        }
        /* NOT WORKING ZIP COMPRESSION */
//	$url = $images[0]->url;
//	$zip = new ZipArchive();
//	$filename = "{$url}.zip";
//      $filepath = dirname(__FILE__) . "/{$filename}";
//        
//	if ( $zip->open( $filepath, ZipArchive::OVERWRITE) != TRUE ) {
//	    echo "An error occured during your request. Cannot create zip archive.";
//	    exit();
//	}
//	
//        foreach ( $images as $image ){
//            $file = array_pop(( explode('/', $image->fileurl) ));
//            
//            if( file_exists(MEDIA_PATH_UPLOADS . $file) ){
//                $zip->addfile( MEDIA_PATH_UPLOADS . $file, $file );
//            }
//        }
//        
//        if( $zip->numFiles != 0 && $zip->close() ){ 
//           
//            header("content-type: application/zip");
//            header("content-disposition: inline; filename={$filename}");
//            readfile( dirname(__FILE__) . "/{$filename}" );
//        }
//        else{
//            echo "An error occured during your request. No content. ";
//            echo $zip->getStatusString();
//            exit();
//        }    
    }
}

class Mysqlhandler {
  
    private $connection;
    
    /**
     * Vytvorenie nového spojenia s databázou
     * 
     * Funkcie metódy by mohol prebrať aj konštruktor, takto však je možne uchovávať
     * viacero spojení v jednom objekte
     * @param String $host Názov hostiteľa
     * @param String $user Používateľské meno
     * @param String $password Heslo
     * @param String $database Názov databázy
     */
    public function newConnection( $host, $user, $password, $database ){
        
        $this->connection = new mysqli( $host, $user, $password, $database );
        $this->connection->set_charset("utf8");
        
        if (mysqli_connect_errno() ) {
            echo "An error occured during your request.";
	    exit();
        }
    }
    
    /**
     * Metóda vykoná databázový dotaz
     * @param String $queryStr Dotaz
     * @return array 
     */
    public function executeQuery( $queryStr ) {

        if( !$result = $this->connection->query( $queryStr ) ) {
            echo "An error occured during your request. $queryStr";
	    exit();
        }
        else {
            $ret = array();
	    while ( $row = $result->fetch_object() ) {
		$ret[] = $row;
	    }
	    return $ret;
        }
    }
}