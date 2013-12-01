<?php
namespace Rest\Model;

use Rest\Model\ApiComponent;

class Api implements \Iterator, \Countable
{

    use \Application\Traits\Iterable;
    use \Application\Traits\ReadOnly;


    protected $_description;

    
    public function setDescription($description = 'API'){
        $this->_description = $description;
        return $this;
    }
    
    public function getDescription(){
        
        if (!isset($this->_description)){
            $this->setDescription();
        }
        return $this->_description;
    }        

    public function addComponent(ApiComponent $apiComponent){
        
        $data = $this->getData();
        $data[] = $apiComponent;
        $this->setData($data);
        return $this;
        
    }
        
}
