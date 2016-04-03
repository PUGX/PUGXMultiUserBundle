<?php

namespace Massil\MultiUserBundle\Controller;

use Massil\MultiUserBundle\Model\UserDiscriminator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Controller\RegistrationController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Massil\MultiUserBundle\Form\FormFactory;

class RegistrationManager
{
    /**
     *
     * @var \Massil\MultiUserBundle\Model\UserDiscriminator
     */
    protected $userDiscriminator;

    /**
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     *
     * @var \FOS\UserBundle\Controller\RegistrationController
     */
    protected $controller;

    /**
     *
     * @var \Massil\MultiUserBundle\Form\FormFactory
     */
    protected $formFactory;

    /**
     *
     * @param \Massil\MultiUserBundle\Model\UserDiscriminator $userDiscriminator
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \FOS\UserBundle\Controller\RegistrationController $controller
     * @param \Massil\MultiUserBundle\Form\FormFactory $formFactory
     */
    public function __construct(UserDiscriminator $userDiscriminator,
                                ContainerInterface $container,
                                RegistrationController $controller,
                                FormFactory $formFactory)
    {
        $this->userDiscriminator = $userDiscriminator;
        $this->container = $container;
        $this->controller = $controller;
        $this->formFactory = $formFactory;
    }

    /**
     *
     * @param string $class
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function register($class)
    {
        $this->userDiscriminator->setClass($class);
        $this->controller->setContainer($this->container);
        $result = $this->controller->registerAction($this->getRequest());
        if ($result instanceof RedirectResponse) {
            return $result;
        }

        $template = $this->userDiscriminator->getTemplate('registration');
        if (is_null($template)) {
            $template = 'FOSUserBundle:Registration:register.html.twig';
        }

        $form = $this->formFactory->createForm();
        return $this->container->get('templating')->renderResponse($template, array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request;
     */
    private function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
}
