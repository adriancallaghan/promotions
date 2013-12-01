<?php
namespace Rest\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Application\Model\Cluster;
use Application\Model\Zone;



class LocationController extends AbstractActionController
{
    public function indexAction()
    {
        
        // in
        $zoneRefs   = is_null($zoneRefs = $this->params()->fromQuery('zone_refs',null)) ? null : explode(':',$zoneRefs);

        // out
        $success    = false;
        $errors     = array();
        $zones      = array();       

        
        /*
         * The query is exposed in the controller so that caching can be optomized and altered later
         * 
         * Cache idea 30Jul2013:
         * 
         * Cache is time controlled by the level of zoom and the name of the zoneref
         * so level 0 will be max at 18hrs and down through the levels back to no cache (this should be a small area)
         * so lower levels such as level 18 are popped immediately from the cache on update
         * 
         * 
         * Cache works by:
         * Instantiating a wrapper class called cache, this holds an object, and gives it a time till expires property
         * when retrieving from the cache, the time till expires is checked and if neccessary it is flushed
         * this is done on a request, by request basis, no need for a cron, it executed just before the stmt exec is called 
         * 
         */ 
        
        // models
        $sm             = $this->getServiceLocator();
        $dbAdapter      = $sm->get('Zend\Db\Adapter\Adapter');
        $prepareStmt    = $dbAdapter->createStatement();   
        $systemLog      = $sm->get('SystemLogger');
        $cacheEnabled   = false;

        $prepareStmt->prepare("CALL getZoneArt(:zoneRef)");

        
        if (count($zoneRefs)<1){
            $errors['zones']         = (object) array('isEmpty'=>'No zone refs defined');
        } else {
        
            $success = true;
            
            foreach ($zoneRefs AS $zoneRef){

                /*
                 * TODO change the instantiation of the object below to a tedatabase look up
                 * the object only holds a reference and nothing more, and throws an exception if
                 * the ref is invalid, this is fatal and should be caught as an error and then skip to the next reference
                 */
                $zone = new Zone(array(
                    'ref'=>$zoneRef
                    ));
 
                $cluster    = new Cluster(); // return format

                
                /*
                 * Cluster not cached or cache not available
                 * (Fetch from db, cluster and cache(if cache avail))
                 */
                if (!$cacheEnabled || !isset($clusterFoundInCache) && !isset($clusterCacheExpired)){   
                    
                    $zoneRef = $zone->getRef();
                    
                    $prepareStmt->getResource()->bindParam('zoneRef', $zoneRef);
                    if ($prepareStmt->execute()){

                        // FETCH FROM DB
                        $artwork        = $prepareStmt->getResource()->fetchAll();

                        // CLUSTER
                        $cluster        = new Cluster($artwork, $zone->zoomDistance);
                        
                        if ($cacheEnabled){
                            // STORE IN CACHE (OVERWRITE EXISTING because the new request, could be down to an expired time)
                            //$systemLog->debug('Storing in cache');
                        }                        

                        $prepareStmt->getResource()->closeCursor();
                        
                    } else {
                        $error      = 'Internal Error Fetching Zone: '.$zone->ref;
                        $errors['Zone']   = (object) array('Internal'=>"The server encountered an error ");
                        $success    = false;
                        $systemLog->alert($error);                         
                    } 
                    
                    
                }                 
                /*
                * Cluster is cached, fetch from cache
                */
                else {
                    

                }
                  
                
                

                $zones[$zone->getRef()]   = $cluster->toArray();
            }
            
        }
        

        return new JsonModel(array(   
            'service name'  => 'Get Artwork by location',
            'success'       => $success,
            'errors'        => $errors,            
            'zones'         => $zones,                      
        ));
    }

}