<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\Zone;
use Application\Model\Art;
use Application\Model\User;


class TestController extends AbstractActionController
{
    
    public function indexAction()
    {
        $this->layout('layout/html');
        return new ViewModel();
    }
    
    public function populateAction()
    {
        
        $noOfUsersToCreate  = 200;
        $maxArtPerUser      = 2;
        
        // models   
        $sm                 = $this->getServiceLocator();
        $dbAdapter          = $sm->get('Zend\Db\Adapter\Adapter');
        $userTable          = $sm->get('Application\Model\UserTable');
        $artTable           = $sm->get('Application\Model\ArtTable');
        $zoneTable          = $sm->get('Application\Model\ZoneTable');
        
        
        // data 
        $users              = array('Screaming_Midget','SoapBox','Raven_Guardia','michael jackson','JimmyCarterIsSmarter','CanadianDracula','God','ARandomWhiteGuy','Chuck Norris','Pigeon');
        $locations          = array('peterborough','luton','tumbridge wells','scotland','barcelona','london','manchester','shoreditch','area 51','seven oaks','harpenden','kingston'); 
        $artLocations       = $dbAdapter->query('SELECT * FROM rndLocations')->execute()->getResource()->fetchAll();
   
        
        
        // logic
        while ($noOfUsersToCreate){
            
            echo "Users left: $noOfUsersToCreate ";
            
            shuffle($users);
            shuffle($locations);
            
            $username       = current($users).'_'.uniqid();
            $email          = "$username@dummydata.co.uk";
            $location       = current($locations);
            $noOfArtWork    = rand(0, $maxArtPerUser);
            
            // create user
            $user   = new User(array(
                'email'             =>$email,
                'password'          =>'lessrain',
                'username'          =>$username,
                'location'          =>$location,
                'confirmedEmail'    =>true
                ));

            $userTable->saveUser($user); // save
            
            
            while($noOfArtWork){
                
                echo "A: $noOfArtWork ";
                
                // random zone ref
                shuffle($artLocations);
                
                $zone_ref           = $artLocations[0]['zone_ref']; 
                $lat                = $artLocations[0]['lat'];
                $lon                = $artLocations[0]['lon'];
                
                // set up zone reference                 
                if ($zone = $zoneTable->findZone($zone_ref)){
                    $art->zone      = $zone;
                } else {
                    try {              
                        $zone       = new Zone(array('ref'=>$zone_ref));
                        $zoneTable->save($zone); 
                    } catch(Exception $e){
                        $error      = $e->getMessage();                 
                        die($error);
                    }
                }
                
                
                // create art
                $art = new Art(array(
                    'user'      => $user,
                    'zone'      => $zone,
                    'lon'       => $lat,
                    'lat'       => $lon,
                    ));

                // save art
                $artTable->saveArt($art); // save
               
                $noOfArtWork--;
            }
            
            echo "Done<br/>";
            
            $noOfUsersToCreate--;
        }
        
        die;
    }
}
