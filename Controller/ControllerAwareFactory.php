<?php

namespace PUGX\MultiUserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Controller\RegistrationController;
use FOS\UserBundle\Controller\ProfileController;

class ControllerAwareFactory
{
    /**
     *
     * @var ContainerInterface 
     */
    protected $container;
    
    /**
     *
     * @param ContainerInterface $container 
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     *
     * @param string $controller 
     */
    public function build($controller)
    {
        $factoryMethod = 'get' . ucfirst($controller) . 'Controller';
        if (!method_exists($this, $factoryMethod)) {
            throw new \LogicException(sprintf('The factory for "%s" controller was not found', $controller));
        }
        
        $controller = $this->$factoryMethod();
        $controller->setContainer($this->container);
        
        return $controller;
    }
    
    protected function getRegistrationController()
    {
        return new RegistrationController();
    }
    
        protected function getProfileController()
    {
        return new ProfileController();
    }
}