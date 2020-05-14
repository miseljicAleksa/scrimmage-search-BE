<?php
namespace SEI;

use SEI\ApiException;
use Firebase\JWT\JWT;
use SEI\Models\Users;
use SEI\Models\Player;
use SEI\Models\Officials;
use SEI\Models\Team;
use SEI\Models\Games;
use SEI\Models\Requests;
use SEI\Models\Messages;
use Workmark\Misc;
use SEI\Models\Notifications;
use SEI\Models\Teams;
use SEI\Models\Players;
use Zend\Http\PhpEnvironment\Request;
use Workmark\User\User;
/**
 * CoachApp specific set of APIs
 * @author arsenleontijevic
 * @since 30.09.2019
 */
class CoachAPI extends Api {
	
	
	const DIR_UPLOADS = "/data/uploads/";
	const DIR_COACH_IMAGE = "coach";
	const DIR_TEAM_IMAGE = "team";
	const DIR_TEAM_LOGO = "logo";
	const DIR_PLAYER_IMAGE = "player";
	const DIR_OFFICIAL_IMAGE = "official";
	
	
	/**
	 * List of allowed actions
	 * @var array
	 */
	private $allwedActions = ['login', 'signup', 'setCoachAccount', 'setPlayerAccount','getTeams', 'resetPassword',
			'getOfficials', 'getPlayers', 'getSports', 'getGames', 'getRequests',  'getNotifications',  'getMessages',
			'getUsers', 'verifyAccount','setCoachExperience', 'setOfficialAccount', 'resendCode', 'searchTeams', 
			'getTeamDetails', 'getOfficialDetails', 'getGamesHistory', 'connectToTeam', 'searchOfficials', 'getRefereedHistory',
	       'rateTeam', 'rateGame', 'searchPlayers', 'searchGames', 'connectToPlayer' , 'connectToGame', 'getRequests', 'getMessages',
	       'sendMessage', 'saveProfile', 'setRequestStatus', 'setDeviceToken', 'testPush', 'connectToOfficial', 'getAvailableTeams',
			'postGame'
	];
	
	/**
	 * Instantiate CoachApp Api
	 * 
	 * @param mixed \CI_DB_driver | Other Adapters $db
	 * @param array $request
	 * @param \Firebase\JWT\JWT $jwt
	 */
	public function __construct($db, array $request, JWT $jwt)
	{
		parent::__construct($db,  $request, $jwt);
	}
	
	
	
	/**
	 * Analize request params
	 *
	 * @param array $request
	 */
	protected function processRequest(array $request)
	{
		
		if (!isset($request['action'])) {
			throw new ApiException("The action param is required");
		}
		
		//Do not check acces_token for login and register actions
		if(!in_array($request['action'], ['login', 'signup']) )
		{
			if (!isset($request['access_token'])) {
				throw new ApiException("The access_token param is required");
			}
			
			$decoded = $this->checkAccessToken($request['access_token']);
			if ($decoded != self::TOKEN_VALID) {
				throw new ApiException("The access_token is not valid");
			}
		}
		
		if(in_array($request['action'], $this->allwedActions))
		{
			$action = $request['action'];
			$this->setResponse($this->$action());
		}else{
			$this->setResponse($this->formatResponse("fail", "Unknown action", array()));
		}
		
	}
	
	
	/**
	 * API login, returns logged in user with access_token
	 * Expecting $_REQUEST params ('email', 'password')
	 * @return array
	 */
	private function login()
	{
		$request = $this->filterParams(['email', 'password']);
		
		$user_model = new Users($this->dbAdapter);
		$users = $user_model->getByEmail($request['email']);
		
		if (!isset($users[0])) {
			return $this->formatResponse(self::STATUS_FAILED, "-1");
		}
		$user = (object) $users[0];
		
		if (password_verify($request['password'], $user->password)) {
			unset($user->password);
			$user->access_token = $this->getAccessToken($user->id);
			//$user->image = $this->getDefaultImage();
			return $this->formatResponse(self::STATUS_SUCCESS, "", $user);
		}
		return $this->formatResponse(self::STATUS_FAILED, "-1");
	}
	
	
	/**
	 * 
	 * @return array string of 4 digits
	 */
	private function resendCode()
	{
	    $user_model = new Users($this->dbAdapter);
	    $users = $user_model->getById($this->user_id);
	    //Send confirmation mail to user
	    try {
	    	$this->sendVerification($users[0]);
	    }catch (\Exception $e)
	    {
	    	return $this->formatResponse(self::STATUS_FAILED, $e->getMessage() . ' ' . $e->getTraceAsString(), []);
	    }
	    
	    if (!isset($users[0])) {
	        return $this->formatResponse(self::STATUS_FAILED, "-1");
	    }
	    
	    return $this->formatResponse(self::STATUS_SUCCESS, "");
	}
	
	
	/**
	 * return json of notfications
	 */
	private function getNotifications(){
		$notifications_model = new Notifications($this->dbAdapter);
		$notifications = $notifications_model->getNotifications(array('user_to'=>$this->user_id));
		return $this->formatResponse(self::STATUS_SUCCESS, " ", $notifications);
		
	}
	
	

	
	/**
	 * API messages, returns messages in one conversation between two users
	 * Expecting $_REQUEST params ('user_from', 'password')
	 * return json of messages
	 */
	private function getConversations(){
		$messages_model = new Messages($this->dbAdapter);
		$messages = $messages_model->getMessages(['conversations_user'=>$this->user_id]);
		return $this->formatResponse(self::STATUS_SUCCESS, " ", $messages);
		
	}
	
	
	/**
	 * API signup, returns logged in user with access_token
	 * Expecting $_REQUEST params ('email', 'password')
	 * @return array
	 */
	private function signup()
	{
	    $request = $this->filterParams(['first_name', 'last_name',  'email', 'password','mobile_number', 'zip_code', 'year_born', 'profile_type', 'sport']);
		
		$user_model = new Users($this->dbAdapter);
		$users = $user_model->getByEmail($request['email']);
		
		$request['verification_code'] = (string)$this->getVerificationCode();
		
		
		if (isset($users[0])) {
			//Return fail, user exists
			return $this->formatResponse(self::STATUS_FAILED, "0");
		}
		$password = password_hash($request['password'], PASSWORD_BCRYPT);
		$request['password'] = $password;
		$user_id = $user_model->signup($request);
		
		if (intval($user_id) > 0) {
		    //Get newly created user from db
		    $users = $user_model->getById($user_id);
		    unset($users[0]['password']);
		    $user = (object) $users[0];
			$user->access_token = $this->getAccessToken($user_id);
			//Send confiramtion mail to user
			$this->sendVerification($users[0]);
			return $this->formatResponse(self::STATUS_SUCCESS, "", $user);
		}
		//Return fail, unable to register user
		return $this->formatResponse(self::STATUS_FAILED, "-1");
		
	}
	
	/**
	 * API setCoachExperience, returns u
	 * Expecting
	 * @return array
	 */
	private function setCoachExperience()
	{
		//Filter request
	    $request = $this->filterParams(['experience_1', 'experience_2', 'session_plan']);
		
	    //Instanitiate model
		$team_model = new Teams($this->dbAdapter);
		
		//Get last team, array will be returned
		$last_team = $team_model->getTeams(['user_id'=>$this->user_id, 'last'=>true]);
		
		//Check if at least one team is retured (first element of array)
		if(!isset($last_team[0]))
		{
			return $this->formatResponse(self::STATUS_FAILED, "There is no team for current user", []);
		}
		//Set value to fields
		$last_team[0]['experience_1'] = $request['experience_1'];
		$last_team[0]['experience_2'] = $request['experience_2'];
		$last_team[0]['session_plan'] = $request['session_plan'];
		unset($last_team[0]['team_rate']);
		unset($last_team[0]['coach_name']);
		
		
		$updated_team = $team_model->update($last_team[0]);
		
		//Convert to object
		$updated_team = (object) $updated_team;
		
		//Return response
		return $this->formatResponse(self::STATUS_SUCCESS, "", $updated_team);
		
		
	}
	
	
	/**
	 * 
	 * @return array|unknown[]|string[]
	 */
	private function setCoachAccount()
	{
	    
	    $request = $this->filterParams(['lon', 'lat', 'coach_image', 'team_gender','team_name', 'age_group','team_colors', 'team_description', 'team_logo', 'team_image']);
	    
	    //Save team image
	    $this->saveImage(self::DIR_TEAM_IMAGE, $this->user_id.".png", $request['team_image']);
	    //Save team logo
	    $this->saveImage(self::DIR_TEAM_LOGO, $this->user_id.".png", $request['team_logo']);
	    //Save coach image
	    $this->saveImage(self::DIR_COACH_IMAGE, $this->user_id.".png", $request['coach_image']);
	    
	    $data = [
	        
	        "team_gender"=>$request['team_gender'],
	        "team_name"=>$request['team_name'],
	        "age_group"=>$request['age_group'],
    		"team_colors"=>$request['team_colors'],
    		"lon"=>$request['lon'],
    		"lat"=>$request['lat'],
	        //@todo We should probably remove these fields from database if there'll be one kind of image per user
	        //"teamImage"=>$this->getTeamImageURL(),
	        //"teamLogo"=>$this->getTeamLogoURL(),
	        //"coachImage"=>$this->getCoachImageURL(),
	        "team_description"=>$request['team_description'],
	    	"user_id"=>$this->user_id,
	    	"sport_id"=>1 //To fix
	        
	    ];
	    
	    
	    
	    $team_model = new Teams($this->dbAdapter);
	    $team = $team_model->insert($data);
	    
	    $team = (object) $data;
	    
	    return $this->formatResponse(self::STATUS_SUCCESS, "", $team);
	    
	    
	}
	

	private function saveImage($dir, $file_name, $base64_string)
	{
		if(!$base64_string)
		{
			throw new \Exception("base64_string is empty");
		}
		$image_string = $this->base64UrlDecode($base64_string);
		$image = imagecreatefromstring($image_string);
		$upload_dir = ROOT_PATH . self::DIR_UPLOADS . $dir . '/';
		$upload_path = $upload_dir.$file_name;
		
		//Create dir if not exists
		if(!is_dir($upload_dir)){
			mkdir($upload_dir, 0777, true);
		}
		
		imagesavealpha($image, true);
		imagepng($image, $upload_path);
		
	}
	
	
	/**
	 * API setPlayerAccount, returns $player
	 * Expecting $_REQUEST params ('player_image', 'years_of_experience', 'team_name', 'interested_in', 'player_description')
	 * @return array
	 */
		
	private function setPlayerAccount()
	{
	    $request = $this->filterParams(['lon', 'lat', 'player_image','years_of_experience', 'team_name', 'interested_in', 'player_description','miles_radius']);
	    
	    //Save coach image
	    $this->saveImage(self::DIR_PLAYER_IMAGE, $this->user_id.".png", $request['player_image']);
	    
	    $data = [
	        "years_of_experience"=>$request['years_of_experience'],
	        "team_name"=>$request['team_name'],
	        "interested_in"=>$request['interested_in'],
    		"player_description"=>$request['player_description'],
    		"lon"=>$request['lon'],
    		"lat"=>$request['lat'],
	        "user_id"=>$this->user_id
	    ];

	    
	    $player_model = new Players($this->dbAdapter);
	    $player = $player_model->insert($data);
	    
	    $player = (object) $data;
	    
	    return $this->formatResponse(self::STATUS_SUCCESS, "", $player);
	    
	    
	}
	
	
	/**
	 * API setOfficialAccount, returns $official
	 * Expecting $_REQUEST params ('official_image','sertificate', 'years_of_experience', 'officiating_fee', 'official_description','miles_radius'])
	 * @return array
	 */
	private function setOfficialAccount()
	{
		$request = $this->filterParams(['lon', 'lat', 'official_image','sertificate', 'years_of_experience', 'officiating_fee', 'official_description','miles_radius']);
	    
	    //Save coach image
	    $this->saveImage(self::DIR_OFFICIAL_IMAGE, $this->user_id.".png", $request['official_image']);
	    
	    $data = [
	        
	        "years_of_experience"=>$request['years_of_experience'],
	        "sertificate"=>$request['sertificate'],
	        "officiating_fee"=>$request['officiating_fee'],
    		"official_description"=>$request['official_description'],
    		"lon"=>$request['lon'],
    		"lat"=>$request['lat'],
	        //"miles_radius"=>$request['miles_radius'],
	        "user_id"=>$this->user_id
	        
	        
	    ];
	    
	    
	    $official_model = new Officials($this->dbAdapter);
	    $official = $official_model->insert($data);
	    
	    $official = (object) $data;
	    
	    return $this->formatResponse(self::STATUS_SUCCESS, "", $official);
	    
	    
	}
	

	
	/**
	 * API verifyAccount from model SEI/model/Users
	 * Expecting $_REQUEST params []
	 * @return json representation of code 
	 */
	private function verifyAccount()
	{
		$request = $this->filterParams(['code']);
		$user_model = new Users($this->dbAdapter);
		$users = $user_model->getById($this->user_id);
		if (!isset($users[0])) {
			//Return fail, user does not exists
			return $this->formatResponse(self::STATUS_FAILED, "0");
		}
		$user = (object) $users[0];
		
		if ($user->verification_code == $request['code'])
		{
			return $this->formatResponse(self::STATUS_SUCCESS, "", $user);
		}
		return $this->formatResponse(self::STATUS_FAILED, "Wrong code");
	}
	
	/**
	 * API getTeams from model SEI/model/Team
	 * Expecting $_REQUEST params []
	 * @return json set of all teams 
	 */
	private function getTeams()
	{
		$data = [];
		
		$teams_model = new Team($this->dbAdapter);
		$teams = $teams_model->getTeams($data);
		return $this->formatResponse(self::STATUS_SUCCESS, "", $teams);
		
	}
	
	/**
	 * API getPlayers from model SEI/model/Player
	 * Expecting 0 args
	 * @return json set of all players
	 */
	private function getPlayers()
	{
	 
	    $players_model = new Player($this->dbAdapter);
	    $players = $players_model->getPlayers();
	    return $this->formatResponse(self::STATUS_SUCCESS, "", $players);
	    
	}
	

	/**
	 * API getRequests from model SEI/model/Requests
	 * Expecting 0 args
	 * @return json set of all requests
	 */
	private function getRequests()
	{
	    
	    $availabilty_model = new Requests($this->dbAdapter);
	    $available = $availabilty_model->getRequests(array("created_by"=>$this->user_id));
	    return $this->formatResponse(self::STATUS_SUCCESS, "", $available);
	    
	}
	
	/**
	 * API getGames from model SEI/model/Scrimmages
	 * Expecting 0 args
	 * @return json set of all scrimmages
	 */
	private function getGames()
	{
	    
	    $players_model = new Games($this->dbAdapter);
	    $players = $players_model->getPlayers();
	    return $this->formatResponse(self::STATUS_SUCCESS, "", $players);
	    
	}
	
	/**
	 * API getOfficials from model SEI/model/Officials
	 * Expecting 0 args
	 * @return json set of all officials
	 */
	private function getOfficials()
	{
	    
	    $officials_model = new Officials($this->dbAdapter);
	    $officials = $officials_model->getOfficials();
	    return $this->formatResponse(self::STATUS_SUCCESS, "", $officials);
	    
	}
	
	
	
	
	/**
	 * API resetPassword
	 * Expecting $_REQUEST params ('email', 'access_token')
	 * @return array
	 */
	private function resetPassword()
	{
		$request = $this->filterParams(['email']);
		
		$user_model = new Users($this->dbAdapter);
		$users = $user_model->getByEmail($request['email']);
		if (!isset($users[0])) {
			return $this->formatResponse(self::STATUS_FAILED, "-1");
		}
		return $this->formatResponse(self::STATUS_SUCCESS);
		
	}
	
	/**
	 * return four digit verification code
	 */
	private function getVerificationCode()
	{
		return rand(1000, 9999);
	}
	
	/**
	 * Get team image
	 */
	private function getTeamImageURL($user_id)
	{

	    $file_path = ROOT_PATH . self::DIR_UPLOADS . '/team/' . $user_id. '.png';
	    if (file_exists($file_path))
		{
		    return SITE_URL . '/application/index/show?type=team&id=' . $user_id. '.png';

		}
		return SITE_URL . '/default.png';
	}
	
	private function getPlayerImageURL($user_id)
	{
	    
	    $file_path = ROOT_PATH . self::DIR_UPLOADS . '/player/' . $user_id. '.png';
	    if (file_exists($file_path))
	    {
	        return SITE_URL . '/application/index/show?type=player&id=' . $user_id. '.png';
	        
	    }
	    return SITE_URL . '/default.png';
	}
	
	/**
	 * Get team logo
	 */
	private function getTeamLogoURL($user_id)
	{

		$file_path = ROOT_PATH . self::DIR_UPLOADS . '/logo/' . $user_id. '.png';
		if (file_exists($file_path))
		{
			return SITE_URL . '/application/index/show?type=logo&id=' . $user_id. '.png';
		}
		return SITE_URL . '/default.png';
	}
	
	/**
	 * Get official logo
	 */
	private function getOfficialLogoURL($user_id)
	{
		
		$file_path = ROOT_PATH . self::DIR_UPLOADS . '/' . self::DIR_OFFICIAL_IMAGE . '/' . $user_id. '.png';
		if (file_exists($file_path))
		{
			return SITE_URL . '/application/index/show?type=' . self::DIR_OFFICIAL_IMAGE . '&id=' . $user_id. '.png';
		}
		return SITE_URL . '/default.png';
	}
	
	
	/**
	 * API getTeams from model SEI/model/Team
	 * Expecting $_REQUEST params []
	 * @return json set of searched teams
	 */
	private function searchTeams()
	{

	    $request = $this->filterParams(['lon','lat','home_field_available', 'looking_date', 'flexible_on_date', 'day_period', 'flexible_on_time', 'need_players', 'miles_radius']);
	    $data = [];
	    $teams_model = new \SEI\Models\Teams($this->dbAdapter);
	    $teams = $teams_model->getTeams($request);
	    $teams_formated = [];
	    foreach ($teams as $team)
	    {

	    	$team['team_image'] = $this->getTeamImageURL($team['user_id']);
	    	$team['team_logo'] = $this->getTeamLogoURL($team['user_id']);
	    	
	    	$dist = Misc::distance($request['lat'],$request['lon'], $team["lat"],$team["lon"]);
	    	$team['distance'] = round($dist, 2);
	    	$teams_formated[] = $team;
	    }	    
	    return $this->formatResponse(self::STATUS_SUCCESS, "", $teams_formated);
	}
	
	
	
	
	/**
	 * API searchPlayers from model SEI/model/Player
	 * Expecting $_REQUEST params []
	 * @return json set of searched teams
	 */
	private function searchPlayers()
	{
	    
	    $request = $this->filterParams(['lon','lat','home_field_available', 'looking_date', 'flexible_on_date', 'day_period', 
	        'flexible_on_time', 'need_players', 'miles_radius']);
	    $data = [];
	    $players_model = new Players($this->dbAdapter);
	    $players = $players_model->getPlayers($request);
	    $players_formated = [];
	    foreach ($players as $player)
	    {
	        
	        $player['player_image'] = $this->getPlayerImageURL($player['user_id']);
	        
	        
	        $dist = Misc::distance($request['lat'],$request['lon'], $player["lat"],$player["lon"]);
	        $player['distance'] = round($dist, 2);
	        $player['zip_code'] = "1312";
	        
	        $players_formated[] = $player;
	    }
	    return $this->formatResponse(self::STATUS_SUCCESS, "", $players_formated);
	}
	
	
	
	/**
	 * API searchGames from model SEI/model/Games
	 * Expecting $_REQUEST params []
	 * @return json set of searched teams
	 */
	private function searchGames()
	{
	    
	    $request = $this->filterParams(['lon','lat','home_field_available', 'looking_date', 'flexible_on_date', 'day_period',
	        'flexible_on_time', 'need_players', 'miles_radius']);
	    $data = [];
	    $games_model = new Games($this->dbAdapter);
	    $games = $games_model->getGames($data);
	    $games_formated = [];
	    foreach ($games as $game)
	    {
	   
	        $dist = Misc::distance($request['lat'],$request['lon'], $game["lat"],$game["lon"]);
	        $game['distance'] = round($dist, 2);
	        
	        $game['coach_name'] = "";
	        
	        
	        $games_formated[] = $game;
	    
	    }
	    return $this->formatResponse(self::STATUS_SUCCESS, "", $games_formated);
	}
	
	
	private function postGame()
	{
		
		$request = $this->filterParams(['lon','lat','sport_id','date_and_time', 'address', 'city', 'state',
				'zip_code', 'team_1_id', 'team_2_id', 'fee', 'negotiable']);
		$data = [];
		$games_model = new Games($this->dbAdapter);
		$request['created_by'] = $this->user_id;
		$id = $games_model->insert($request);
		$request['id'] = $id;
		return $this->formatResponse(self::STATUS_SUCCESS, $request);
	}
	
	
	/**
	 * Get team image
	 */
	private function getCoachImageURL()
	{
		$file_path = ROOT_PATH . self::DIR_UPLOADS . '/coach/' . $this->user_id . '.png';
		if (file_exists($file_path))
		{
			return SITE_URL . '/application/index/show?type=coach&id=' . $this->user_id . '.png';
		}
		return SITE_URL . '/default.png';
	}
	
	/**
	 * Get details about requested team
	 * Required params ['id']
	 * @return array
	 */
	private function getTeamDetails()
	{
	    $request = $this->filterParams(['id']);
	    
	    $team_model = new \SEI\Models\Teams($this->dbAdapter);
	    $team = $team_model->getById($request['id']);
	    
	    
	    if (!isset($team[0])) {
	        return $this->formatResponse(self::STATUS_FAILED, "-1");
	    }
	    
	    return $this->formatResponse(self::STATUS_SUCCESS, $team);
	}
	
	private function getAvailableTeams()
	{
		$team_model = new \SEI\Models\Teams($this->dbAdapter);
		$teams = $team_model->getAvailableTeams($this->user_id);
		return $this->formatResponse(self::STATUS_SUCCESS, "", $teams);
	}
	
	/**
	 * Rate team
	 * Required params ['team_id', 'rate']
	 * @return array
	 */
	private function rateTeam()
	{
		$request = $this->filterParams(['team_id', 'rate']);
		
		$data = [
				"team_id"=>$request['team_id'],
				"rate"=>$request['rate'],
				"created_by"=>$this->user_id
		];
		
		$model = new \SEI\Models\Rates($this->dbAdapter);
		$result = $model->insert($data);
		return $this->formatResponse(self::STATUS_SUCCESS, $result);
	}
	
	
	/**
	 * Rate team
	 * Required params ['team_id', 'rate']
	 * @return array
	 */
	private function rateGame()
	{
	    $request = $this->filterParams(['game_id', 'rate']);
	    
        $games_model = new Games($this->dbAdapter);
        $game = $games_model->getById($request['game_id']);
        $game['rate'] = $request['rate'];        
	    $games_model->update($game[0]);	    
	    return $this->formatResponse(self::STATUS_SUCCESS, $game);
	}
	
	/**
	 * @return game history based on team id
	 */
	private function getGamesHistory()
	{
	    $request = $this->filterParams(['team_id']);
	    $model = new Games($this->dbAdapter);
	    $result = $model->getGames();
	    
	    return $this->formatResponse(self::STATUS_SUCCESS, "", $result);
	}
	
	/**
	 * 
	 * @return send request to connect with other team in order to chat 
	 */
	private function connectToTeam()
	{
	    $request = $this->filterParams(['team_id']);
	    
	    $team_model = new \SEI\Models\Teams($this->dbAdapter);
	    $results = $team_model->getById($request['team_id']);
	    $result = $results[0];
	    
	    //Get recipient user
	    $user_model = new Users($this->dbAdapter);
	    $recipient = $user_model->getById($result['user_id'])[0];
	    
	    $message = "Hi, I'm interested to get in touch with you.";
	    
	    //Save request
	    $dataRequest = ["team_id"=>$request['team_id'],
	    				"created_by"=>$this->user_id];
	    $request_model = new Requests($this->dbAdapter);
	    $request_id = $request_model->insert($dataRequest);
	    
	    return $this->processRequestAction($request_id, $message, $recipient, "New team request");
	}
	
	private function connectToPlayer()
	{
	    $request = $this->filterParams(['player_id']);
	    
	    $model = new \SEI\Models\Players($this->dbAdapter);
	    $results = $model->getById($request['player_id']);
	    $result = $results[0];
	    
	    //Get recipient user
	    $user_model = new Users($this->dbAdapter);
	    $recipient = $user_model->getById($result['user_id'])[0];
	    
	    $message = "Hi, we need a player for a game.";
	    
	    //Save request
	    $dataRequest = ["player_id"=>$request['player_id'],
	    				"created_by"=>$this->user_id];
	    $request_model = new Requests($this->dbAdapter);
	    $request_id = $request_model->insert($dataRequest);
	    
	    return $this->processRequestAction($request_id, $message, $recipient, "New player request");
	}
	
	
	
	private function connectToOfficial()
	{
		$request = $this->filterParams(['official_id']);
		
		$model = new \SEI\Models\Players($this->dbAdapter);
		$results = $model->getById($request['official_id']);
		$result = $results[0];
		
		//Get recipient user
		$user_model = new Users($this->dbAdapter);
		$recipient = $user_model->getById($result['user_id'])[0];
		
		$message = "Hi, we need a official for a game.";
		
		//Save request
		$dataRequest = ["official_id"=>$request['official_id'],
				"created_by"=>$this->user_id];
		$request_model = new Requests($this->dbAdapter);
		$request_id = $request_model->insert($dataRequest);
		
		return $this->processRequestAction($request_id, $message, $recipient, "New official request");
	}
	
	private function connectToGame()
	{
	    $request = $this->filterParams(['game_id']);
	    
	    $model = new \SEI\Models\Games($this->dbAdapter);
	    $results = $model->getById($request['game_id']);
	    $result = $results[0];
	    
	    //Get recipient user
	    $user_model = new Users($this->dbAdapter);
	    $recipient = $user_model->getById($result['user_id'])[0];
	    
	    $message = "Hi, I would like to talk to you regarding the game you posted.";
	    
	    //Save request
	    $dataRequest = ["game_id"=>$request['game_id'],
	    		"created_by"=>$this->user_id];
	    $request_model = new Requests($this->dbAdapter);
	    $request_id = $request_model->insert($dataRequest);
	    
	    return $this->processRequestAction($request_id, $message, $recipient, "New official request");
	}
	
	
	private function processRequestAction($request_id, $message, $recipient, $subject, $sendEmail = true)
	{
		if($recipient == false){
			$recipient = $this->getRecipientByRequestId($request_id);
		}
		
		//Save message
		$data = ["message"=>$message,
				"request_id"=>$request_id,
				"user_from"=>$this->user_id,
				"viewed"=>0];
		$model = new Messages($this->dbAdapter);
		$message_id = $model->insert($data);
		
		//Save notification
		$dataNotification = ["message"=>$message,
				"request_id"=>$request_id,
				"user_to"=>$recipient['id'],
				"viewed"=>0];
		$notifications_model= new Notifications($this->dbAdapter);
		$notification_id = $notifications_model->insert($dataNotification);
		
		//Send push and email
		if ($sendEmail){
		$this->sendEmail($recipient, $message, $subject);
		}
		$this->sendPush($message, $recipient);
		return $this->formatResponse(self::STATUS_SUCCESS, []);
	}
	
	private function getRecipientByRequestId($request_id)
	{
		$request_model = new Requests($this->dbAdapter);
		$requests = $request_model->getById($request_id);
		$request = $requests[0];
		if(!is_null($request['team_id']))
		{
			$model = new Teams($this->dbAdapter);
			$results = $model->getById($request['team_id']);
		}
		if(!is_null($request['official_id']))
		{
			$model = new Officials($this->dbAdapter);
			$results = $model->getById($request['official_id']);
		}if(!is_null($request['player_id']))
		{
			$model = new Players($this->dbAdapter);
			$results = $model->getById($request['player_id']);
		}if(!is_null($request['game_id']))
		{
			$model = new Games($this->dbAdapter);
			$results = $model->getById($request['game_id']);
		}
		$result = $results[0];
		
		//Get recipient user
		$user_model = new Users($this->dbAdapter);
		$recipient = $user_model->getById($result['user_id'])[0];
		return $recipient;
	}
	
	
	/**
	 * API getTeams from model SEI/model/Officials
	 * Expecting $_REQUEST params []
	 * @return json set of searched Officials 
	 */
	private function searchOfficials()
	{
		$request = $this->filterParams(['lon','lat','home_field_available', 'looking_date', 'flexible_on_date', 'day_period', 'flexible_on_time', 'need_players', 'miles_radius']);
		$data = [];
	    $officials_model = new Officials($this->dbAdapter);
	    $officials = $officials_model->getOfficials($request);
	    $official_formated = [];
	    foreach ($officials as $official)
	    {
	    	
	    	$official['official_image'] = $this->getOfficialLogoURL($official['user_id']);
	    	$dist = Misc::distance($request['lat'],$request['lon'], $official["lat"],$official["lon"]);
	    	$official['distance'] = round($dist, 2);
	    	$official_formated[] = $official;
	    }
	    
	    return $this->formatResponse(self::STATUS_SUCCESS, "", $official_formated);
	    
	}
	
	
	/**
	 *
	 * @return official details based on team id
	 */
	private function getOfficialDetails()
	{
	    $request = $this->filterParams(['official_id']);
	    
	    $official_model = new Officials($this->dbAdapter);
	    $officials = $official_model->getById($request['official_id']);
	    
	    
	    if (!isset($officials[0])) {
	        return $this->formatResponse(self::STATUS_FAILED, "-1");
	    }
	    
	    return $this->formatResponse(self::STATUS_SUCCESS, $officials);
	}
	
	
	/**
	 *
	 * @return official details based on team id
	 */
	private function getRefereedHistory()
	{
		
		$request = $this->filterParams(['official_id']);
		$model = new Games($this->dbAdapter);
		$result = $model->getGames();
		
		return $this->formatResponse(self::STATUS_SUCCESS, "", $result);
	}
	
	
	
	private function getMessages()
	{
	    
	    $request = $this->filterParams(['request_id']);
	    $model = new Messages($this->dbAdapter);
	    $result = $model->getAllMessages(array("request_id"=>$request['request_id']));
	    
	    return $this->formatResponse(self::STATUS_SUCCESS, "", $result);
	}
	
	
	private function sendMessage()
	{
	    
	    $request = $this->filterParams(['request_id', 'message']);
        
        $this->processRequestAction($request['request_id'], $request['message'], false, "New message", false);
	    
        return $this->formatResponse(self::STATUS_SUCCESS, "", $request['message']);
	     
	}
	
	
	private function saveProfile()
	{
	    $request = $this->filterParams(['first_name', 'last_name',  'email',  'mobile_number', 'year_born']);
	    
	    $user_model = new Users($this->dbAdapter);
	    /**$users = $user_model->getByEmail($request['email']);	    
	    
	    if (isset($users[0])) {
	        //Return fail, user exists
	        return $this->formatResponse(self::STATUS_FAILED, "0");
	    }**/
	    $request['id'] = $this->user_id;
	    $user_id = $user_model->update($request);
	    
	    if ($user_id) {
	        //Get newly created user from db
	        $users = $user_model->getById($this->user_id);
	        unset($users[0]['password']);	
	        $user = (object) $users[0];
	        return $this->formatResponse(self::STATUS_SUCCESS, "", $user);
	    }
	    //Return fail, unable to register user
	    return $this->formatResponse(self::STATUS_FAILED, "-1");
	    
	}
	
	
	
	private function setRequestStatus()
	{
	    $request = $this->filterParams(['request_id', 'status']);
	    
	    $request_model = new Requests($this->dbAdapter);//
	    $request_object = $request_model->getById($request['request_id']);//
	    $request_object[0]['accepted'] = $request['status'];
	    $request_object[0]['accepted_on'] = date("Y-m-d");
	    $request_model->update($request_object[0]);
	    
	    
	    $user_model = new Users($this->dbAdapter);
	    $sender = $user_model->getById($this->user_id)[0];
	    
	    if($request['status'] == "1")
	    {
	    	$message = "Hi, " . $sender['first_name'] . " " . $sender['last_name'] . " accepted your request";
	    }elseif($request['status'] == "0"){
	    	$message = "Sorry, " . $sender['first_name'] . " " . $sender['last_name'] . " rejected your request";
	    }
	    
	    $recipient = $user_model->getById($request_object[0]['created_by'])[0];
	    $subject = "Response on your ScrimmageSearch request";
	     
	    //Save notification
	    $dataNotification = ["message"=>$message,
	    		"request_id"=>$request['request_id'],
	    		"user_to"=>$recipient['id'],
	    		"viewed"=>0];
	    $notifications_model= new Notifications($this->dbAdapter);
	    $notification_id = $notifications_model->insert($dataNotification);
	    
	    //Send push and email
	    $this->sendEmail($recipient, $message, $subject);
	    $this->sendPush($message, $recipient);
	    
	    return $this->formatResponse(self::STATUS_SUCCESS, $request_object);

	}
	
	private function setDeviceToken()
	{
		$request = $this->filterParams(['deviceToken']);
		
		$model = new Users($this->dbAdapter);
		$object = $model->getById($this->user_id);
		$object[0]['device_token'] = $request['deviceToken'];
		$model->update($object[0]);
		
		return $this->formatResponse(self::STATUS_SUCCESS, $object[0]);
	}
	
	private function testPush()
	{
		$request = $this->filterParams(['deviceToken']);
		
		$model = new Users($this->dbAdapter);
		$object = $model->getById($this->user_id);
		$user['id'] = $object[0]['id'];
		$user['device_token'] = $request['deviceToken'];
		
		$model->update($user);
		
		$result = $this->sendPush("Hello, " . $object[0]['first_name']. " sent you a request.", $object[0]);
		
		return $this->formatResponse(self::STATUS_SUCCESS, $result, []);
	}
	
	private function sendPush($msg, $recipient)
	{    
		$model = new Users($this->dbAdapter);
		$object = $model->getById($recipient['id']);
		$user = $object[0];
		
		//die(var_dump($object[0]));
		if($this->getDevice($user) == User::DEVICE_IOS)
		{
			return $this->sendIosPush($user, $msg);
		}elseif($this->getDevice($user) == User::DEVICE_ANDROID){
			return $this->sendAndPush($user, $msg);
		}
	}
	
	private function sendIosPush($user, $msg)
	{
		$token = $this->getDeviceToken($user);
		$keyfile = ROOT_PATH . '/' . 'AuthKey_9ND2HSVBVJ.p8';               # <- Your AuthKey file
		$keyid = '9ND2HSVBVJ';                            # <- Your Key ID
		$teamid = 'Y266NUKF5C';                           # <- Your Team ID (see Developer Portal)
		$bundleid = 'com.sei.coachApp';                # <- Your Bundle ID
		$url = 'https://api.push.apple.com';  # <- development url, or use http://api.push.apple.com for production environment
		
		$message = '{"aps":{"content-available" : 1, "alert":"'.$msg.'","sound":"default","badge":1}}';
		
		$key = openssl_pkey_get_private('file://'.$keyfile);
		//die($token);
		
		$header = ['alg'=>'ES256','kid'=>$keyid];
		$claims = ['iss'=>$teamid,'iat'=>time()];
		
		$header_encoded = $this->base64($header);
		$claims_encoded = $this->base64($claims);
		
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
		//echo $result;
		
		return $result;
	}
	
	private function base64($data) {
		return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
	}
	
	private function sendAndPush($user, $msg)
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
				'to' => $this->getDeviceToken($user),
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
	 *
	 * @return array
	 */
	private function getDevice($user){
		return substr($user['device_token'], 0, 3);
	}
	
	/**
	 *
	 * @return array
	 */
	private function getDeviceToken($user){
		return substr($user['device_token'], 4);
	}
	
	/**
	 * Base64 encoding that doesn't need to be urlencode()ed.
	 * Exactly the same as base64_encode except it uses
	 *   - instead of +
	 *   _ instead of /
	 *   No padded =
	 *
	 * @param string $input base64UrlEncoded input
	 *
	 * @return string The decoded string
	 */
	protected static function base64UrlDecode($input) {
		return base64_decode(strtr($input, ' ', '+'));
	}
	
	
}