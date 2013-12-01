<?php
namespace Rest\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Rest\Model\Api;
use Rest\Model\ApiComponent;
use Application\Model\User;
use Application\Model\Art;


class IndexController extends AbstractActionController
{
    
    
    
    public function indexAction()
    {
        
        $art        = new Art();
        $artArray   = $art->toArray();
        $user       = new User();
        $userArray  = $user->toArray();
        
        
        $api = new Api();
        //$api->setDescription('API');
        

        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('List`s all users')
                ->setStatus(ApiComponent::DEPRICATED)
                ->setRoute('rest/user')
                ->setReturns(array(
                    'service name'=>'',
                    'success'=>true,
                    'errors'=>array(),
                    'users'=>array($userArray),                    
                ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('Fetches an existing user`s detail')
                ->setRoute('rest/user',array('action'=>'get'))
                ->addArgument('email',true, 'GET')
                ->addArgument('user_id',true, 'GET')
                ->addArgument('identity_token',true, 'GET')
                ->setReturns(array(
                    'service name'  =>'',                    
                    'success'       =>true,
                    'errors'        =>array('user'=>'unknown'),
                    'user'          =>$userArray
                        
                    )
                );
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('Get the user identity token, used for interacting with the services (login)')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/user',array('action'=>'getIdentityToken'))
                ->addArgument('email',true, 'POST')
                ->addArgument('password',true, 'POST')
                ->setReturns(array(
                    'service name'  => '',                    
                    'success'       => true,
                    'identity_token'=> uniqid(),
                    'user'          => $userArray,
                    'errors'        => array('submission'=>'invalid','user'=>'unknown'),                
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('Create`s user and emails them the registration process')
                ->setRoute('rest/user',array('action'=>'set'))
                ->addArgument('email',true, 'POST')
                ->addArgument('password',true, 'POST')
                ->addArgument('username',true, 'POST')
                ->addArgument('location',true, 'POST')
                ->setReturns(array(
                    'service name'  =>'',                    
                    'success'       =>true,
                    'errors'        =>array(
                        'email'         =>'taken',
                        'username'      =>'taken',
                        'submission'    =>'invalid'
                    ),
                    'user_id'       => 0,
                    'identity_token'=> '',
                    'emailed'       => true,                    
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
             
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('<p>Updates an existing user`s details</p>')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/user',array('action'=>'update'))
                ->addArgument('identity_token',true, 'GET')
                ->addArgument('username',false, 'POST')
                ->addArgument('password',false, 'POST')
                ->addArgument('location',false, 'POST')
                ->addArgument('image',false, 'POST')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array(
                        'user'              =>'unknown',
                        'submission'        =>'invalid',
                        'server'            =>'error',                        
                        ),
                    'user_id'=> 0,                   
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);

        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('Resends the user`s registration email')
                ->setRoute('rest/user',array('action'=>'remind'))
                ->addArgument('identity_token',true, 'POST')
                ->setReturns(array(
                    'service name'  =>'',                    
                    'success'       =>true,
                    'errors'        =>array(
                        'user'              =>'unknown',
                        'server'            =>'error', 
                        ),                
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('Resets the user`s password and email`s them')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/user',array('action'=>'reset'))
                ->addArgument('email',true, 'POST')
                ->setReturns(array(
                    'service name'  =>'',                    
                    'success'       =>true,
                    'errors'        =>array(
                        'user'              =>'unknown',
                        'server'            =>'error', 
                        ),                                
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('Returns the artwork a user likes')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/user',array('action'=>'likes'))
                ->addArgument('email',true, 'GET')
                ->addArgument('user_id',true, 'GET')
                ->addArgument('identity_token',true, 'GET')
                ->addArgument('limit',false, 'GET')
                ->addArgument('offset',false, 'GET')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array('user'=>'unknown'),
                    'artwork'=>array(array($artArray)),                   
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('Returns a user`s own artwork in order of latest')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/user',array('action'=>'art','order'=>'latest'))
                ->addArgument('email',true, 'GET')
                ->addArgument('user_id',true, 'GET')
                ->addArgument('limit',false, 'GET')
                ->addArgument('offset',false, 'GET')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array('user'=>'unknown'),
                    'artwork'=>array($artArray),                   
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('Returns a user`s own artwork in order of likes')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/user',array('action'=>'art','order'=>'likes'))
                ->addArgument('email',true, 'GET')
                ->addArgument('user_id',true, 'GET')
                ->addArgument('limit',false, 'GET')
                ->addArgument('offset',false, 'GET')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array('user'=>'unknown'),
                    'artwork'=>array($artArray),                   
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('Returns a user`s own artwork in order of views')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/user',array('action'=>'art','order'=>'views'))
                ->addArgument('email',true, 'GET')
                ->addArgument('user_id',true, 'GET')
                ->addArgument('limit',false, 'GET')
                ->addArgument('offset',false, 'GET')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array('user'=>'unknown'),
                    'artwork'=>array($artArray),                   
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);        
        
        
        /*
         * 
         * ALL ART         
         */
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('All artwork in order of latest')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/art-latest',array('action'=>'latest'))
                ->addArgument('limit',false, 'GET')
                ->addArgument('offset',false, 'GET')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array(),
                    'artwork'=>array($artArray),                   
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('All artwork in order of featured')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/art-featured')
                ->addArgument('limit',false, 'GET')
                ->addArgument('offset',false, 'GET')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array(),
                    'artwork'=>array($artArray),                   
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
               
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('All artwork in order of views')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/art',array('action'=>'views'))
                ->addArgument('limit',false, 'GET')
                ->addArgument('offset',false, 'GET')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array(),
                    'artwork'=>array($artArray),                   
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('All artwork in order of most like count')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/art',array('action'=>'likes'))
                ->addArgument('limit',false, 'GET')
                ->addArgument('offset',false, 'GET')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array(),
                    'artwork'=>array($artArray),                   
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('All artwork based on a set of id`s - delimited by :')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/art')
                ->addArgument('limit',false, 'GET')
                ->addArgument('offset',false, 'GET')
                ->addArgument('art_ids',true, 'GET')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array(),
                    'artwork'=>array($artArray),                   
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription(
                        '<p>Save artwork</p>'.
                        '<p>Image final and thumb can be sourced back from the server</p>'.
                        '<p>Image tag, background and data are image information (not returned back)</p>'
                        )
                //->setAlert('user_email is being used instead of identity token')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/art',array('action'=>'set'))
                ->addArgument('identity_token',true, 'GET')
                ->addArgument('image_final',true, 'POST') // the original image (512 x 512 will be rescaled on the front end to 308x308 making just one request)
                ->addArgument('image_thumb',true, 'POST') // the thumbnail image (sent across already scaled to 131 x 131)
                ->addArgument('image_tag',true, 'POST')
                ->addArgument('image_background',true, 'POST')                
                ->addArgument('data',true, 'POST')
                ->addArgument('lon',true, 'POST')
                ->addArgument('lat',true, 'POST')
                ->addArgument('zone_ref',true, 'POST')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array('user'=>'unknown','submission'=>'invalid','server'=>'error'),  
                    'artwork_id'=>1
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('<p>Report artwork, User must be logged in</p>')
                ->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/art',array('action'=>'report'))
                ->addArgument('identity_token',true, 'POST')
                ->addArgument('art_id',true, 'POST')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array(),                
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('<p>Like artwork</p>')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/art',array('action'=>'like'))
                ->addArgument('identity_token',true, 'POST')
                ->addArgument('art_id',true, 'POST')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array('user'=>'unknown','submission'=>'invalid','art'=>'unknown'),                
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('<p>Unlike artwork</p>')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/art',array('action'=>'unlike'))
                ->addArgument('identity_token',true, 'POST')
                ->addArgument('art_id',true, 'POST')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array('user'=>'unknown','submission'=>'invalid','art'=>'unknown'),                
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('<p>Increment view counter for artwork</p>')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/art',array('action'=>'view'))
                ->addArgument('art_id',true, 'POST')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array('art'=>'unknown'),                
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('<p>Get surrounding artwork based on zone refs delimited by : </p><p>Example 0-0-0:0-1-0</p>')
                ->setAlert('The distance between markers at different zooms needs to be finalised at the momement it is defaulting')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/location')
                ->addArgument('zone_refs',true, 'GET')
                //->addArgument('limit',false, 'GET')
                //->addArgument('offset',false, 'GET')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array(),
                    'zones'=>array(),
                    'min-distance'=>1,
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $apiComponent = new ApiComponent();
        $apiComponent
                ->setDescription('<p>Hall of fame listing, algorithm (TBC)</p>')
                ->setAlert('Returns 100 users in descending order of total_likes & total_views')
                //->setStatus(ApiComponent::UNFINISHED)
                ->setRoute('rest/mostfamous')
                //->addArgument('limit',false, 'GET')
                //->addArgument('offset',false, 'GET')
                ->setReturns(array(
                    'service name'=>'',                    
                    'success'=>true,
                    'errors'=>array(),
                    'users'=>array(array_merge($userArray,array('likes'=>'','views'=>'')))
                    ));
        $api->addComponent($apiComponent);
        unset($apiComponent);
        
        
        $this->layout('layout/html');
        return new ViewModel(array('api'=>$api));

        
    }

}