<?php

namespace PUGX\MultiUserBundle\Security;

use FOS\UserBundle\Security\UserProvider as FOSUserProvider;

class UserProvider extends FOSUserProvider {
    public function supportsClass($class) {
        $userClass = $this->userManager->getClass();
        return $userClass === $class || is_subclass_of($class, $userClass) ||
               (get_parent_class($class) === get_parent_class($userClass) && get_parent_class($class) !== false);
    }
}
