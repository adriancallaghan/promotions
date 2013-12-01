<?php
namespace Application\Model;
use Application\Traits\ReadOnly;
use Application\Model\User;
use Application\Model\Art;

class Like 
{

    use ReadOnly;

    protected $_inputFilter;
    protected $_id;
    protected $_user;
    protected $_art;
    protected $_created;

    
    
    public function setId($id = 0){
        $this->_id = $id;
        return $this;
    }
    
    public function getId(){
        
        if (!isset($this->_id)){
            $this->setId();
        }
        return $this->_id;
    }        
    
    public function setUser(User $user){
        $this->_user = $user;
        return $this;
    }
    
    public function getUser(){
        
        if (!isset($this->_user)){
            $this->setUser(new User());
        }
        return $this->_user;
    } 
    
    
    public function setArt(Art $art){
        $this->_art = $art;
        return $this;
    }
    
    public function getArt(){
        
        if (!isset($this->_art)){
            $this->setArt(new Art());
        }
        return $this->_art;
    } 
    
    public function setCreated($created = ''){
        $this->_created = $created;
        return $this;
    }
    
    public function getCreated(){
        
        if (!isset($this->_created)){
            $this->setCreated();
        }
        return $this->_created;
    } 

    public function exchangeArray($data)
    {
        /*
         * Differs from setOptions in that it defines EVERY field, resetting the object
         */
        $this->id                   = (isset($data['id'])) ? $data['id'] : null;
        $this->user                 = (isset($data['user'])) ? new User(array('id'=>$data['user'])) : null;
        $this->art                  = (isset($data['art'])) ? new Art(array('id'=>$data['art'])) : null;
        $this->created              = (isset($data['created'])) ? $data['created'] : null;
    }
    
}
