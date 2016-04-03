<?php

namespace Massil\MultiUserBundle\Model;

use Massil\MultiUserBundle\Model\UserFactoryInterface;

/**
 * @author leonardo proietti (leonardo.proietti@gmail.com)
 */
class UserFactory implements UserFactoryInterface
{
    /**
     *
     * @param type $class
     * @return \Massil\MultiUserBundle\Model\class 
     */
    public static function build($class)
    {        
        $user = new $class;        
        return $user;
    }
}