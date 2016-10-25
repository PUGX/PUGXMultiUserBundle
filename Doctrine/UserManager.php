<?php

namespace PUGX\MultiUserBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\ORMException;
use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
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
    public function __construct(PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater, ObjectManager $om, $class, UserDiscriminator $userDiscriminator)
    {
        $this->om = $om;
        $this->userDiscriminator = $userDiscriminator;

        parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, $class);
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

            // Some models does not have the property associated to the criteria
            try {
                $user = $repo->findOneBy($criteria);
            } catch (ORMException $e) {
                $user = null;
            }

            if ($user) {
                $this->userDiscriminator->setClass($class);
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

        $usersAll = array();
        foreach ($classes as $class) {
            $repo = $this->om->getRepository($class);

            $users = $repo->findAll();

            if ($users) {
                $usersAll = array_merge($usersAll, $users); // $usersAll
            }
        }

        return $usersAll;
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
}
