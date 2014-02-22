<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

/*
use Application\Model\Album;
use Application\Model\AlbumTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
*/

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $app            = $e->getApplication();
        $eventManager   = $app->getEventManager();
        $request        = $e->getRequest();
        $sm             = $app->getServiceManager();
        $em             = $sm->get('Doctrine\ORM\EntityManager');
        $routesEntity   = $em->getRepository('Application\Entity\Routes');
        
        $moduleRouteListener    = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $route          = $routesEntity->findOneBy(array(
                        'route'  => $request->getUri()->getPath(),
                        'domain' => $request->getUri()->getHost()
                        ));
        
        if ($route){
            die
            // trigger controller
            $route->template;
            //$e->setRequest($request);
            //$eventManager->setEventClass($route->template); 
        }


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
    
    /*public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Application\Model\AlbumTable' =>  function($sm) {
                    $tableGateway = $sm->get('AlbumTableGateway');
                    $table = new AlbumTable($tableGateway);
                    return $table;
                },
                'AlbumTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Album());
                    return new TableGateway('album', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }*/

}
