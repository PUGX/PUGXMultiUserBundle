<?php

namespace PUGX\MultiUserBundle\Tests\Stub;

class UserRegistrationForm
{
    protected $class;
    protected $options;

    public function __construct($class, $options = null)
    {
        $this->class = $class;
        $this->options = $options;
    }

    public function getName()
    {
        return 'form_name';
    }
}
