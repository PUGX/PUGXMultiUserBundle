<?php

namespace PUGX\MultiUserBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Validator\Constraint;
use PUGX\MultiUserBundle\Model\UserDiscriminator;

/**
 * Custom user manager for FOSUserBundle
 *
 * @author leonardo proietti (leonardo.proietti@gmail.com)
 * @author eux (eugenio@netmeans.net)
 */
class UserManager extends BaseUserManager
{ 
    /**
     *
     * @var ObjectManager 
     */
    protected $om;
        
    /**
     *
     * @var UserDiscriminator 
     */
    protected $userDiscriminator;

    /**
     * Constructor.
     *
     * @param EncoderFactoryInterface $encoderFactory
     * @param CanonicalizerInterface  $usernameCanonicalizer
     * @param CanonicalizerInterface  $emailCanonicalizer
     * @param ObjectManager           $om
     * @param string                  $class
     * @param UserDiscriminator       $userDiscriminator
     */
    public function __construct(EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $usernameCanonicalizer, CanonicalizerInterface $emailCanonicalizer, ObjectManager $om, $class, UserDiscriminator $userDiscriminator)
    {
        $this->om = $om;
        $this->userDiscriminator = $userDiscriminator;
        
        parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer, $om, $class);
    }
    
    /**
     *
     * {@inheritDoc}
     */
    public function createUser()
    {
        return $this->userDiscriminator->createUser();
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->userDiscriminator->getClass();
    }

    /**
     * {@inheritDoc}
     */
    public function findUserBy(array $criteria)
    {        
        $classes = $this->userDiscriminator->getClasses();
                
        foreach ($classes as $class) {

            $repo = $this->om->getRepository($class);
            
            if (!$repo) {
                throw new \LogicException(sprintf('Repository "%s" not found', $class));
            }
                        
            $user = $repo->findOneBy($criteria);
            
            if ($user) {                
                return $user;
            }
        }
        
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function findUsers()
    {
        $classes = $this->userDiscriminator->getClasses();
        
        $users = array();
        foreach ($classes as $class) {
            $repo = $this->om->getRepository($class);
                        
            $users = $repo->findAll();
            
            if ($users) {
                $users = array_merge($users, $users);
            }               
        }
        
        return $users;
    }


    /**
     * {@inheritDoc}
     */
    protected function findConflictualUsers($value, array $fields)
    {
        $classes = $this->userDiscriminator->getClasses();
                
        foreach ($classes as $class) {

            $repo = $this->om->getRepository($class);
                        
            $users = $repo->findBy($this->getCriteria($value, $fields));
            
            if (count($users) > 0) {                
                return $users;
            }
        }

        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(SecurityUserInterface $user)
    {
        $class = $this->getClass();
        // Ex. when we switch user, $class is an old one and $user is a new one
        // so this checking from parent code is not passed.
        // Check if class is registered in discriminator and use this class
        if (!$user instanceof $class) {
            $newClass = get_class($user);
            if (in_array($newClass, $this->userDiscriminator->getClasses())) {
                $this->userDiscriminator->setClass($newClass);
            }
        }

        return parent::refreshUser($user);
    }
}
