<?php

namespace PUGX\MultiUserBundle\Listener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\UserEvent;
use PUGX\MultiUserBundle\Model\UserDiscriminator;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;

class AuthenticationListener implements EventSubscriberInterface
{
    /**
     *
     * @var UserDiscriminator 
     */
    protected $userDiscriminator;
    
    /**
     *
     * @param UserDiscriminator $controllerHandler 
     */
    public function __construct(UserDiscriminator $userDiscriminator)
    {
        $this->userDiscriminator = $userDiscriminator;
    }

    /**
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::SECURITY_IMPLICIT_LOGIN => 'onSecurityImplicitLogin',
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
            SecurityEvents::SWITCH_USER => 'onSecuritySwitchUser'
        );
    }
    
    protected function discriminate($user)
    {
        $this->userDiscriminator->setClass(get_class($user), true);
    }

    /**
     * 
     * @param \FOS\UserBundle\Event\UserEvent $event
     */
    public function onSecurityImplicitLogin(UserEvent $event)
    {
        $this->discriminate($event->getUser());
    }
    
    /**
     * 
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $this->discriminate($event->getAuthenticationToken()->getUser());
    }
    
    /**
     * 
     * @param \Symfony\Component\Security\Http\Event\SwitchUserEvent $event
     */
    public function onSecuritySwitchUser(SwitchUserEvent $event)
    {
        $this->discriminate($event->getTargetUser());
    }
}
