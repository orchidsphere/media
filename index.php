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

$req_uri = strpos($_SERVER['REQUEST_URI'], '?') == 0? $_SERVER['REQUEST_URI'] : substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
$request = explode('/', str_replace('/files/','',$req_uri));

$mh->image_handle(...array_reverse($request));
