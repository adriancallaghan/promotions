<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Application\Model\Art;
use Application\Model\ArtTable;
use Application\Model\Zone;
use Application\Model\ZoneTable;
use Application\Model\User;
use Application\Model\UserTable;
use Application\Model\Like;
use Application\Model\LikeTable;
use Application\Model\Mailer;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    
    public function onBootstrap(MvcEvent $e)
    {
        $e->getApplication()->getServiceManager()->get('translator');
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Application\Model\ArtTable' =>  function($sm) {
                    $tableGateway = $sm->get('ArtTableGateway');
                    $table = new ArtTable($tableGateway);
                    return $table;
                },
                'ArtTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Art());
                    return new TableGateway('art', $dbAdapter, null, $resultSetPrototype);
                },
                'Application\Model\LikeTable' =>  function($sm) {
                    $tableGateway = $sm->get('LikeTableGateway');
                    $table = new LikeTable($tableGateway);
                    return $table;
                },
                'LikeTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Like());
                    return new TableGateway('like', $dbAdapter, null, $resultSetPrototype);
                },
                'Application\Model\ZoneTable' =>  function($sm) {
                    $tableGateway = $sm->get('ZoneTableGateway');
                    $table = new ZoneTable($tableGateway);
                    return $table;
                },
                'ZoneTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Zone());
                    return new TableGateway('zone', $dbAdapter, null, $resultSetPrototype);
                },     
                'Application\Model\UserTable' =>  function($sm) {
                    $tableGateway = $sm->get('UserTableGateway');
                    $table = new UserTable($tableGateway);
                    return $table;
                },
                'UserTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new User());
                    return new TableGateway('user', $dbAdapter, null, $resultSetPrototype);
                },   
                'Application\Model\UserArtTable' =>  function($sm) {
                    $tableGateway = $sm->get('UserTableGateway');
                    $table = new UserTable($tableGateway);
                    return $table;
                },                  
                'SystemLogger' => function ($sm) {
                    
                    $config = $sm->get('config');                    
                    $logger = new \Zend\Log\Logger;
                    
                    try {
                        $writer = new \Zend\Log\Writer\Stream($config['loggers']['system']);
                        $logger->addWriter($writer);
                        return $logger;
                    }
                    catch(\Zend\Log\Exception\RuntimeException $e){
                        echo $e->getMessage();
                        return null;
                    }
                    
                },   
                'MailLogger' => function ($sm) {
                    
                    $config = $sm->get('config');                    
                    $logger = new \Zend\Log\Logger;
                    
                    try {
                        $writer = new \Zend\Log\Writer\Stream($config['loggers']['mail']);
                        $logger->addWriter($writer);
                        return $logger;
                    }
                    catch(\Zend\Log\Exception\RuntimeException $e){
                        echo $e->getMessage();
                        return null;
                    }
                },
                'Mailer'=>function($sm){

                    $mailLogger = $sm->get('MailLogger');                    
                    $mailer = new Mailer();
                    $mailer->setLogger($mailLogger);
                    return $mailer;

                }
            ),
        );
    }
}
