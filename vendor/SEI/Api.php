<?php
namespace SEI;

use SEI\ApiException;
use Firebase\JWT\JWT;
use SEI\DbAdapters\CIAdapter;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\DriverInterface;
use SEI\DbAdapters\ZendAdapter;
use Workmark\Mailer\Mailer;
use Workmark\Mailer\Parser;

/**
 * Api class for Lagree project
 * 
 * @package SEI
 * @author arsen.leontijevic
 * @version 1.0
 * @since 2019-09-26
 * @copyright Sei
 *
 */
abstract class Api {
	
	
	/**
	 * 
	 * Constants
	 */
	const STATUS_SUCCESS = 'success';
	const STATUS_FAILED = 'fail';
	const TOKEN_INVALID = 'token_invalid';
	const TOKEN_EXPIRED = 'token_expired';
	const TOKEN_VALID = 'token_valid';
	
	/**
	 * 
	 * @var string
	 */
	private $key = "wuy6T?0?=!11hs?+_ahor!@da";
	
	/**
	 * Hashing algorythm
	 * @var string
	 */
	private $alg = 'HS256';
	
	/**
	 * 
	 * @var JWT
	 */
	protected $jwt = null;
	
	/**
	 *
	 * @var DbAdapterInterface
	 */
	protected $dbAdapter = null;
	
	/**
	 * 
	 * @var array
	 */
	protected $request = array();
	
	/**
	 *
	 * @var array
	 */
	protected $response = array();
	
	/**
	 *
	 * @var int
	 */
	protected $user_id = null;
	
	/**
	 * Instantiate Api library
	 * 
	 * @param mixed \CI_DB_driver | Other Adapters $db
	 * @param array $request
	 * @param \Firebase\JWT\JWT $jwt
	 */
	public function __construct($db, array $request, \Firebase\JWT\JWT $jwt)
	{
		//Convert db errors to exceptions
		$driver = new \mysqli_driver();
		$driver->report_mode = MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR;
		
		//Turn notices and warrnings to exceptions
		set_error_handler(function ($severity, $message, $file, $line) {
			if (!(error_reporting() & $severity)) {
				// This error code is not included in error_reporting
				return;
			}
			throw new ApiException($message);
		});
			
		
		//Set proper adapter
		if ($db instanceof \CI_DB_driver)
		{
			$dbAdapter = new CIAdapter($db);
			$this->dbAdapter = $dbAdapter;
		}elseif($db instanceof DriverInterface){
			$this->dbAdapter = new ZendAdapter($db);
		}else{
			throw new ApiException("Unknown Db Adapter");
		}
			
		$this->jwt = $jwt;
		$this->request = $request;
		$this->processRequest($request);
		
	}
	
	abstract protected function processRequest(array $request);
	
	/**
	 * Get response formatted as array
	 * 
	 * @return array
	 */
	public function getResponse()
	{
		return $this->response;
	}
	
	/**
	 * Set and check response if properly formatted
	 *
	 * @return array
	 */
	public function setResponse(array $response)
	{
		if(!isset($response['status']) || !isset($response['message']) || !isset($response['result']))
		{
			throw new ApiException("Faild to set response, wrong format");
		}
		$this->response = $response;
		return $this;
	}
	
	/**
	 * Get failed response
	 * 
	 * @return array
	 */
	public static function getFailedResponse($message = "")
	{
		return self::formatResponse("fail", $message);
	}
	
	
	
	
	
	/**
	 * Make sure we get only required params
	 * 
	 * @param array $required
	 * @throws ApiException
	 * @return string[]
	 */
	protected function filterParams(array $required = array())
	{
		$result = [];
		foreach ($required as $one)
		{
			if (!isset($this->request[$one]))
			{
				throw new ApiException('This action requires params: ' . implode(" ", $required) . '. The "' . $one . '" is not set');
			}else{
				$result[$one] = trim($this->request[$one]);
			}
		}
		return $result;
	}
	
	/**
	 * Get user id from access token
	 *
	 * @return int
	 */
	protected function getUserId()
	{
		if(intval($this->user_id) < 1)
		{
			throw new ApiException('This action requires user id');
		}
		return $this->user_id;
	}
	
	/**
	 * Format response
	 * 
	 * @return array
	 */
	protected static function formatResponse($status, $message="", $res=array())
	{
		
		if(!in_array($status, [self::STATUS_SUCCESS,self::STATUS_FAILED]))
		{
			throw new ApiException("Given status is not allowed");
		}
		
		$json = json_encode($res);
		
		if($json[0] == "{")
		{
			//Object format
			$result = $res;
			$result->list = [];
		}else{
			//Array format
			$result["list"] = $res;
		}
		
		return array("status"=>$status, "message"=>$message, "result"=>$result);
	}
	
	/**
	 * Check Access Token
	 * 
	 * @param unknown $accessToken
	 * @return string
	 */
	protected function checkAccessToken($accessToken)
	{
		try{
			$decoded = $this->jwt::decode($accessToken, $this->key, array('HS256'));
		}catch (\Firebase\JWT\BeforeValidException $e)
		{
			return self::TOKEN_INVALID;
		}catch (\Firebase\JWT\ExpiredException $e)
		{
			return self::TOKEN_EXPIRED;
		}catch (\Firebase\JWT\SignatureInvalidException $e)
		{
			return self::TOKEN_INVALID;
		}catch(\Throwable $e)
		{
			return self::TOKEN_INVALID;
		}
		try{
			//User is valid, proceed with id
			$this->user_id = $decoded->user_id;
			
		}catch(ApiException $e)
		{
			return self::TOKEN_INVALID;
		}
		return self::TOKEN_VALID;
	}
	
	
	/**
	 * Get Access Token
	 * 
	 * @param string $user_id
	 * @return unknown
	 */
	protected function getAccessToken(string $user_id)
	{
		$issuedAt = time();
		$expirationTime = $issuedAt + 31536000;  // jwt valid for 1 year from the issued time
		$payload = array(
				'user_id' => $user_id,
				'iat' => $issuedAt,
				'exp' => $expirationTime
		);
		
		$jwt = JWT::encode($payload, $this->key, $this->alg);
		return $jwt;
	}
	
	
	/**
	 *
	 * @param array $users
	 * @return boolean
	 */
	protected function sendVerification(array $users)
	{
		
		$message = "Thanks for registering on ScrimmageSearch! Below is your verification code. Please enter the code in the app verification screen.";
		$message .= "<br /><b>" . $users['verification_code'];
		$message .= "</b>";
		
		$data['subject'] = "Confirm your registration";
		
		$msg['recipientFirstName'] = $users['first_name'];
		$msg['description'] = $message;
		$oParser = new Parser();
		$aEmailData = $oParser->doParse($msg, $this->getHtmlTemplate());
		$data['body'] = "Thanks for registering on ScrimmageSearch! Below is your verification code. Please enter the code in the app verification screen. \n" . $users['verification_code']. "\n\nRegards,\nScrimmage Search Team";
		$data['bodyHtml'] = $aEmailData;
		
		$mailer = new Mailer();
		$mailer->prepareEmail($users['email'], array('support@scrimmagesearch.com'=>'Scrimmage Search'), $data);
		$mailer->sendPreparedEmails();
	}
	
	/**
	 *
	 * @param array $users
	 * @return boolean
	 */
	protected function sendEmail(array $user, $message, $subject)
	{
		$data['subject'] = $subject;
		
		$msg['recipientFirstName'] = $user['first_name'];
		$msg['description'] = nl2br($message);
		$oParser = new Parser();
		$aEmailData = $oParser->doParse($msg, $this->getHtmlTemplate());
		$data['body'] = $message;
		$data['bodyHtml'] = $aEmailData;
		
		$mailer = new Mailer();
		$mailer->prepareEmail($user['email'], array('support@scrimmagesearch.com'=>'Scrimmage Search'), $data);
		$mailer->sendPreparedEmails();
	}
	
	public function getHtmlTemplate()
	{
		$str = <<<'EOD'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width"/>
				
    <style type="text/css">
    * { margin: 0; padding: 0; font-size: 100%; font-family: 'Avenir Next', "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif; line-height: 1.65; }
img { max-width: 100%; margin: 0 auto; display: block; }
body, .body-wrap { width: 100% !important; height: 100%; background: #f8f8f8; }
a { color: #71bc37; text-decoration: none; }
a:hover { text-decoration: underline; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.text-left { text-align: left; }
.button { display: inline-block; color: white; background: #2B7CB5; border: solid #2B7CB5; border-width: 10px 20px 8px; font-weight: bold; border-radius: 4px; }
.button:hover { text-decoration: none; }
.mark-text { display: inline-block; color: #333333; padding: 10px; background: #eeeeee; border-style: none; font-weight: regular; border-radius: 1px; }
h1, h2, h3, h4, h5, h6 { margin-bottom: 20px; line-height: 1.25; }
h1 { font-size: 32px; }
h2 { font-size: 28px; }
h3 { font-size: 24px; }
h4 { font-size: 20px; }
h5 { font-size: 16px; }
p, ul, ol { font-size: 16px; font-weight: normal; margin-bottom: 20px; }
.container { display: block !important; clear: both !important; margin: 0 auto !important; max-width: 580px !important; }
.container table { width: 100% !important; border-collapse: collapse; }
.container .masthead { padding: 40px 0; background: #56DE00; color: white; }
.container .masthead h1 { margin: 0 auto !important; max-width: 90%; text-transform: uppercase; }
.container .content { background: white; padding: 30px 35px; }
.container .content.footer { background: none; }
.container .content.footer p { margin-bottom: 0; color: #888; text-align: center; font-size: 14px; }
.container .content.footer a { color: #888; text-decoration: none; font-weight: bold; }
.container .content.footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<table class="body-wrap">
    <tr>
        <td class="container">
				
            <!-- Message start -->
            <table>
                <tr>
                    <td align="center" class="masthead">
				
                        <img width="100" src="https://test.scrimmage.atakinteractive.com/defaultalpha.png"/>
				
                    </td>
                </tr>
                <tr>
                    <td class="content">
				
                        <h4>Hi {%recipientFirstName%},</h4>
						<p>you have new alert on ScrimmageSearch</p>
				
                        <p class="mark-text">{%description%}</p>
				
                        
				
                        <p>Regards,<br />
						ScrimmageSearch Team</p>
				
                        <p><em></em></p>
				
                    </td>
                </tr>
            </table>
				
        </td>
    </tr>
    <tr>
        <td class="container">
				
            <!-- Message start -->
            <table>
                <tr>
                    <td class="content footer" align="center">
                        <p>Sent by <a href="">Scrimmage Search</a></p>
                    </td>
                </tr>
            </table>
				
        </td>
    </tr>
</table>
</body>
</html>
EOD;
		return $str;
	}
}