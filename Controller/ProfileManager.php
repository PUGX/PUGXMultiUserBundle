<?php

namespace PUGX\MultiUserBundle\Controller;

use PUGX\MultiUserBundle\Model\UserDiscriminator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Controller\ProfileController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use PUGX\MultiUserBundle\Form\FormFactory;

class ProfileManager
{
    /**
     *
     * @var \PUGX\MultiUserBundle\Model\UserDiscriminator
     */
    protected $userDiscriminator;

    /**
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     *
     * @var \FOS\UserBundle\Controller\ProfileController
     */
    protected $controller;

    /**
     *
     * @var \PUGX\MultiUserBundle\Form\FormFactory
     */
    protected $formFactory;

    /**
     *
     * @param \PUGX\MultiUserBundle\Model\UserDiscriminator $userDiscriminator
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \FOS\UserBundle\Controller\ProfileController $controller
     * @param \PUGX\MultiUserBundle\Form\FormFactory $formFactory
     */
    public function __construct(UserDiscriminator $userDiscriminator,
                                ContainerInterface $container,
                                ProfileController $controller,
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
    public function profile($class)
    {
        $this->userDiscriminator->setClass($class);

        $this->controller->setContainer($this->container);
        $result = $this->controller->editAction($this->container->get('request'));
        if ($result instanceof RedirectResponse) {
            return $result;
        }

        $template = $this->userDiscriminator->getTemplate('profile');
        if (is_null($template)) {
            $engine = $this->container->getParameter('fos_user.template.engine');
            $template = 'FOSUserBundle:Profile:edit.html.'.$engine;
        }

        $form = $this->formFactory->createForm();
        return $this->container->get('templating')->renderResponse($template, array(
            'form' => $form->createView(),
        ));
    }
}
