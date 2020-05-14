<?php
namespace Workmark\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Where;
use SEI\Models\Officials;

class Official extends ModelAbstract
{
    static $table = 'officials';
    
    const DIR_UPLOADS = "/data/uploads/";
    const DIR_PLAYER_IMAGE = "official";
    
    
    public function __construct(TableGatewayInterface $tableGateway, Model $model)
    {
        $this->tableGateway = $tableGateway;
        $this->model = $model;
    }
    
    public function fetchAll()
    {
        return $this->tableGateway->select();
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \Workmark\Model\ModelAbstract::getById()
     */
    public function getById($id)
    {
        $official = $this->getOfficial(['id'=>$id]);
        return $official[0];
    }
    
    /**
     * getUsers
     *
     * @param array $args
     * @return Officials
     */
    public function getTotal(array $args = null)
    {
        $sqlSelect = "SELECT count(*) as num FROM officials ";
        
        //die($sqlSelect);
        $result = $this->query($sqlSelect, []);
        return $result[0]['num'];
    }
    
    
    /**
     * getUsers
     *
     * @param array $args
     * @return Officials
     */
    public function getOfficial(array $args = null)
    {
        $sqlSelect = "SELECT o.*, u.email, u.fbId, CONCAT(u.first_name, ' ', u.last_name) as name FROM `officials` o
		JOIN users u ON u.id = o.user_id
            
		WHERE 1 ";
        if(isset($args['id']))
        {
            $sqlSelect .= "AND o.id = :id ";
        }
        
        $result = $this->query($sqlSelect, $args);
        
        
        return $result;
    }
    
    
    public function save($data)
    {
        
        //Set default values
        $values['id'] = $data['id'] ?? 0;
        $values['experience'] = $data['experience'] ?? null;
        $values['fee_per_game'] = $data['fee_per_game'] ?? null;
        $values['certification_file_name'] = $data['certification_file_name'] ?? null;
        $values['image_file_name'] = $data['image_file_name'] ?? null;
        $values['user_id'] = $data['user_id'] ?? null;
        
        
        return $this->insertOrUpdate($values, $data);
    }
    
    public function delete($id)
    {
        $this->tableGateway->delete(['id' => (int) $id]);
    }
    
    static function getImage($type, $id)
    {
        $file_path = ROOT_PATH . '/data/uploads/' . $type . '/' . $id. '.png';
        //die(var_dump($file_path));
        if (file_exists($file_path))
        {
            return SITE_URL . '/application/index/show?type=' . $type . '&id=' . $id;
        }
        
        return SITE_URL . '/default.png';
        
    }
}
