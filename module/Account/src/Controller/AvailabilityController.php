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
use Workmark\Model\Model;
use Workmark\Model\Teams;
use Workmark\Model\Scrimmages;
use Workmark\Model\Availability;
use Workmark\Model\Requests;

class AvailabilityController extends WorkmarkController
{
	
	/**@var Model */
	private $model;
	
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->model = $container->get(\Workmark\Model\Model::class);
	}
	
	/**
	 * edit instance of team class
	 * @throws \ no team selected
	 * @return Model\Teams
	 */
	public function editAction()
	{
	    if (!isset($_GET['id']) || intval($_GET['id']) < 1)
	    {
	        throw new \Exception('No team selected');
	    }
	    
	    $status = false;
	    
	    $team = $this->container->get(\Workmark\Model\Model::class)->get(\Workmark\Model\Teams::class);
	    
	    $sports = $team->getSports();
	    
	    $teamProperties = $team->getById($_GET['id']);
	    
	    if ($this->getRequest()->isPost())
	    {
	        $post = $_POST;
	        $status = "Something went wrong";
	       
	        try{
    	        $teamProperties['name'] = $post['name'];
    	        $teamProperties['gender'] = $post['gender'];
    	        $teamProperties['sport'] = $post['sport'];
    	        $teamProperties['age_year'] = $post['age_year'];
    	        $teamProperties['color'] = $post['color'];
    	        $teamProperties['description'] = $post['description'];
    	        $team->save($teamProperties);
    	        $status = "success";
	        }catch (\Exception $e){
	            $status = $e->getMessage();
	        }
	    }
	    
	    $team = (object)$teamProperties;
	    
	    return new ViewModel(['team'=>$team, 'status'=>$status, 'sports'=>$sports]);
	    
	}
	
    
    public function listAction()
    {
    	$REQUEST = null;
    	$q = isset($_REQUEST['q'])?trim($_REQUEST['q']):false;
    	if($q){
    		$REQUEST['q']=$q;
    	}
    	$model = $this->container->get(Model::class)->get(Requests::class);
    	$total = (int)$model->getTotal($REQUEST);
    	
    	$currentpage    = (isset($_GET['page']) ? intval($_GET['page']) : 1);
    	$perPage 		= 30;
    	$startFetch 	= ($currentpage * $perPage) - $perPage;
    	if($q){
    		$REQUEST = ['startFetch'=>(int)$startFetch, 'perPage'=>(int)$perPage, 'desc'=>true, 'q'=>$q];
    	}else{
    		$REQUEST = ['startFetch'=>(int)$startFetch, 'perPage'=>(int)$perPage, 'desc'=>true];
    	}
    	$list = (array)$model->getRequests($REQUEST);
    	
    	
    	$totalpage      = ceil($total / $perPage);
    	$lastpage       = $totalpage;
    	$loopcounter 	= ( ( ( $currentpage + 2 ) <= $lastpage ) ? ( $currentpage + 2 ) : $lastpage );
    	$startCounter 	=  ( ( ( $currentpage - 2 ) >= 3 ) ? ( $currentpage - 2 ) : 1 );
    	$pagination = '';
    	
    	if($totalpage > 1)
    	{
    		$pagination .= '<ul class="pagination" id="paginate">';
    		$pagination .= '<li><a  href="/account/availability/list?page=1" class="paginate_click" id="1-page">First</a></li>';
    		for($i = $startCounter; $i <= $loopcounter; $i++)
    		{
    			$activeClass = $currentpage == $i ? 'active' : '';
    			$pagination .= '<li class="'.$activeClass.'"><a href="/account/availability/list?page='.$i.'">'.$i.'</a></li>';
    		}
    		$pagination .= '<li><a href="/account/availability/list?page='.$totalpage.'" class="paginate_click" id="'.$totalpage.'-page">Last</a></li>';
    		$pagination .= '</ul>';
    	}
    	
    	$view = new ViewModel(['list'=>$list, 'pagination'=>$pagination]);
    	return $view;
    }
}
