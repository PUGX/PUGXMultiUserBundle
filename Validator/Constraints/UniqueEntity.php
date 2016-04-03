<?php

namespace Massil\MultiUserBundle\Validator\Constraints;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity as BaseConstraint;

/**
 * Constraint for the Unique Entity validator
 *
 * @Annotation
 */
class UniqueEntity extends BaseConstraint
{
    public $service = 'massil.orm.validator.unique';
    public $targetClass = null;
}
