<?php

namespace PUGX\MultiUserBundle\Tests\Controller;

use PUGX\MultiUserBundle\Controller\ProfileManager;

class ProfileManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->discriminator = $this->getMockBuilder('PUGX\MultiUserBundle\Model\UserDiscriminator')
                ->disableOriginalConstructor()->getMock();

        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
                ->disableOriginalConstructor()->getMock();

        $this->controller = $this->getMockBuilder('FOS\UserBundle\Controller\ProfileController')
                ->disableOriginalConstructor()->getMock();

        $this->formFactory = $this->getMockBuilder('PUGX\MultiUserBundle\Form\FormFactory')
                ->disableOriginalConstructor()->getMock();

        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                ->disableOriginalConstructor()->getMock();

        $this->redirectResponse = $this->getMockBuilder('Symfony\Component\HttpFoundation\RedirectResponse')
                ->disableOriginalConstructor()->getMock();

        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
                ->disableOriginalConstructor()->getMock();

        $this->twig = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')
                ->disableOriginalConstructor()->getMock();

        $this->formView = $this->getMockBuilder('Symfony\Component\Form\FormView')
                ->disableOriginalConstructor()->getMock();

        $this->userManager = new ProfileManager($this->discriminator, $this->container, $this->controller, $this->formFactory);
    }

    public function common()
    {
        $this->discriminator
                ->expects($this->exactly(1))
                ->method('setClass')
                ->with('MyUser');

        $this->controller
                ->expects($this->exactly(1))
                ->method('setContainer')
                ->with($this->container);

        $this->container
                ->expects($this->at(0))
                ->method('get')
                ->with('request')
                ->will($this->returnValue($this->request));
    }

    public function testProfileReturnRedirectResponse()
    {
        $this->common();

        $this->controller
                ->expects($this->exactly(1))
                ->method('editAction')
                ->with($this->request)
                ->will($this->returnValue($this->redirectResponse));

        $result = $this->userManager->edit('MyUser');

        $this->assertSame($result, $this->redirectResponse);
    }

    public function testProfileReturnDefaultTemplate()
    {
        $this->common();

        $this->controller
                ->expects($this->exactly(1))
                ->method('editAction')
                ->with($this->request)
                ->will($this->returnValue(null));

        $this->discriminator
                ->expects($this->exactly(1))
                ->method('getTemplate')
                ->with('profile')
                ->will($this->returnValue(null));

        $this->formFactory
                ->expects($this->exactly(1))
                ->method('createForm')
                ->will($this->returnValue($this->form));

        $this->container
                ->expects($this->at(1))
                ->method('get')
                ->with('templating')
                ->will($this->returnValue($this->twig));

        $this->twig
                ->expects($this->exactly(1))
                ->method('renderResponse')
                ->with('FOSUserBundle:Profile:edit.html.twig', array('form' => $this->formView, 'templates' => array()));

        $this->form
                ->expects($this->exactly(1))
                ->method('createView')
                ->will($this->returnValue($this->formView));

        $result = $this->userManager->edit('MyUser');
    }

    public function testProfileReturnSpecificTemplate()
    {
        $this->common();

        $this->controller
                ->expects($this->exactly(1))
                ->method('editAction')
                ->with($this->request)
                ->will($this->returnValue(null));

        $this->discriminator
                ->expects($this->exactly(1))
                ->method('getTemplate')
                ->with('profile')
                ->will($this->returnValue('PUGXMultiUserBundle:Profile:edit.html.twig'));

        $this->formFactory
                ->expects($this->exactly(1))
                ->method('createForm')
                ->will($this->returnValue($this->form));

        $this->container
                ->expects($this->at(1))
                ->method('get')
                ->with('templating')
                ->will($this->returnValue($this->twig));

        $this->twig
                ->expects($this->exactly(1))
                ->method('renderResponse')
                ->with('PUGXMultiUserBundle:Profile:edit.html.twig', array('form' => $this->formView, 'templates' => array()));

        $this->form
                ->expects($this->exactly(1))
                ->method('createView')
                ->will($this->returnValue($this->formView));

        $result = $this->userManager->edit('MyUser', $templates = array());
    }
}
