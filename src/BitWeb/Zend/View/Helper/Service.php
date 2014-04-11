<?php

namespace BitWeb\Zend\View\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;

class Service extends AbstractHelper implements ServiceLocatorAwareInterface
{

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    public function setServiceLocator(ServiceLocatorInterface $pluginManager)
    {
        /* @var $pluginManager \Zend\ServiceManager\AbstractPluginManager */
        $this->serviceLocator = $pluginManager->getServiceLocator();
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function __invoke($name)
    {
        if ($this->serviceLocator == null) {
            $this->serviceLocator = $this->helperPluginManager->getServiceLocator();
        }

        return $this->serviceLocator->get($name);
    }
}
