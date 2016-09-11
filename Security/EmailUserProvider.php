<?php

namespace PUGX\MultiUserBundle\Security;

use FOS\UserBundle\Security\EmailUserProvider as FOSEmailUserProvider;

class EmailUserProvider extends FOSEmailUserProvider {
    public function supportsClass($class) {
        $userClass = $this->userManager->getClass();
        return $userClass === $class || is_subclass_of($class, $userClass) ||
               (get_parent_class($class) === get_parent_class($userClass) && get_parent_class($class) !== false);
    }
}
