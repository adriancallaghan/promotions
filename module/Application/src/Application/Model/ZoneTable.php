<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Application\Model\Zone;

class ZoneTable
{
    
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }    
 
    public function save(Zone $zone)
    {
            
       
        /*
         * In order to save we must know where in the hierachy it goes
         * 
         * this can be done by calculating the zones parent
         * 
         * the calculation is within the zone object
         * 
         * first we create the parent, and check if it exists by saving it
         * 
         * this is a recursive call that also checks for its parents, and so on, right back to the root
         */

        // the root (zoom zero) is always present
        if ($zone->getId()==0){

            // create the immediate parent from the zone                     
            if (!$parent = $this->findZone($zone->getParentRef())){
                
                $parent = new Zone(array(
                    'ref'=>$zone->getParentRef()
                    ));
                
                $this->save($parent); // save calls this save method, which saves this parent, this is a recursive call back to the nearest saved node
            }
            
            $zoneRef = $zone->getRef();
            $parentRef = $parent->getRef();
            
            $dbAdapter = $this->tableGateway->getAdapter();
            $prepareStmt = $dbAdapter->createStatement();

            $prepareStmt->prepare("CALL addChildZone(:zoneRef, :parentRef)");
            $prepareStmt->getResource()->bindParam('zoneRef', $zoneRef);
            $prepareStmt->getResource()->bindParam('parentRef', $parentRef);
            
            if ($prepareStmt->execute()){
                // fetch the results from query, which contains the last insert id
                $result = current($prepareStmt->getResource()->fetchAll());
                if (isset($result['LastInsertId'])){
                    $zone->id = $result['LastInsertId'];
                }
            }

        } else {

            /*$this->getDbTable()->update(
                array('zone_ref'=>$zone->ref), array('zone_id = ?' => $zone->getId())
            );*/
            throw new Exception('Zones do not update');         
        }

    }


    public function findZone($zoneRef)
    {    
        $rowset = $this->tableGateway->select(array('zone_ref = ?' => $zoneRef));
        $row = $rowset->current();
        return $row;
    }
    
    
    public function fetchAll(array $where = null)
    {
        $resultSet = $this->tableGateway->select($where);        
        return $resultSet;
    }


}

