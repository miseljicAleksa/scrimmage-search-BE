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
use Workmark\Model\Official;

class OfficialsController  extends WorkmarkController
{
    
    /**@var Model */
    private $model;
    
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->model = $container->get(\Workmark\Model\Model::class);
    }
    
    
    
    public function editAction()
    {
        if (!isset($_GET['id']) || intval($_GET['id']) < 1)
        {
            throw new \Exception('No official selected');
        }
        
        $status = false;
        
        $official = $this->container->get(\Workmark\Model\Model::class)->get(\Workmark\Model\Official::class);
        
        $officialProperties = $official->getById($_GET['id']);
        
        if ($this->getRequest()->isPost())
        {
        	$status = "Something went wrong";
            $post = $_POST;
            try{
                $officialProperties['years_of_experience'] = $post['years_of_experience'];
                $officialProperties['officiating_fee'] = $post['officiating_fee'];
                $officialProperties['sertificate'] = $post['sertificate'];
                //$officialProperties['image_file_name'] = $post['image_file_name'];
                
                $official->save($officialProperties);
                $status="success";
            }catch (\Exception $e){
                $status = $e->getMessage();
            }
            
            
        }
        
        $official = (object)$officialProperties;
        
        return new ViewModel(['official'=>$official,'status'=>$status]);
        
    }
    
    public function listAction()
    {
        $REQUEST = null;
        $q = isset($_REQUEST['q'])?trim($_REQUEST['q']):false;
        if($q){
            $REQUEST['q']=$q;
        }
        $officialModel = $this->container->get(Model::class)->get(Official::class);
        $totalOfficials = (int)$officialModel->getTotal($REQUEST);
        
        $currentpage    = (isset($_GET['page']) ? intval($_GET['page']) : 1);
        $perPage 		= 30;
        $startFetch 	= ($currentpage * $perPage) - $perPage;
        if($q){
            $REQUEST = ['startFetch'=>(int)$startFetch, 'perPage'=>(int)$perPage, 'desc'=>true, 'q'=>$q];
        }else{
            $REQUEST = ['startFetch'=>(int)$startFetch, 'perPage'=>(int)$perPage, 'desc'=>true];
        }
        $teams = (array)$officialModel->getOfficial($REQUEST);
        
        
        $totalpage      = ceil($totalOfficials / $perPage);
        $lastpage       = $totalpage;
        $loopcounter 	= ( ( ( $currentpage + 2 ) <= $lastpage ) ? ( $currentpage + 2 ) : $lastpage );
        $startCounter 	=  ( ( ( $currentpage - 2 ) >= 3 ) ? ( $currentpage - 2 ) : 1 );
        $pagination = '';
        
        if($totalpage > 1)
        {
            $pagination .= '<ul class="pagination" id="paginate">';
            $pagination .= '<li><a  href="/account/users/list?page=1" class="paginate_click" id="1-page">First</a></li>';
            for($i = $startCounter; $i <= $loopcounter; $i++)
            {
                $activeClass = $currentpage == $i ? 'active' : '';
                $pagination .= '<li class="'.$activeClass.'"><a href="/account/users/list?page='.$i.'">'.$i.'</a></li>';
            }
            $pagination .= '<li><a href="/account/users/list?page='.$totalpage.'" class="paginate_click" id="'.$totalpage.'-page">Last</a></li>';
            $pagination .= '</ul>';
        }
        
        $view = new ViewModel(['officials'=>$teams, 'pagination'=>$pagination]);
        return $view;
    }
}
