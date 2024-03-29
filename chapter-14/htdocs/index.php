<?php
    require_once('Zend/Loader.php');
    Zend_Loader::registerAutoload();

    // setup the application logger
    $logger = new Zend_Log(new Zend_Log_Writer_Null());

    try {
        $writer = new EmailLogger($_SERVER['SERVER_ADMIN']);
        $writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::CRIT));
        $logger->addWriter($writer);


        // load the application configuration
        $configFile = '';
        if (isset($_SERVER['APP_CONFIG_FILE']))
            $configFile = basename($_SERVER['APP_CONFIG_FILE']);

        if (strlen($configFile) == 0)
            $configFile = 'settings.ini';

        $configSection = '';
        if (isset($_SERVER['APP_CONFIG_SECTION']))
            $configSection = basename($_SERVER['APP_CONFIG_SECTION']);

        if (strlen($configSection) == 0)
            $configSection = 'production';

        $config = new Zend_Config_Ini('../' . $configFile, $configSection);
        Zend_Registry::set('config', $config);


        // alter the application logger
        $logger->addWriter(new Zend_Log_Writer_Stream($config->logging->file));
        $writer->setEmail($config->logging->email);

        Zend_Registry::set('logger', $logger);


        // connect to the database
        $params = array('host'     => $config->database->hostname,
                        'username' => $config->database->username,
                        'password' => $config->database->password,
                        'dbname'   => $config->database->database);

        $db = Zend_Db::factory($config->database->type, $params);
        $db->getConnection();

        Zend_Registry::set('db', $db);


        // setup application authentication
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session());


        // handle the user request
        $controller = Zend_Controller_Front::getInstance();
        $controller->setControllerDirectory($config->paths->base .
                                            '/include/Controllers');
        $controller->registerPlugin(new CustomControllerAclManager($auth));

        // setup the view renderer
        $vr = new Zend_Controller_Action_Helper_ViewRenderer();
        $vr->setView(new Templater());
        $vr->setViewSuffix('tpl');
        Zend_Controller_Action_HelperBroker::addHelper($vr);

        // setup the route for user home pages
        $route = new Zend_Controller_Router_Route(
            'user/:username/:action/*',
            array('controller' => 'user',
                  'action'     => 'index')
        );

        $controller->getRouter()->addRoute('user', $route);

        // set up the route for viewing blog posts
        $route = new Zend_Controller_Router_Route(
            'user/:username/view/:url/*',
            array('controller' => 'user',
                  'action'     => 'view')
        );

        $controller->getRouter()->addRoute('post', $route);

        // set up the route for viewing monthly archives
        $route = new Zend_Controller_Router_Route(
            'user/:username/archive/:year/:month/*',
            array('controller' => 'user',
                  'action'     => 'archive')
        );

        $controller->getRouter()->addRoute('archive', $route);

        // setup the route for user tag spaces
        $route = new Zend_Controller_Router_Route(
            'user/:username/tag/:tag/*',
            array('controller' => 'user',
                  'action' => 'tag')
        );

        $controller->getRouter()->addRoute('tagspace', $route);

        $controller->dispatch();
    }
    catch (Exception $ex) {
        $logger->emerg($ex->getMessage());

        header('Location: /error.html');
        exit;
    }
?>