<?php
namespace Rest\Model;
use \Application\Traits\ReadOnly;

class ApiComponent
{
    
    use ReadOnly;

    /*
     * Used for status, to maintain a constant naming convention
     */
    const DEPRICATED = "DEPRICATED";
    const REMOVED = "REMOVED";
    const UNFINISHED = "UNFINISHED";
    
    
    protected $_route;
    protected $_description;
    protected $_alert;
    protected $_arguments;
    protected $_returns;
    protected $_status;


    public function setRoute($name = '/home', array $arguments = array()){
        
        $stdClass = new \stdClass();
        $stdClass->name = $name;
        $stdClass->arguments = $arguments;        
        $this->_route = $stdClass;
        return $this;
    }
    
    public function getRoute(){
        
        if (!isset($this->_route)){
            $this->setRoute();
        }
        return $this->_route;
    }

    public function setDescription($description = ''){
        $this->_description = $description;
        return $this;
    }
    
    public function getDescription(){
        
        if (!isset($this->_description)){
            $this->setDescription();
        }
        return $this->_description;
    }        
    
    public function setAlert($alert = null){
        $this->_alert = $alert;
        return $this;
    }
    
    public function getAlert(){
        
        if (!isset($this->_alert)){
            $this->setAlert();
        }
        return $this->_alert;
    } 
    
    private function setArguments(array $arguments = array()){
        $this->_arguments = $arguments;
        return $this;
    }
    
    public function getArguments(){
        
        if (!isset($this->_arguments)){
            $this->setArguments();
        }
        return $this->_arguments;
    }  

    public function addArgument($name, $required = false, $datatype = 'GET'){
        
        $stdClass = new \stdClass();
        $stdClass->name = $name;
        $stdClass->required = $required;        
        $stdClass->datatype = $datatype;        
        
        $arguments = $this->getArguments();
        $arguments[] = $stdClass;
        $this->setArguments($arguments);
        return $this;
    }
    
    public function setReturns(array $nameAndDatatypePairs = array()){
        
        $returns = array();
        
        foreach ($nameAndDatatypePairs AS $name=>$datatype){
            
            $stdClass = new \stdClass();
            $stdClass->name = $name;
            $stdClass->datatype = gettype($datatype);            
            $stdClass->details = $datatype;

            $returns[] = $stdClass;
        }
        $this->_returns = $returns;
        return $this;
    }
    
    public function getReturns(){
        
        if (!isset($this->_returns)){
            $this->setReturns();
        }
        return $this->_returns;
    } 
    
    public function setStatus($statusConstant = null){
        
        /*
         * Use the class constants to define a status
         */
        $this->_status = $statusConstant;
        return $this;
    }
    
    public function getStatus(){
        
        if (!isset($this->_status)){
            $this->setStatus();
        }
        return $this->_status;
    } 
        
    
}
