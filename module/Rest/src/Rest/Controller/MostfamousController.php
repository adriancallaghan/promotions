<?php

namespace Rest\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Application\Model\User;



class MostfamousController extends AbstractActionController
{
    
    const userUploadDir = 'uploads'; // no trailing slash
    
    
    public function indexAction()
    {


        $success    = true; 
        $errors     = array();
        $users      = array();
                
        
        $sm                 = $this->getServiceLocator();
        $dbAdapter          = $sm->get('Zend\Db\Adapter\Adapter');

        
        /*
         * Currently a list of users based on total likes then total views
         */
        /*$stmt = $dbAdapter->createStatement(                
                'SELECT u.*, sum(a.views) AS totalViews, count(l.id) AS totalLikes '.
                'FROM user u '.
                'LEFT JOIN art a ON u.id = a.user '.
                'LEFT JOIN likes l ON a.id = l.art '.
                'WHERE u.confirmed_email=1 '.
                'GROUP BY u.id '.
                'ORDER BY totalLikes DESC, totalViews DESC LIMIT 0,100'
                );*/
        
        $stmt = $dbAdapter->createStatement(                
                'SELECT u.*, sum(a.views) AS totalViews, sum(a.likes) AS totalLikes '.
                'FROM user u '.
                'LEFT JOIN art a ON u.id = a.user '.                
                'WHERE u.confirmed_email=1 '.
                'GROUP BY u.id '.
                'ORDER BY totalLikes DESC, totalViews DESC LIMIT 0,100'
                );

        
        foreach($stmt->execute() AS $result){  
            
            $user = new User($result);
            
            $users[] = array(
                'id'            => $user->id,
                'username'      => $user->username,
                'location'      => $user->location,
                'image'         => "/".self::userUploadDir."/".$user->image,
                'total_likes'   => $result['totalLikes'],
                'total_views'   => $result['totalViews'],                
                //'confirmedEmail'=> $userRaw->confirmedEmail,
            );
        }
        
                
        return new JsonModel(array(      
            'service name'  => 'Most Famous',
            'success'       => $success,
            'errors'        => $errors,
            'users'         => $users,
        ));
        
        
    }

}