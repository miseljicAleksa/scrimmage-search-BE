<?php

namespace Workmark\Project;


    use Workmark\Model\Projects;
use Workmark\Model\Groups;
								
    /**
     * The Project class
     *
     */
    class Project
    {
    	/**
    	 * Model
    	 * @var Projects
    	 */
    	private $model;
    	
    	/**
    	 * Model
    	 * @var array
    	 */
    	private $groups;
    	
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
    		$this->row = $this->model->getById($id);
    	}
    	
    	private function setGroups()
    	{
    		$groups = $this->model->model->get(Groups::class)->getGroups(['projectId'=>$this->row['id']]);
    		foreach ($groups as $one)
    		{
    			$group = new Group($this->model->model->get(Groups::class));
    			$group->init($one['id']);
    			$this->groups[] = $group;
    		}
    	}
    	
    	public function getGroups()
    	{
    		if (!$this->groups)
    		{
    			$this->setGroups();
    		}
    		return $this->groups;
    	}
    	
    	
    	/**
    	 *
    	 * @return number
    	 */
    	public function getPrice()
    	{
    		$price = 0;
    		foreach ($this->getGroups() as $group)
    		{
    			$price += $group->getPrice();
    		}
    		return $price;
    	}

    }
