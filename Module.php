<?php
namespace LfAssetsManager;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;

class Module
{ 
    public $basePath;
    public $headLink;
    public $headScript;
    private $currentRoute;
    private $configuration;
    private $assetsVersion;

    /**
     * Module bootsrap
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {   
        $this->getCurrentRoute( $e );

    	$serviceManager             = $e->getApplication()->getServiceManager();
    	$config                     = $serviceManager->get('config');
    	$this->basePath             = $e->getApplication()->getRequest()->getBasePath();
    
    	$this->headLink         = $serviceManager->get('viewhelpermanager')->get('headLink');
    	$this->headScript       = $serviceManager->get('viewhelpermanager')->get('headScript');
    
    	//get assets dependencies configuration
    	$this->configuration    = $config["assetsDependencies"];
    	$this->assetsVersion	= $this->configuration["version"];
    }
    
    /**
     * Get currentRoute
     * @param unknown $e
     */
    private function getCurrentRoute( $e )
    {
    	//injection des CSS et JS pour les routes spécifiques
    	$eventManager        = $e->getApplication()->getEventManager();
    	$moduleRouteListener = new ModuleRouteListener();
    	$moduleRouteListener->attach($eventManager);
    	$module = $this;
    
    	$eventManager->attach( 'route',function($e)use($module)
    	{
    		$router    = $e->getRouter();
    		$request   = $e->getApplication()->getServiceManager()->get('request');
    		$routeName = $router->match($request)->getMatchedRouteName();
    
    		$headLink    = $e->getApplication()->getServiceManager()->get('viewhelpermanager')->get('headLink');
    		$headScript  = $headScript = $e->getApplication()->getServiceManager()->get('viewhelpermanager')->get('headScript');
    		$basePath    = $e->getApplication()->getRequest()->getBasePath();
    
    		$module->injectRouteDependencies( $routeName );
    	});
    }
    
    /**
     * Inject specific files for routes
     * @param unknown $routeName
     */
    public function injectRouteDependencies( $routeName )
    {
    	//INJECT DEFAULT FILES
    	$this->writeCssFile( $this->configuration['default']["css"] );
    	$this->writeJsFile( $this->configuration['default']["js"] );
    
    	//INJECT ROUTE DEPENDENCIES
    	if( array_key_exists( $routeName , $this->configuration ) )
    	{
    		if( array_key_exists( "css", $this->configuration[$routeName]  ) )
    		{
    			$this->writeCssFile( $this->configuration[$routeName]["css"] );
    		}
    		 
    		if( array_key_exists( "js", $this->configuration[$routeName]  ) )
    		{
    			$this->writeJsFile( $this->configuration[$routeName]["js"] );
    		}
    	}
    }
    
    /**
     * Write CSS files
     */
    private function writeCssFile( $files )
    {
    	if( is_array( $files  ) )
    	{
    		foreach( $files as $file )
    		{
    			$this->headLink->appendStylesheet( $this->basePath.'/'.$file);
    		}
    	}
    	else
    	{
    		$this->headLink->appendStylesheet( $this->basePath.'/'.$file);
    	}
    }
    
    /**
     * Write JS files
     */
    private function writeJsFile( $files )
    {
    	if( is_array( $files  ) )
    	{
    		foreach( $files as $file )
    		{
    			$this->headScript->appendFile( $this->basePath.'/'.$file.$this->assetsVersion["separator"].$this->assetsVersion["name"]);
    		}
    	}
    	else
    	{
    		$this->headScript->appendFile( $this->basePath.'/'.$file.$this->assetsVersion["separator"].$this->assetsVersion["name"]);
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
}
