<?php

namespace Application\Model;
use \Application\Traits\ReadOnly;

class Zone
{

    use ReadOnly;
    
    const delimiter = '-';
    
    protected $_id;
    protected $_ref;
    
    
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

    public function getZoomDistance(){

        // return position three
        $zoomLevel = explode(self::delimiter, $this->ref)[2];
        switch($zoomLevel){
            case 18: return 300; break;
            case 17: return 150; break;
            case 16: return 110; break;
            case 15: return 70; break;
            case 14: return 40; break;
            case 13: return 20; break;
            case 12: return 12; break;
            case 11: return 10; break;
            case 10: return 8; break;
            case 9: return 7; break;
            case 8: return 6; break;
            case 7: return 5; break;
            case 6: return 4; break;
            case 5: return 3; break;
            case 4: return 2; break;
            case 3: return 1; break;
            case 2: return 1; break;
            case 1: return 1; break;
            default: return 300; break;

            /*case 18: return 300; break;
            case 17: return 150; break;
            case 16: return 110; break;
            case 15: return 80; break;
            case 14: return 50; break;
            case 13: return 30; break;
            case 12: return 20; break;
            case 11: return 15; break;
            case 10: return 11; break;
            case 9: return 7; break;
            case 8: return 6; break;
            case 7: return 5; break;
            case 6: return 4; break;
            case 5: return 3; break;
            case 4: return 2; break;
            case 3: return 1; break;
            case 2: return 1; break;
            case 1: return 1; break;
            default: return 300; break;*/
        }
    }
    
    public function setRef($ref = "0-0-0"){
        

        $delimiter = self::delimiter;
        $reference = explode($delimiter, $ref);
        
        // check if the reference is valid 
        if (count($reference)!==3){
            throw new \Exception('Bad Zone Ref '.$ref);
        }
        
        list($x,$y,$z) = $reference;
        
        // check zoom
        if ($z <0 || $z>18){
            throw new \Exception('Bad Zone Zoom Ref '.$ref);
        }      
                
        $this->_ref = "$x{$delimiter}$y{$delimiter}$z";

        return $this;
    }
    
    public function getRef(){
        
        if (!isset($this->_ref)){
            $this->setRef();
        }
        return $this->_ref;
    }        
    
    public function getParentRef(){
        
        $delimiter = self::delimiter;        
        $references = explode($delimiter, $this->ref);

        list($x,$y,$zoom) = $references;
        $px = floor($x/2);
        $py = floor($y/2);
        $pz = $zoom-1;            
        return "$px{$delimiter}$py{$delimiter}$pz";
        
    }    

    public function exchangeArray($data)
    {        
        $this->id     = (isset($data['zone_id'])) ? $data['zone_id'] : null;
        $this->ref = (isset($data['zone_ref'])) ? $data['zone_ref'] : null;
    }
    
    
}
