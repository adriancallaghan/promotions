<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Application\Model\User;

class ArtTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getUserArt(User $user, $orderBy = null, $limit = null, $offset = null){
        
        $select = new Select(); 
        
        $select->from($this->tableGateway->getTable());
        
        $select->where(array('user=?'=>$user->id));
       
        if (!is_null($orderBy)) {
            $select->order($orderBy);
        }        
        
        if (!is_null($limit)) {
            $select->limit((int) $limit);
        }
        
        if (!is_null($offset)) {
            $select->offset((int) $offset);
        }

        $resultSet = $this->tableGateway->selectWith($select);
        
        return $resultSet;
    }

    
    public function getArts(array $ids = null, $orderBy = null, $limit = null, $offset = null){
        
        $select = new Select(); 
        
        $select->from($this->tableGateway->getTable());
        
        if (!is_null($ids) && !empty($ids)) {
            $select->where->in('id', $ids);
        }
              
        if (!is_null($orderBy)) {
            $select->order($orderBy);
        }        
        
        if (!is_null($limit)) {
            $select->limit((int) $limit);
        }
        
        if (!is_null($offset)) {
            $select->offset((int) $offset);
        }

        $resultSet = $this->tableGateway->selectWith($select);
        
        return $resultSet;
    }
    
    public function fetchAll(array $arguments = null)
    {
        $resultSet = $this->tableGateway->select($arguments);
        return $resultSet;
    }

    public function getArt($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        return $row;
    }

    public function saveArt(Art $art)
    {
        $data = array(
            'user'              => $art->user->id,
            'views'             => $art->views,
            'likes'             => $art->likes,
            'lat'               => $art->lat,
            'lon'               => $art->lon,
            'image_final'       => $art->imageFinal,
            'image_tag'         => $art->imageTag,
            'image_background'  => $art->imageBackground,
            'image_thumb'       => $art->imageThumb,
            'data'              => $art->data,
            'zone'              => $art->zone->id,
        );

        $id = (int)$art->id;
        
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->getLastInsertValue();
            $art->setId($id);
        } else {
            $this->tableGateway->update($data, array('id' => $id));            
        }
    }

    public function deleteArt($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }
}