<?php

namespace PUGX\MultiUserBundle\Tests\Controller;

use PUGX\MultiUserBundle\Controller\ControllerHandler;
use PUGX\MultiUserBundle\Event\ContainerChangeEvent;
use PUGX\MultiUserBundle\Event\ManualLoginEvent;
use Symfony\Component\HttpFoundation\ParameterBag;

use PUGX\MultiUserBundle\Tests\Stub\RegistrationController;
use PUGX\MultiUserBundle\Tests\Stub\ProfileController;
use PUGX\MultiUserBundle\Tests\Stub\ResettingController;

class ControllerHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->userDiscriminator = $this->getMockBuilder('PUGX\MultiUserBundle\Model\UserDiscriminator')
                ->disableOriginalConstructor()->getMock();
        $this->securityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
                ->disableOriginalConstructor()->getMock();
        $this->eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
                ->disableOriginalConstructor()->getMock();
        $this->controllerFactory = $this->getMockBuilder('PUGX\MultiUserBundle\Controller\ControllerAwareFactory')
                ->disableOriginalConstructor()->getMock();
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
                ->disableOriginalConstructor()->getMock();
        $this->formFactory = $this->getMockBuilder('FOS\UserBundle\Form\Factory\FormFactory')
            ->disableOriginalConstructor()->getMock();
        $this->registrationController = $this->getMockBuilder('FOS\UserBundle\Controller\RegistrationController')
                ->disableOriginalConstructor()->getMock();
        $this->response = $this->getMockBuilder('Symfony\Component\HttpFoundation\RedirectResponse')
                ->disableOriginalConstructor()->getMock();
        $this->user = $this->getMockBuilder('FOS\UserBundle\Model\UserInterface')
                ->disableOriginalConstructor()->getMock();
        $this->token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
                ->disableOriginalConstructor()->getMock();
        
        //mocking a stub that extends Symfony\Component\HttpFoundation\Request solve this error:
        //PHP Fatal error:  __clone method called on non-object in Symfony/Component/HttpFoundation/Request.php on line 382
        //that happen if the request are passed as parameter
        $this->request = $this->getMockBuilder('PUGX\MultiUserBundle\Tests\Stub\Request')
                ->disableOriginalConstructor()->getMock();  
        
        $this->request->attributes = new ParameterBag(array());
        
        $this->handler = new ControllerHandler($this->userDiscriminator, $this->securityContext, $this->eventDispatcher, $this->controllerFactory);
    }
    
    public function testRegistration()
    {
        $this->userDiscriminator->expects($this->exactly(1))->method('setClass')
                ->with('MyFirstUserClass');
        $this->userDiscriminator->expects($this->exactly(1))->method('getRegistrationFormFactory')
            ->with('MyFirstUserClass')
            ->will($this->returnValue($this->formFactory));
        $this->eventDispatcher->expects($this->at(0))->method('dispatch')
                ->with('pugx_multi_user.change_container_value', new ContainerChangeEvent('fos_user.registration.form.factory', $this->formFactory));
        $this->controllerFactory->expects($this->exactly(1))->method('build')
                ->with('Registration')
                ->will($this->returnValue($this->registrationController));
        $this->registrationController->expects($this->exactly(1))->method('registerAction')
                ->with($this->request)
		->will($this->returnValue($this->response));
        $this->securityContext->expects($this->exactly(1))->method('getToken')
                ->will($this->returnValue($this->token));
        $this->token->expects($this->exactly(1))->method('getUser')
                ->will($this->returnValue($this->user));
        $this->eventDispatcher->expects($this->at(1))->method('dispatch')
                ->with('security.manual_login', new ManualLoginEvent($this->user));
        
        $result = $this->handler->registration('MyFirstUserClass', $this->request);
        $this->assertEquals($this->response, $result);
    }
        
    public function testHandlerPreRegistrationConfirm()
    {
        $controller = new RegistrationController();
        $method     = 'confirmAction';
        
        $result = $this->handler->handlerPre($controller, $method, $this->request);
        
        $this->assertEquals('registrationConfirm', $this->request->attributes->get(ControllerHandler::POST_ACTION_NAME));
    }
    
    public function testHandlerPreResettingReset()
    {
        $controller = new ResettingController();
        $method     = 'resetAction';
        
        $result = $this->handler->handlerPre($controller, $method, $this->request);
        
        $this->assertEquals('resettingReset', $this->request->attributes->get(ControllerHandler::POST_ACTION_NAME));
    }
    
    public function testHandlerPreProfileEdit()
    {
        $controller = new ProfileController();
        $method     = 'editAction';
        
        $this->userDiscriminator->expects($this->exactly(1))->method('getProfileForm')
                ->will($this->returnValue($this->form));
        $this->eventDispatcher->expects($this->exactly(1))->method('dispatch')
                ->with('pugx_multi_user.change_container_value', new ContainerChangeEvent('fos_user.profile.form', $this->form));
        
        $result = $this->handler->handlerPre($controller, $method, $this->request);
    }
    
    public function testHandlerPostLogin()
    {        
        $this->securityContext->expects($this->exactly(1))->method('getToken')
                ->will($this->returnValue($this->token));
        $this->token->expects($this->exactly(1))->method('getUser')
                ->will($this->returnValue($this->user));
        $this->eventDispatcher->expects($this->exactly(1))->method('dispatch')
                ->with('security.manual_login', new ManualLoginEvent($this->user));
        
        $result = $this->handler->handlerPost('registrationConfirm');
    }
}
