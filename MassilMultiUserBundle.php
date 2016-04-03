<?php

namespace Massil\MultiUserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Massil\MultiUserBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MassilMultiUserBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideServiceCompilerPass());
    }
}
