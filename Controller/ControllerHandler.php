<?php

namespace PUGX\MultiUserBundle\Controller;

use PUGX\MultiUserBundle\Model\UserDiscriminator;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PUGX\MultiUserBundle\Controller\ControllerAwareFactory;
use PUGX\MultiUserBundle\Event\ContainerChangeEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use PUGX\MultiUserBundle\Event\ManualLoginEvent;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Request;

class ControllerHandler
{
    /**
     *
     * @var UserDiscriminator 
     */
    protected $userDiscriminator;
    
    /**
     *
     * @var SecurityContextInterface 
     */
    protected $securityContext;
    
    /**
     *
     * @var EventDispatcherInterface 
     */
    protected $eventDispatcher;
    
    /**
     *
     * @var ControllerAwareFactory 
     */
    protected $controllerFactory;
    
    const POST_ACTION_NAME = 'pugx_multi_user_post_action';
    
    /**
     *
     * @param UserDiscriminator $userDiscriminator
     * @param SecurityContextInterface $securityContext
     * @param EventDispatcherInterface $eventDispatcher 
     */
    public function __construct(UserDiscriminator $userDiscriminator, SecurityContextInterface $securityContext, EventDispatcherInterface $eventDispatcher, ControllerAwareFactory $controllerFactory)
    {
        $this->userDiscriminator = $userDiscriminator;
        $this->securityContext = $securityContext;
        $this->eventDispatcher = $eventDispatcher;
        $this->controllerFactory = $controllerFactory;
    }
    
    /**
     *
     * @param string $userClass 
     */
    public function registration($userClass, Request $request)
    {
        $this->userDiscriminator->setClass($userClass);

        $formFactory = $this->userDiscriminator->getRegistrationFormFactory($userClass);
        
        $event = new ContainerChangeEvent('fos_user.registration.form.factory', $formFactory);
        $this->eventDispatcher->dispatch('pugx_multi_user.change_container_value', $event);
        
        $controller = $this->controllerFactory->build('Registration');
        $return = $controller->registerAction($request);
        
        $this->dispatchManualLogin();
        
        return $return;
    }
    
     /**
     *
     * @param string $userClass
     * @param Request $request
     * @return
     */
    public function profile($userClass, Request $request)
    {

        $this->userDiscriminator->setClass($userClass);

        $formFactory = $this->userDiscriminator->getProfileFormFactory($userClass);

        $event = new ContainerChangeEvent('fos_user.profile.form.factory', $formFactory);

        $this->eventDispatcher->dispatch('pugx_multi_user.change_container_value', $event);

        $controller = $this->controllerFactory->build('Profile');

        $return = $controller->editAction($request);

        return $return;
    }
    
    /**
     * dispatch an event to manual login 
     */
    protected function dispatchManualLogin()
    {
        $user = $this->securityContext->getToken()->getUser(); 
        if (is_object($user) && $user instanceof UserInterface) {
            $event = new ManualLoginEvent($user);
            $this->eventDispatcher->dispatch('security.manual_login', $event);
        }
    }


    /**
     *
     * @param string $controller
     * @param string $method 
     */
    public function handlerPre($controller, $method, Request $request)
    {
        $reflectionController = new \ReflectionObject($controller);
        
        $controllerName = str_replace('Controller', '', $reflectionController->getShortName());
        $methodName     = str_replace('Action', '', $method);
        
        $handlerMethod = 'handlerPre' . ucfirst($controllerName) . ucfirst($methodName);
        
        if (method_exists($this, $handlerMethod)) {
            return $this->$handlerMethod($request);
        }
        
        return null;
    }
    
    /**
     *
     * @param string $postAction
     * @return null 
     */
    public function handlerPost($postAction)
    {
        $handlerMethod = 'handlerPost' . ucfirst($postAction);
        
        if (method_exists($this, $handlerMethod)) {
            return $this->$handlerMethod();
        }
        
        return null;
    }
    
    //STRATEGY HANDLERS
    
    /**
     *
     * @param Request $request 
     */
    protected function handlerPreProfileEdit(Request $request)
    {
        $form = $this->userDiscriminator->getProfileForm();
        
        $event = new ContainerChangeEvent('fos_user.profile.form', $form);
        $this->eventDispatcher->dispatch('pugx_multi_user.change_container_value', $event);
    }
    
    
    /**
     *
     * @param Request $request 
     */
    protected function handlerPreRegistrationConfirm(Request $request)
    {
        $request->attributes->set(static::POST_ACTION_NAME, 'registrationConfirm');
    }
    
    /**
     *
     * @param Request $request 
     */
    protected function handlerPreResettingReset(Request $request)
    {
        $request->attributes->set(static::POST_ACTION_NAME, 'resettingReset');
    }
    
    /**
     * simply login
     */
    protected function handlerPostRegistrationConfirm()
    {
        $this->dispatchManualLogin();
    }
    
    /**
     * simply login
     */
    protected function handlerPostResettingReset()
    {
        $this->dispatchManualLogin();
    }
}
