<?php
namespace Workmark\User;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use Workmark\Model\Users;
use Workmark\Mailer\Mailer;
use Workmark\Mailer\Parser;

/**
 * Adapter used for authenticating user. It takes login and password on input
 * and checks the database if there is a user with such login (email) and password.
 * If such user exists, the service returns his identity (email). The identity
 * is saved to session and can be retrieved later with Identity view helper provided
 * by ZF3.
 */
class User 
{
	/**
	 * User model.
	 * @var Users
	 */
	private $model;
	
	const STATUS_ACTIVE = 'ACTIVE';
	const STATUS_DEACTIVATED = 'DEACTIVATED';
	
	const ROLE_MEMBER = 'MEMBER';
	const ROLE_MANAGER = 'MANAGER';
	const URL_IMAGE_DEFAULT = "http://coachapp/default.png";
	
	const DEVICE_IOS = 'ios';
	const DEVICE_ANDROID = 'and';
	
	
	/**
	 * Instance holder
	 *
	 * @var User
	 */
	protected static $instance;
	protected $userRow = null;
	
	/**
	 * Constructor.
	 */
	public function __construct($model)
	{
		$this->model = $model;
	}  
		
	/**
	 * 
	 */
	public function init($id)
	{
		//var_dump($id);
		if (!is_numeric($id)) {
			$userRecord = $this->getModel()->getByEmail($id);
		}else{
			$userRecord = $this->getModel()->getById($id);
		}
		$this->userRow = $userRecord;
	}
	
	/**
	 * @return string
	 */
	public function getMapper() {
		return $this->userRow;
	}
	
	/**
	* @return string
	*/
	public function getModel() {
		return $this->model;
	}
	
	/**
	 * @return string
	 */
	public function getMyData() {
		$row = $this->getMapper();
		unset($row['password']);
		return $row;
	}
	
	/**
	 * @return string
	 */
	public function getImageSrc() {
		return self::getImage($this->userRow);
	}
	
	/**
	 * @return string
	 */
	public function getId() {
		return $this->getMapper()['id'];
	}
	
	/**
	 * @return string
	 */
	public function getFirstName() {
		return $this->getMapper()['first_name'];
	}
	
	/**
	 * @return string
	 */
	public function getLastName() {
		return $this->getMapper()['last_name'];
	}
	
	/**
	 * @return string
	 */
	public function getBio() {
		return $this->getMapper()['bio'];
	}
	
	/**
	 * @return string
	 */
	public function getFullName() {
		return $this->getMapper()['first_name']. ' ' . $this->getMapper()['last_name'];
	}
	
	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->getMapper()['email'];
	}
	
	/**
	 * @return string
	 */
	public function getStatus() {
		return $this->getMapper()['status'];
	}
	
	/**
	 * @return string
	 */
	public function getDefaultResource() {
		return $this->getMapper()['defaultResource'];
	}
	
	/**
	 * @return string
	 */
	public function getCurrentOrg() {
		$org = $this->model->model->get(\Workmark\Model\Organisations::class)->getById($this->getMapper()['organisationId']);
		return $org;
	}
	
	/**
	 * @return array
	 */
	public static function getRoles() {
		return array(self::ROLE_MEMBER, self::ROLE_MANAGER);
	}
	
	/**
	 * @return string
	 */
	public function getRole() {
		//die(var_dump($this->getMapper()));
		if(!$this->getMapper()['role'])
		{
			return self::ROLE_MEMBER;
		}
		return $this->getMapper()['role'];
	}
	
	/**
	 *
	 * @return array
	 */
	public function getDevice(){
		return substr($this->getMapper()['device_token'], 0, 3);
	}
	
	/**
	 *
	 * @return array
	 */
	public function getDeviceToken(){
		return substr($this->getMapper()['device_token'], 4);
	}
	
	/**
	 * @return boolean
	 */
	public function isManager() {
		return $this->getRole() == self::ROLE_MANAGER?true:false;
	}
	
	/**
	 * @return boolean
	 */
	public function persist() {
		return $this->userRow?true:false;
	}
	
	
	
	public function sendPush($msg)
	{             # <- Your Bundle ID
		if($this->getDevice() == User::DEVICE_IOS)
		{
			$this->sendIosPush($user, $msg);
		}elseif($this->getDevice() == User::DEVICE_ANDROID){
			$this->sendAndPush($user, $msg);
		}
	}
	
	private function sendIosPush($msg)
	{
		$token = $this->getDeviceToken();
		$keyfile = ROOT_PATH.'AuthKey_9ND2HSVBVJ.p8';               # <- Your AuthKey file
		$keyid = '9ND2HSVBVJ';                            # <- Your Key ID
		$teamid = 'Y266NUKF5C';                           # <- Your Team ID (see Developer Portal)
		$bundleid = 'com.sei.coachApp';                # <- Your Bundle ID
		$url = 'https://api.push.apple.com';  # <- development url, or use http://api.development.push.apple.com for production environment
		
		$message = '{"aps":{"alert":"'.$msg.'","sound":"default","badge":1}}';
		
		$key = openssl_pkey_get_private('file://'.$keyfile);
		
		$header = ['alg'=>'ES256','kid'=>$keyid];
		$claims = ['iss'=>$teamid,'iat'=>time()];
		
		$header_encoded = base64($header);
		$claims_encoded = base64($claims);
		
		$signature = '';
		openssl_sign($header_encoded . '.' . $claims_encoded, $signature, $key, 'sha256');
		$jwt = $header_encoded . '.' . $claims_encoded . '.' . base64_encode($signature);
		//die($jwt);
		// only needed for PHP prior to 5.5.24
		if (!defined('CURL_HTTP_VERSION_2_0')) {
			define('CURL_HTTP_VERSION_2_0', 3);
		}
		
		$full_url = $url."/3/device/".$token;
		
		$http2ch = curl_init();
		
		curl_setopt($http2ch, CURLOPT_POST, TRUE);
		curl_setopt($http2ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
		curl_setopt($http2ch, CURLOPT_POSTFIELDS, $message);
		curl_setopt($http2ch, CURLOPT_HTTPHEADER, array(
				"apns-topic: {$bundleid}",
				"authorization: bearer $jwt"
		));
		curl_setopt($http2ch, CURLOPT_URL, $full_url);
		curl_setopt($http2ch, CURLOPT_PORT, 443);
		curl_setopt($http2ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($http2ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($http2ch, CURLOPT_HEADER, 1);
		
		$result = curl_exec($http2ch);
		if ($result === FALSE) {
			throw new Exception("Curl failed: ".curl_error($http2ch));
		}
		
		$status = curl_getinfo($http2ch, CURLINFO_HTTP_CODE);
		//echo $status;
		
		return $result;
	}
	
	function base64($data) {
		return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
	}
	
	private function sendAndPush($msg)
	{
		$firebase_api = "AAAAN53TOQA:APA91bHWGx0hIvmiuURd1rFQaqmOAqv470xA-TpRKyWpMptTGPh4qYsE9V9h9EmdRQyBNAsKmz8EbmMHo-Y0U0ensVtzez2aV9gtd9ZBLxeOo0cXCA_mS5vu1KmX0k80JN9U7Yv3Wu0n";
		$notification = array();
		$notification['title'] = "Scrimmage Search";
		$notification['message'] = $msg;
		$notification['image'] = "ddd";
		$notification['badge'] = "1";
		$notification['click_action'] = "com.com.lepsha.lepsha.MainActivity";
		$notification['action_destination'] = "com.com.lepsha.lepsha.MainActivity";
		
		
		$fields = array(
				'to' => $this->getDeviceToken(),
				'data' => $notification,
		);
		
		
		// Set POST variables
		$url = 'https://fcm.googleapis.com/fcm/send';
		
		$headers = array(
				'Authorization: key=' . $firebase_api,
				'Content-Type: application/json'
		);
		
		// Open connection
		$ch = curl_init();
		
		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);
		
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		// Disabling SSL Certificate support temporarily
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		
		
		$result = curl_exec($ch);
		if ($result === FALSE) {
			throw new \Exception("Curl failed: ".curl_error($ch));
		}
		
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		//var_dump($status);
		
		//die(var_dump($fields));
		
		return $result;
	}
	
	/**
	 * @return boolean
	 */
	public function sendNotification(array $msg) {
		
		$data['subject'] = $msg['description'];
		
		$oParser = new Parser();
		$aEmailData = $oParser->doParse($msg, $this->getHtmlTemplate());
		$data['body'] = "Here's what's new... \n" . $msg['description'] . "\n" . $msg['url'];
		$data['bodyHtml'] = $aEmailData;
		
		$mailer = new Mailer();
		$mailer->prepareEmail($this->getEmail(), array('notifications@lepsha.com'=>'WORKMARK'), $data);
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
h1, h2, h3, h4, h5, h6 { margin-bottom: 20px; line-height: 1.25; }
h1 { font-size: 32px; }
h2 { font-size: 28px; }
h3 { font-size: 24px; }
h4 { font-size: 20px; }
h5 { font-size: 16px; }
p, ul, ol { font-size: 16px; font-weight: normal; margin-bottom: 20px; }
.container { display: block !important; clear: both !important; margin: 0 auto !important; max-width: 580px !important; }
.container table { width: 100% !important; border-collapse: collapse; }
.container .masthead { padding: 40px 0; background: #2B7CB5; color: white; }
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

                        <img width="100" src="http://workmark.lepsha.com/assets/img/big-logo.png"/>

                    </td>
                </tr>
                <tr>
                    <td class="content">

                        <h4>Hi {%recipientFirstName%}, here's what's new...</h4>

                        <p>{%description%}</p>

                        <table>
                            <tr>
                                <td align="center">
                                    <p>
                                        <a href="{%url%}" class="button">View more..</a>
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <p></p>

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
                        <p>Sent by <a href="http://workmark.lepsha.com">Workmark</a></p>
                        <p><a href="http://workmark.lepsha.com/account/log/in">Unsubscribe</a></p>
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
	
	/**
	 * return string
	 */
	public static function getName(array $d)
	{
		if (!isset($d['email']))
		{
			return "";
		}
		$parts = explode("@", $d['email']);
		$fn = $d['first_name'] ?? $parts[0];
		$ln = $d['last_name'] ?? '';
		return $fn . ' ' . $ln;
	}
	
	public static function getFirstNameStatic(array $d) {
		if (!isset($d['email']))
		{
			return "";
		}
		$parts = explode("@", $d['email']);
		$fn = $d['first_name'] ?? $parts[0];
		return $fn;
	}
	
	/**
	 * return string
	 */
	public static function getInitials($email)
	{
		$res = "";
		$parts = explode("@", $email);
		$username = $parts[0];
		if (strpos($username, ".") !== false)
		{
			$initials = explode(".", $username);
			$res = $initials[0][0] . $initials[1][0];
		}else{
			$res = $username[0];
		}
		return strtoupper($res);
	}
	
	public static function getImage($d) {
		if (!isset($d['email']))
		{
			return "";
		}
		if (isset($d['fbId']) && !empty($d['fbId']))
		{
			return "https://graph.facebook.com/".$d['fbId']."/picture";
		}else{
	 
		    //return self::URL_IMAGE_DEFAULT;
		    
		}
		
		$initials = self::getInitials($d['email']);
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" pointer-events="none" width="44" height="44"
							 style="background-color:#DEE2E3; width: 44px; height:44px; border-radius: 0px; -moz-border-radius: 0px;">
							<text text-anchor="middle" y="50%" x="50%" dy="0.35em" pointer-events="auto" fill="#9C9C9C" font-family="HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica, Arial,Lucida Grande, sans-serif" style="font-weight: 300; font-size: 30px;">'.$initials.'</text>
							</svg>';
		$imgSrc = 'data:image/svg+xml;base64,'.base64_encode($svg);
		return $imgSrc;
	}
	
	public static function getImagePath($d) {
		if (is_readable(ROOT_PATH . '/data/images/' . $d['id'] . '.jpg'))
		{
			return "https://kisskissbangbang.me/account/log/show?id=".$d['id'];
		}
		if (isset($d['fbId']) && intval($d['fbId'])>0)
		{
			return "https://graph.facebook.com/".$d['fbId']."/picture?type=large";
		}
		return "https://kisskissbangbang.me/img/no_user.jpg";
	}
	
	/**
	 * @return string
	 */
	public function createUser($data) {
		//die(var_dump($data['last_name']));
		$user['first_name'] = $data['first_name'];
		$user['last_name'] = $data['last_name'];
		$user['email'] = $data['email'];
		$user['fbId'] = $data['fbId'] ?? null;
		$user['ggId'] = $data['ggId'] ?? null;
		$user['password'] = md5(time());
		//$user['createdOn'] = date('YY-mm-dd');
		$user['lastLogin'] = date('YY-mm-dd');
		$user['status'] = 'ACTIVE';
		try {
			$id =  $this->getModel()->save($user);
		}catch (\Exception $e)
		{
			//return $e->getMessage();
			return false;
		}
		$this->init($id);
		return $this;
	}
	
	/**
	 * Returns an instance of the currently logged in user
	 *
	 * @static
	 * @return User
	 *
	 * @since         2013-07-15
	 * @author        Arsen
	 */
	public static function setCurrent(User $user)
	{
		if (!$user->persist()) {
			throw new \Exception('You cannot set non existing user as current');
		}
		self::$instance = $user;
	}
	
	/**
	 * Returns an instance of the currently logged in user
	 *
	 * @static
	 * @return User
	 *
	 * @since         2013-07-15
	 * @author        Arsen
	 */
	public static function getCurrent()
	{
		return self::$instance;
	}
}
