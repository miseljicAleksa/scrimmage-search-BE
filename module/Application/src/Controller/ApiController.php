<?php
namespace Application\Controller;
use Firebase\JWT\JWT;

//Turn notices and warrnings to exceptions
set_error_handler(function ($severity, $message, $file, $line) {
	if (!(error_reporting() & $severity)) {
		// This error code is not included in error_reporting
		return;
	}
	throw new \Exception($message);
});
try{
	require_once(ROOT_PATH.'/vendor/SEI/loader.php');
}catch(\Throwable $e){
	//Return failed responce
	header('Access-Control-Allow-Origin: *');
	header('Content-type: application/json');
	die(json_encode(array("status"=>"fail","message"=>"fail","result"=>new stdClass()))); 
}


use Workmark\Controller\WorkmarkController;
use Zend\View\Model\JsonModel;
use Interop\Container\ContainerInterface;
use Workmark\Model\Model;

class ApiController extends WorkmarkController
{
	/**@var Model */
	private $model;
	
	
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->model = $container->get(\Workmark\Model\Model::class);
	}
    
	function jsonAction()
	{
	    //$_REQUEST['action'] = $this->params()->fromRoute('apiAction');
		try{
			//Instantiate Api
			$api = new \SEI\CoachAPI($this->model->getAdapter()->getDriver(), $_REQUEST, new JWT());
		}catch(\Throwable $e){
			//Test
			throw $e;
			//Return formated failed response
			return $this->setOutput(500, \SEI\Api::getFailedResponse($e->getMessage()));
		}
		
		//Return response
		return $this->setOutput(200, $api->getResponse());
		
	}
	
	
	/**
	 *
	 * @param int $statusCode
	 * @param string $output
	 * @return JsonModel
	 */
	private function setOutput($statusCode, $output)
	{
		
		$response = $this->getResponse();
		$response->getHeaders()->addHeaderLine('Access-Control-Allow-Origin', '*');
		return new JsonModel($output);
	}
}
