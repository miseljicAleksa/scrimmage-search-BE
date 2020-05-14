<?php

namespace Workmark\Project;


    use Workmark\Model\Projects;
use Workmark\Model\Tasks;
								
    /**
     * The Project class
     *
     */
    class Group
    {
    	/**
    	 * Model.
    	 * @var Projects
    	 */
    	private $model;
    	
    	/**
    	 * Model.
    	 * @var array
    	 */
    	private $tasks;
    	
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
    		$this->row= $this->model->getById($id);
    	}
    	
    	private function setTasks()
    	{
    		$tasks = $this->model->model->get(Tasks::class)->getTasks(['groupId'=>$this->row['id']]);
    		
    		//die(var_dump($tasks));
    		foreach ($tasks as $one)
    		{
    			$task = new Task($this->model->model->get(Tasks::class));
    			$task->init($one['id']);
    			$this->tasks[] = $task;
    		}
    	}
    	
    	public function getMapper()
    	{
    		return $this->row;
    	}
    	
    	public function getTasks()
    	{
    		if (!$this->tasks)
    		{
    			$this->setTasks();
    		}
    		return $this->tasks;
    	}
    	
    	/**
    	 *
    	 * @return \DateTime
    	 */
    	public function getWorkingDays()
    	{
    		$wd = 0;
    		foreach ($this->getTasks() as $task)
    		{
    			$wd += $task->getWorkingDays();
    		}
    		return $wd;
    	}
    	
    	/**
    	 *
    	 * @return \DateTime
    	 */
    	public function getWorkingHours()
    	{
    		$wh = 0;
    		foreach ($this->getTasks() as $task)
    		{
    			$wh += $task->getWorkingHours();
    		}
    		return $wh;
    	}
    	
    	/**
    	 *
    	 * @return number
    	 */
    	public function getProjectDates()
    	{
    		return $this->model->model->get(Tasks::class)->getTaskPeriod(['projectId'=>$this->row['projectId']]);
    	}
    	
    	/**
    	 *
    	 * @return number
    	 */
    	public function getProjectDays()
    	{
    		$tasks = $this->getProjectDates();

    		if (isset($tasks[0]))
    		{
    			return $this->getDays($tasks[0]['startDate'], $tasks[0]['endDate']);
    		}
    		return 0;
    	}
    	
    	/**
    	 *
    	 * @return number
    	 */
    	public function getGroupDates()
    	{
    		return $this->model->model->get(Tasks::class)->getTaskPeriod(['groupId'=>$this->row['id']]);
    	}
    	
    	/**
    	 *
    	 * @return number
    	 */
    	public function getGroupDays()
    	{
    		$tasks = $this->getGroupDates();
    		//var_dump($tasks[0]);
    		if (isset($tasks[0]))
    		{
    			return $this->getDays($tasks[0]['startDate'], $tasks[0]['endDate']);
    		}
    		return 0;
    	}
    	
    	
    	/**
    	 *
    	 * @return number
    	 */
    	public function getDays($startDate, $endDate)
    	{
    		$startDate = new \DateTime($startDate);
    		$endDate = new \DateTime($endDate);
    		return $startDate->diff($endDate)->format("%a");
    	}
    	
    	/**
    	 *
    	 * @return \DateTime
    	 */
    	public function getPercent()
    	{
    		return round(($this->getGroupDays() / $this->getProjectDays() * 100));
    	}
    	
    	/**
    	 *
    	 * @return \DateTime
    	 */
    	public function getStartPercent()
    	{
    		$projectDates = $this->getProjectDates();
    		$groupDates = $this->getGroupDates();
    		
    		$startDays = $this->getDays($projectDates[0]['startDate'], $groupDates[0]['startDate']);
    		//var_dump($startDays);
    		return round(($startDays / $this->getProjectDays() * 100));
    	}
    	
    	/**
    	 *
    	 * @return \DateTime
    	 */
    	public function getStartDate()
    	{
    		$groupDates = $this->getGroupDates();
    		return $groupDates[0]['startDate'];
    	}
    	
    	/**
    	 *
    	 * @return \DateTime
    	 */
    	public function getEndDate()
    	{
    		$groupDates = $this->getGroupDates();
    		return $groupDates[0]['endDate'];
    	}

    	/**
    	 * 
    	 * @return number
    	 */
    	public function getPrice()
    	{
    		$price = 0;
    		foreach ($this->getTasks() as $task)
    		{
    			$price += $task->getPrice();
    		}
    		return $price;
    	}

    }
