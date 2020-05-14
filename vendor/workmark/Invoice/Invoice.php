<?php
namespace Workmark\Invoice;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use Workmark\Model\Users;
use Workmark\Mailer\Mailer;
use Workmark\Project\Project;
use Workmark\Model\Projects;

/**
 * Adapter used for authenticating user. It takes login and password on input
 * and checks the database if there is a user with such login (email) and password.
 * If such user exists, the service returns his identity (email). The identity
 * is saved to session and can be retrieved later with Identity view helper provided
 * by ZF3.
 */
class Invoice 
{
	/**
	 * User model.
	 * @var Users
	 */
	private $model;
	
	/**
	 * Project
	 * @var Project
	 */
	private $project;
	
	const STATUS_ACTIVE = 'ACTIVE';
	const STATUS_DEACTIVATED = 'DEACTIVATED';
	
	
	/**
	 * Instance holder
	 *
	 * @var array
	 */
	protected $row = null;
	
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
		if(is_numeric($id))
		{
			$rows = $this->getModel()->getInvoices(['id'=>$id]);
		}else{
			$rows = $this->getModel()->getInvoices(['hash'=>$id]);
		}
		$this->row = $rows[0];
	}
	
	/**
	 * @return array
	 */
	public function getMapper() {
		return $this->row;
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
	public function getId() {
		return $this->getMapper()['id'];
	}
	
	/**
	 * @return string
	 */
	public function getPrice() {
		return $this->getMapper()['price'];
	}
	
	/**
	 * @return string
	 */
	public function getNumber() {
		return  "#".str_pad($this->getMapper()['offerId'],6,"0",STR_PAD_LEFT);;
	}
	
	/**
	 * @return string
	 */
	public function getSubTotal() {
		return $this->getPrice() * (100 - $this->getDiscount()) / 100;
	}
	
	/**
	 * @return string
	 */
	public function getTotal() {
		return $this->getTaxBase() * (100 + $this->getTax()) / 100;
	}
	
	/**
	 * @return string
	 */
	public function getTax() {
		return $this->getMapper()['tax'];
	}
	
	/**
	 * @return string
	 */
	public function getTaxBase() {
		return $this->getSubTotal();
	}
	
	/**
	 * @return string
	 */
	public function getProject() {
		
		if (!$this->project)
		{
			$this->setProject();
		}
		return $this->project;
	}
	
	/**
	 * @return string
	 */
	public function setProject() {
		$project = new Project($this->model->model->get(Projects::class));
		$project->init($this->getProjectId());
		$this->project = $project;
	}
	
	/**
	 * @return string
	 */
	public function getProjectName() {
		return $this->getMapper()['projectName'];
	}
	
	/**
	 * @return string
	 */
	public function getProjectId() {
		return $this->getMapper()['projectId'];
	}
	
	/**
	 * @return string
	 */
	public function getHash() {
		return $this->getMapper()['hash'];
	}
	
	/**
	 * @return string
	 */
	public function getDiscount() {
		return $this->getMapper()['discount'];
	}
	
	/**
	 * @return string
	 */
	public function getAttention() {
		return $this->getMapper()['attention'];
	}
	
	/**
	 * @return string
	 */
	public function getOfferDate($formatted = false) {
		
		if (!$formatted)
		{
			return $this->getMapper()['offerDate'];
		}
		
		$date = new \DateTime($this->getMapper()['offerDate']);
		return $date->format('M d, Y');
	}
	
	/**
	 * @return boolean
	 */
	public function persist() {
		return $this->row?true:false;
	}
}
