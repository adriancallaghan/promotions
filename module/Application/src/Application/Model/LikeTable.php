<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Application\Model\Art;
use Application\Model\User;

class LikeTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function likeExists(User $user, Art $art){
        
        $rowset = $this->tableGateway->select(array('user'=>$user->id,'art' => $art->id));
        return count($rowset)>0 ? true : false;
    }
    
    public function fetchAll(array $arguments = null)
    {
        $rowset = $this->tableGateway->select($arguments);
        return $rowset;
    }
    
    public function getLike($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        return $row;
    }
    
    public function getLikesForArt(Art $art){        
        $rowset = $this->tableGateway->select(array('art' => (int) $art->id));
        return $rowset;
    }
    
    public function saveLike(Like $like)
    {
        $data = array(
            'user'                => (int)$like->user->id,
            'art'                 => (int)$like->art->id,
        );

        $id = (int)$like->id;
        
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $like->id = $this->tableGateway->lastInsertValue;
        } else {
            $this->tableGateway->update($data, array('id' => $id));            
        }
    }

    public function deleteLike($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }
}