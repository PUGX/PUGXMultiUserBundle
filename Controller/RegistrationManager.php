<?php

namespace PUGX\MultiUserBundle\Controller;

use PUGX\MultiUserBundle\Model\UserDiscriminator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Controller\RegistrationController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use PUGX\MultiUserBundle\Form\FormFactory;

class RegistrationManager
{
    /**
     * @var \PUGX\MultiUserBundle\Model\UserDiscriminator
     */
    protected $userDiscriminator;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \FOS\UserBundle\Controller\RegistrationController
     */
    protected $controller;

    /**
     * @var \PUGX\MultiUserBundle\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @param \PUGX\MultiUserBundle\Model\UserDiscriminator             $userDiscriminator
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \FOS\UserBundle\Controller\RegistrationController         $controller
     * @param \PUGX\MultiUserBundle\Form\FormFactory                    $formFactory
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
     * @param string $class
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function register($class, $templates = array())
    {
        $this->userDiscriminator->setClass($class);

        $this->controller->setContainer($this->container);
        $result = $this->controller->registerAction($this->container->get('request'));
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
            'templates' => $templates,
        ));
    }
}
