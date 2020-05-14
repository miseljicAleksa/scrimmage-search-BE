<?php

namespace Workmark\Project;


    use Workmark\Model\Projects;
use Workmark\Model\Tasks;
use Zend\Db\Sql\Ddl\Column\Datetime;
use phpDocumentor\Reflection\Types\String_;
																
    /**
     * The Project class
     *
     */
    class Task
    {
    	/**
    	 * Model
    	 * @var Tasks
    	 */
    	private $model;
    	
    	/**
    	 * Weekends
    	 * @var int
    	 */
    	private $numOfWeekends = null;
    	
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
    		$rows = $this->model->getTasks(['id'=>$id]);
    		$this->row = $rows[0];
    	}
    	
    	/**
    	 * 
    	 * @return \DatePeriod
    	 */
    	public function getPeriod()
    	{
    		$interval = \DateInterval::createFromDateString('1 day');
    		return new \DatePeriod($this->getStartDate(), $interval, $this->getEndDate());
    	}
    	
    	/**
    	 *
    	 * @return String
    	 */
    	public function getDays()
    	{
    		$startDate = new \DateTime($this->row['startDate']);
    		$endDate = new \DateTime($this->row['endDate']);
    		return $endDate->diff($startDate)->format("%a");
    	}
    	
    	/**
    	 * 
    	 * @return \DateTime
    	 */
    	public function getStartDate()
    	{
    		return new \DateTime($this->row['startDate']);
    	}
    	
    	/**
    	 * 
    	 * @return \DateTime
    	 */
    	public function getEndDate()
    	{
    		return new \DateTime($this->row['endDate']);
    	}
    	
    	/**
    	 *
    	 * @return \DateTime
    	 */
    	public function getWorkingDays()
    	{
    		return $this->getDays() - $this->getNumOfWeekends();
    	}
    	
    	/**
    	 *
    	 * @return \DateTime
    	 */
    	public function getWorkingHoursPerDay()
    	{
    		return $this->row['hours'] > 0  ? $this->row['hours'] : $this->row['userWorkingHours'];
    	}
    	
    	/**
    	 *
    	 * @return \DateTime
    	 */
    	public function getWorkingHours()
    	{
    		return $this->getWorkingHoursPerDay() * $this->getWorkingDays();
    	}
    	
    	
    	/**
    	 *
    	 * @return \DateTime
    	 */
    	public function getNumOfWeekends()
    	{
    		if (!$this->numOfWeekends)
    		{
    			$this->setNumOfWeekends();
    		}
    		return $this->numOfWeekends;
    	}
    	
    	/**
    	 *
    	 * @return \DateTime
    	 */
    	public function setNumOfWeekends()
    	{
    		$num = 0;
    		foreach($this->getPeriod() as $day)
    		{
    			if ($this->isWeekend($day))
    			{
    				$num++;
    			}
    		}
    		return $this->numOfWeekends = $num;
    	}
    	
    	/**
    	 *
    	 * @return double
    	 */
    	public function getPrice()
    	{
    		if(!$this->row)
    		{
    			throw new \Exception('Task is not initialised');
    		}
    		
    		$hourPrice = $this->row['hourPrice'] > 0 ? $this->row['hourPrice'] : $this->row['userHourPrice'];

    		return round(($hourPrice * $this->getWorkingHoursPerDay() * $this->getWorkingDays()), 2);
    	}
    	
    	private function isWeekend($date)
    	{
    		return $date->format('N') >= 6;
    	}

    }
