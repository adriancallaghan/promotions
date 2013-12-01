<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ConfirmationController extends AbstractActionController
{
    
   
    public function indexAction()
    {

        $this->layout('layout/html');
        
        $userTable = $this->getServiceLocator()->get('Application\Model\UserTable');                
        $user = $userTable->getUserByConfirmationToken($this->params()->fromRoute('token',''));
        
        
        if (!$user){
            
            return new ViewModel(array(
                'title'=>'Cannot activate user account',
                'message'=>'The user was not found',
            ));
            
        } elseif ($user->confirmedEmail){
            
            return new ViewModel(array(
                'title'=>'Cannot activate user account',
                'message'=>'The user is already actived',
            ));           

        } else {
            
            $user->setConfirmedEmail(true);
            $userTable->saveUser($user);
            
            return new ViewModel(array(
                'title'=>'User account activated',
                'message'=>'Your user account is now fully activated',
            ));
            
        }
        

        
            
        
    }
}
