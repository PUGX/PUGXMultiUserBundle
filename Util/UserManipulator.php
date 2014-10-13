<?php

namespace PUGX\MultiUserBundle\Util;

use FOS\UserBundle\Util\UserManipulator as BaseUserManipulator;

use FOS\UserBundle\Model\UserManagerInterface;
use PUGX\MultiUserBundle\Doctrine\UserManager;

/**
 * Executes some manipulations on the users
 */
class UserManipulator extends BaseUserManipulator
{
    /**
     * User manager
     *
     * @var UserManagerInterface
     */
    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }
    /**
     * Creates a user by type and returns it.
     *
     * @param string  $type
     * @param string  $username
     * @param string  $password
     * @param string  $email
     * @param Boolean $active
     * @param Boolean $superadmin
     *
     * @return \FOS\UserBundle\Model\UserInterface
     */
    public function createByType($type, $username, $password, $email, $active, $superadmin)
    {
        $user = $this->userManager->createUserByType($type);

        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setEnabled((Boolean) $active);
        $user->setSuperAdmin((Boolean) $superadmin);
        $this->userManager->updateUser($user);

        return $user;
    }

}
