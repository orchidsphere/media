<?php
/**
 * Vstupný skript pre zobrazovanie multimediálnych súborov
 * 
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @copyright  2016 OrchidSphere
 * @link       http://orchidsphere.com/
 * @license    License here
 * @version    1.0.0
 */
ini_set('display_errors',1);  
//error_reporting(E_ALL);

define('MEDIA_PATH_UPLOADS', __DIR__ . '/../../web/uploads/');
define('MEDIA_PATH_DEFAULT_IMAGE', __DIR__. '/include/default.png');
//define('MEDIA_IMAGE_WATERMARK', __DIR__. '/include/watermark.png');
//define('MEDIA_IMAGE_WATERMARK_THUMB', __DIR__. '/include/watermark_thumb.png');

require_once 'mediahandler.class.php';
$mh = new MediaHandler();

$fn = urldecode(basename(array_shift((explode('?', $_SERVER['REQUEST_URI'])))));
$mh->image_handle($fn);
