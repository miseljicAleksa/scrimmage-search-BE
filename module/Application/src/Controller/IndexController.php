<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Workmark\Model\Timeline;
use Zend\View\Model\ViewModel;
use Workmark\Controller\WorkmarkController;
use Interop\Container\ContainerInterface;
use Workmark\User\User;

class IndexController extends WorkmarkController
{
	/**@var Model */
	private $model;
	
	public function __construct(ContainerInterface $container)
	{
		$this->model = $container->get(\Workmark\Model\Model::class);
    	parent::__construct($container);
    }
    
    public function indexAction()
    {
    	return $this->redirect()->toUrl('/account/log/in');
    	//echo ini_get('session.gc_maxlifetime');
    	return new ViewModel([
    			'currentUser' => User::getCurrent()
    		]);
    }
    
    public function privacyAction()
    {
    	return new ViewModel([]);
    }
    
    public function termsAction()
    {
    	return new ViewModel([]);
    }
   
    
    public function showAction()
    {
    	if(!isset($_GET['id']) || !isset($_GET['type']))
    	{
    		return $this->getResponse();
    	}
    	$id = intval($_GET['id']);
    	$type = str_replace("\\", "", $_GET['type']);
    	
    	$uploaddir = ROOT_PATH . '/data/uploads/'.$type.'/';
    	$filePath = $uploaddir . $id . '.png';
    	
    	$finfo = finfo_open(FILEINFO_MIME_TYPE);
    	$mimetype = finfo_file($finfo, $filePath);
    	finfo_close($finfo);
    	
    	session_cache_limiter('none');
    	
    	//Then send Cache-Control: max-age=number_of_seconds and optionally an equivalent Expires: header.
    	
    	header('Cache-control: max-age='.(60*60*24*365));
    	header('Expires: '.gmdate(DATE_RFC1123,time()+60*60*24*365));
    	
    	header('Pragma: public');
    	header('Cache-Control: max-age=86400, public');
    	
    	//For the best cacheability, send Last-Modified header and reply with status 304 and empty body if the browser sends a matching If-Modified-Since header.
    	
    	header('Last-Modified: '.gmdate(DATE_RFC1123,filemtime($filePath)));
    	
    	//For brevity I'm cheating here a bit (the example doesn't verify the date), but it's valid as long as you don't mind browsers keeping the cached file forever:
    	
    	if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    		header('HTTP/1.1 304 Not Modified');
    		die();
    	}
    	header('Content-Disposition: inline');
    	header('Content-Length: ' . filesize($filePath));
    	header("Content-Type: " . $mimetype);
    	
    	die(readfile($filePath));
    	
    }
    
    
    private function processImageFile($tmpName, $maxDim = 900)
    {
    	$ext = pathinfo($tmpName, PATHINFO_EXTENSION);
    	
    	//die(var_dump($ext));
    	/* Process image with GD library */
    	$verifyimg = getimagesize($tmpName);
    	
    	/* Make sure the MIME type is an image */
    	$pattern = "#^(image/)[^\s\n<]+$#i";
    	
    	if(!preg_match($pattern, $verifyimg['mime'])){
    		return false;
    	}
    	
    	list($width, $height) = getimagesize($tmpName);
    	
    	
    	if ( $width > $maxDim || $height > $maxDim ) {
    		$ratio = $width/$height; // width/height
    		if( $ratio > 1) {
    			$newWidth = $maxDim;
    			$newHeight = $maxDim/$ratio;
    		} else {
    			$newWidth= $maxDim*$ratio;
    			$newHeight= $maxDim;
    		}
    		
    		$dst = imagecreatetruecolor( $newWidth, $newHeight);
    		$src = ($ext == 'png')?imagecreatefrompng($tmpName):imagecreatefromjpeg($tmpName);
    		imagecopyresampled( $dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    		
    	}else{
    		$dst = imagecreatetruecolor( $newWidth, $height );
    		$src = ($ext == 'png')?imagecreatefrompng($tmpName):imagecreatefromjpeg($tmpName);
    		imagecopyresampled( $dst, $src, 0, 0, 0, 0, $width, $height, $width, $height);
    	}
    	imagedestroy( $src );
    	//die(var_dump($ext));
    	if ($dst) {
    		return ($ext == 'png')?imagepng($dst):imagejpeg($dst);
    	}
    	
    	return false;
    }
}
