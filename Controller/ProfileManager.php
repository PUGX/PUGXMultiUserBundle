<?php

namespace PUGX\MultiUserBundle\Controller;

use FOS\UserBundle\Controller\ProfileController;
use PUGX\MultiUserBundle\Form\FormFactory;
use PUGX\MultiUserBundle\Model\UserDiscriminator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ProfileManager extends Controller
{
    /**
     * @var UserDiscriminator
     */
    protected $userDiscriminator;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ProfileController
     */
    protected $controller;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @param UserDiscriminator  $userDiscriminator
     * @param ContainerInterface $container
     * @param ProfileController  $controller
     * @param FormFactory        $formFactory
     */
    public function __construct(
        UserDiscriminator $userDiscriminator,
        ContainerInterface $container,
        ProfileController $controller,
        FormFactory $formFactory
    ) {
        $this->userDiscriminator = $userDiscriminator;
        $this->container = $container;
        $this->controller = $controller;
        $this->formFactory = $formFactory;
    }

    /**
     * @param string $class
     *
     * @return RedirectResponse
     */
    public function edit($class)
    {
        $this->userDiscriminator->setClass($class);

        $this->controller->setContainer($this->container);
        $result = $this->controller->editAction($this->getRequest());
        if ($result instanceof RedirectResponse) {
            $result;
        }

        $template = $this->userDiscriminator->getTemplate('profile');
        if (is_null($template)) {
            $template = 'FOSUserBundle:Profile:edit.html.twig';
        }

        $form = $this->formFactory->createForm();

        return $this->container->get('templating')->renderResponse($template, [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param string $class
     * @return RedirectResponse
     */
    public function show($class)
    {
        $user =  $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $this->userDiscriminator->setClass($class);


        $this->controller->setContainer($this->container);
        $result = $this->controller->showAction($this->getRequest());
        if ($result instanceof RedirectResponse) {
            return $this->controller->redirect($this->getRequest()->getRequestUri());
        }

        $template = $this->userDiscriminator->getTemplate('profile');
        if (is_null($template)) {
            $template = 'FOSUserBundle:Profile:show.html.twig';
        }

       
        return $this->container->get('templating')->renderResponse($template, array(
            'user' => $user
        ));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request;
     */
    public function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
}
