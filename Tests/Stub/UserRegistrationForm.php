<?php

namespace PUGX\MultiUserBundle\Tests\Stub;

class UserRegistrationForm
{
    private $class;
    private $options;

    public function __construct($class, $options = null)
    {
        $this->class   = $class;
        $this->options = $options;
    }

    public function getName()
    {
        return "form_name";
    }
}
