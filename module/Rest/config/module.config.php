<?php
return array(
    
    'controllers' => array(
        'invokables' => array(
            'Rest\Controller\Index'         => 'Rest\Controller\IndexController',
            'Rest\Controller\User'          => 'Rest\Controller\UserController',
            'Rest\Controller\Art'           => 'Rest\Controller\ArtController',
            'Rest\Controller\Location'      => 'Rest\Controller\LocationController',
            'Rest\Controller\Mostfamous'    => 'Rest\Controller\MostfamousController',
        ),
    ),
    

    'router' => array(
        'routes' => array(

            'rest' => array(
                
                'may_terminate' => true, 
                'type'          => 'Literal',
                    
                'options'   => array(
                    'route'     => '/rest/',
                    'defaults'  => array(
                        '__NAMESPACE__' => 'Rest\Controller',
                        'controller'    => 'Index', 
                        'action'        => 'index',
                    ),
                ),                

                'child_routes' => array(

                    'user'  => array(
                        'type'      => 'Segment',
                        'options'   => array(
                            'route'     => 'user[/:action][/:id][/:order]',
                            'constraints'   => array(
                                'action'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'        => '[0-9]+',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Rest\Controller',
                                'controller'    => 'User',
                                'action'        => 'index',
                            ),
                        ),
                    ),
                    'art' => array(
                        'may_terminate' => true, 
                        'type'      => 'Segment',
                        'options'   => array(
                            'route'         => 'art[/:action][/:id]',
                            'constraints'       => array(
                                'action'        => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'            => '[0-9]+',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Rest\Controller',
                                'controller'    => 'Art',
                                'action'        => 'get',
                            ),
                        ),                    
                    ), 
                    
                    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
                     * Art listing types
                     */
                    'art-latest' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => 'art/latest',                                    
                            'defaults' => array(
                                '__NAMESPACE__' => 'Rest\Controller',
                                'controller'    => 'Art',
                                'action'        => 'list',
                            ),
                        ),
                    ),
                    'art-featured' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => 'art/featured',                                    
                            'defaults' => array(
                                '__NAMESPACE__' => 'Rest\Controller',
                                'controller'    => 'Art',
                                'action'        => 'list',
                                'order'         => 'featured'
                            ),
                        ),
                    ),
                    'art-views' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => 'art/views',                                    
                            'defaults' => array(
                                '__NAMESPACE__' => 'Rest\Controller',
                                'controller'    => 'Art',
                                'action'        => 'list',
                                'order'         => 'views'
                            ),
                        ),
                    ),
                    'art-likes' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => 'art/likes',                                    
                            'defaults' => array(
                                '__NAMESPACE__' => 'Rest\Controller',
                                'controller'    => 'Art',
                                'action'        => 'list',
                                'order'         => 'likes'
                            ),
                        ),
                    ),
                    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
                     * Art listing types
                     */
                    
                    'location' => array(
                        'type'      => 'Segment',
                        'options'   => array(
                            'route'     => 'location[/:action][/:id]',
                            'constraints'   => array(
                                'action'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'        => '[0-9]+',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Rest\Controller',
                                'controller'    => 'Location',
                                'action'        => 'index',
                            ),
                        ),
                    ),
                    'mostfamous'    => array(
                        'type'      => 'Segment',
                        'options'   => array(
                            'route'     => 'mostfamous[/:action][/:id]',
                            'constraints'   => array(
                                'action'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'        => '[0-9]+',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Rest\Controller',
                                'controller'    => 'Mostfamous',
                                'action'        => 'index',
                            ),
                        ),
                    ),
                ),
                
            )  

        ),
    ),

    
    'view_manager' => array(
        'template_path_stack' => array(
            'rest' => __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);

