<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Account\Controller;

use Zend\View\Model\ViewModel;
use Workmark\Controller\WorkmarkController;
use Interop\Container\ContainerInterface;
use Zend\View\Model\JsonModel;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use Workmark\User\User;
use Workmark\Invoice\Invoice;
use Workmark\Model\Users;

class LogController  extends WorkmarkController
{
	private $authManager = null;
	private $model = null;
	
	public function __construct(ContainerInterface $container, $authManager)
	{
		$this->model = $container->get(\Workmark\Model\Model::class);
		$this->authManager = $authManager;
		parent::__construct($container);
	}
	
    public function inAction()
    {
    	
    	// Store login status.
    	$isLoginError = false;
    	
    	if ($this->authManager->isLogged() && User::getCurrent()->isManager()) {
    		return $this->redirect()->toUrl('/account/users/list');
    	}elseif($this->authManager->isLogged()){
    		return $this->redirect()->toUrl('/');
    	}
    	// Check if user has submitted the form
    	if ($this->getRequest()->isPost()) {
    		
    		$data = $this->params()->fromPost();
    		
    		if (empty($data['email']) || empty($data['password'])) {
    			$isLoginError = true;
    			
    			$this->layout()->setTemplate('layout/loginLayout');
    			return new ViewModel([
    					'isLoginError' => $isLoginError,
    					'currentUser' => User::getCurrent()
    			]);
    		}
    		
    		$result = $this->authManager->login($data['email'],
    				$data['password'], true);
    		
    		$user = new User($this->model->get(\Workmark\Model\Users::class));
    		
    		
    		if ($result->getCode()==Result::SUCCESS) {
    			$user->init($data['email']);
    			if($user->isManager()){
    				return $this->redirect()->toUrl('/account/users/list');
    			}else{
    				$isLoginError = true;
    			}
    		}else{
    			$isLoginError = true;
    		}
    	}
    	
    	$this->layout()->setTemplate('layout/loginLayout');
    	return new ViewModel([
    			'isLoginError' => $isLoginError,
    			'currentUser' => User::getCurrent()
    	]);
    }
    
    public function upAction()
    {
    	// Store login status.
    	$isLoginError = false;
    	
    	// Check if user has submitted the form
    	if ($this->getRequest()->isPost()) {
    		$data = $this->params()->fromPost();
    		$result = $this->authManager->login($data['email'],
    				$data['password'], true);
    		if ($result->getCode()==Result::SUCCESS) {
    			return $this->redirect()->toRoute('home');
    		}else{
    			$isLoginError = true;
    		}
    	}
    	
    	$this->layout()->setTemplate('layout/loginLayout');
    	return new ViewModel([
    			'isLoginError' => $isLoginError,
    			'currentUser' => User::getCurrent()
    	]);
    }
    
    public function fbAction()
    {
    	if ($this->authManager->isLogged())
    	{
    		return new JsonModel(array('status' => 'success', 'message' => 'fb', 'result'=>''));
    	}
    	
    	$fb = new Facebook([
    			'app_id' => '137098546976540',
    			'app_secret' => '2abfb760b6f9d5cd8b83a3ba25e00b52',
    			'default_graph_version' => 'v2.2',
    	]);

    	$helper = $fb->getJavaScriptHelper();
    	$userId = $helper->getUserId();
    	
    	
    	if(!isset($_SESSION['fb_access_token'])){
    		
    		//return new JsonModel(array('status' => 'success', 'message' => 'fbLogged', 'result'=>$userId));
	    	
	    	try {
	    		$accessToken = $helper->getAccessToken();
	    	} catch(Facebook\Exceptions\FacebookResponseException $e) {
	    		// When Graph returns an error
	    		return new JsonModel(array('status' => 'fail', 'message' => 'Graph returned an error: ' . $e->getMessage(), 'result'=>''));
	    	} catch(Facebook\Exceptions\FacebookSDKException $e) {
	    		// When validation fails or other local issues
	    		return new JsonModel(array('status' => 'fail', 'message' => 'Facebook SDK returned an error: ' . $e->getMessage(), 'result'=>''));
	    	}
	    	
	    	if (!isset($accessToken)) {
	    		return new JsonModel(array('status' => 'fail', 'message' => 'No cookie set or no OAuth data could be obtained from cookie.', 'result'=>''));
	    	}
	    	$_SESSION['fb_access_token'] = $accessToken;
	    	
	    	//die(var_dump($_SESSION['fb_access_token']));
	    	// User is logged in!
	    	$userId = $helper->getUserId();
	    	
    	}
    	if($userId)
    	{
    		$userRow = $this->model->get(\Workmark\Model\Users::class)->getByFbId($userId);
    		if($userRow['email'])
    		{
    			//$user = $this->container->get(User::class);
    			//$user->init($userRow['email']);
    			//if($user->persist())
    			//{
    				$this->authManager->loginFb($userRow['email'], true);
    				//User::setCurrent($user);
    				return new JsonModel(array('status' => 'success', 'message' => 'fb', 'result'=>''));
    			//}
    		}else{
    			try {
    				// Returns a Facebook\FacebookResponse object
    				$response = $fb->get('/me?fields=email,first_name,last_name,id', $_SESSION['fb_access_token']);
    				
    			} catch(Facebook\Exceptions\FacebookResponseException $e) {
    				return new JsonModel(array('status' => 'fail', 'message' => 'Graph returned an error: ' . $e->getMessage(), 'result'=>''));
    			} catch(Facebook\Exceptions\FacebookSDKException $e) {
    				return new JsonModel(array('status' => 'fail', 'message' => 'Facebook SDK returned an error: ' . $e->getMessage(), 'result'=>''));
    			}
    			$rData = $response->getGraphUser();
    			
    			if (!$rData['id'])
    			{
    				return new JsonModel(array('status' => 'fail', 'message' => 'No id', 'result'=>''));
    			}
    			$rData['fbId'] = $rData['id'];
    			if(!$rData['email'])
    			{
    				$rData['email']= $rData['id']."@facebook.com";
    			}
    			try {
    				$userRow = $this->model->get(\Workmark\Model\Users::class)->getByEmail($rData['email']);
    				$this->authManager->loginFb($userRow['email'], true);
    				return new JsonModel(array('status' => 'success', 'message' => 'fb', 'result'=>''));
    			}catch (\Exception $e)
    			{
    				//nothing
    			}
    			$user = $this->container->get(User::class);
    			
    			$cUser = $user->createUser($rData);
    			
    			if($cUser)
    			{
    				$this->authManager->loginFb($rData['email'], true);
    				return new JsonModel(array('status' => 'success', 'message' => 'No user '.$userId, 'result'=>''));
    			}
    		}
    	}
    	
    	return new JsonModel(array('status' => 'fail', 'message' => 'Something went wrong', 'result'=>''));
    }
    
    
    public function googleAction()
    {
    	
    	//include_once ROOT_PATH. '/vendor/autoload.php';
    	if ($this->authManager->isLogged())
    	{
    		//return new JsonModel(array('status' => 'success', 'message' => 'gg', 'result'=>''));
    	}
    	//die(var_dump(ROOT_PATH));
    	$redirect_uri = 'http://workmark.lepsha.com';
    	$client = new \Google_Client();
    	$client->setClientId('216597812261-jkqbe7vv494u3i4uih6r2c8dc9olf8ro.apps.googleusercontent.com');
    	$client->setClientSecret('KfhcA_p8xyusI0auHJmDTp4E');
    	$client->setRedirectUri($redirect_uri);
    	//$client->setScopes('email');
    	
    	
    	if (isset($_REQUEST['code'])) {
    		$token = $client->fetchAccessTokenWithAuthCode($_REQUEST['code']);
    		//die(var_dump($token));
    		try{
    		$client->setAccessToken($token);
	    		// store in the session also
	    		$_SESSION['id_token_token'] = $token;
	    		
	    		$plus= new \Google_Service_Plus($client);
	    		
	    		$me = $plus->people->get('me');
	    		
	    		$rData['ggId'] = $me['id'];
	    		$rData['first_name'] = $me['name']['givenName'];
	    		$rData['last_name'] = $me['name']['familyName'];
	    		$rData['email'] = $me['emails'][0]['value'];
	    		$userId = $me['id'];
	    		if($userId)
	    		{
	    			$userRow = $this->model->get(\Workmark\Model\Users::class)->getByGoogleId($userId);
	    			if($userRow['email'])
	    			{
	    				//$user = $this->container->get(User::class);
	    				//$user->init($userRow['email']);
	    				//if($user->persist())
	    				//{
	    				$this->authManager->loginFb($userRow['email'], true);
	    				//User::setCurrent($user);
	    				return new JsonModel(array('status' => 'success', 'message' => 'gg', 'result'=>''));
	    				//}
    				}else{
    					
    					if (!$rData['ggId'])
    					{
    						return new JsonModel(array('status' => 'fail', 'message' => 'No id', 'result'=>''));
    					}
    					
    					if(!$rData['email'])
    					{
    						$rData['email']= $rData['ggId']."@google.com";
    					}
    					
    					$userRow = $this->model->get(\Workmark\Model\Users::class)->getByEmail($rData['email']);
    					if($userRow['email'])
    					{
    						$upData['id']=$userRow['id'];
    						$upData['ggId']=$rData['ggId'];
    						$this->model->get(\Workmark\Model\Users::class)->save($upData);
    						$this->authManager->loginFb($userRow['email'], true);
    						return new JsonModel(array('status' => 'success', 'message' => 'gg', 'result'=>''));
    					}
    					
    					$user = $this->container->get(User::class);
    					$cUser = $user->createUser($rData);
    					//die(var_dump($cUser));
    					if($cUser)
    					{
    						$this->authManager->loginFb($rData['email'], true);
    						return new JsonModel(array('status' => 'success', 'message' => 'No user '.$userId, 'result'=>''));
    					}
    				}
	    			}
	    		return new JsonModel(array('status' => 'success', 'message' => 'fb', 'result'=>$data));
    		}catch (\Exception $e)
    		{
    			return new JsonModel(array('status' => 'fail', 'message' => $e->getMessage(), 'result'=>''));
    		}
    	}
    	
    	return new JsonModel(array('status' => 'fail', 'message' => 'Something went wrong', 'result'=>''));
    }
    
    public function outAction()
    {
    	// Remove identity from session.
    	$this->authManager->logout();
    	return $this->redirect()->toUrl('/account/log/in');
    	return new ViewModel();
    }
    
    public function showAction()
    {
    	if(!isset($_GET['id']) || intval($_GET['id']) < 1)
    	{
    		return $this->getResponse();
    	}
    	$id = intval($_GET['id']);
    	
    	$uploaddir = ROOT_PATH . '/data/images/';
    	$filePath = $uploaddir.$id.'.jpg';
    	
    	if (!is_readable($filePath))
    	{
    		return "";
    	}
    	
    	
    	$finfo = finfo_open(FILEINFO_MIME_TYPE);
    	$mimetype = finfo_file($finfo, $filePath);
    	finfo_close($finfo);
    	
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