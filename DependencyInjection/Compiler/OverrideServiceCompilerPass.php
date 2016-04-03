<?php

namespace Massil\MultiUserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->changeService(
                $container, 
                'fos_user.registration.form.factory', 
                'massil_multi_user.registration_form_factory');
        
        $this->changeService(
                $container, 
                'fos_user.profile.form.factory', 
                'massil_multi_user.profile_form_factory');
    }
    
    private function changeService($container, $serviceName, $newServiceName)
    {
        $service = $container->getDefinition($serviceName);
        $newService = $container->getDefinition($newServiceName);
        
        if ($service && $newService) {
            $container->removeDefinition($serviceName);
            $container->setDefinition($serviceName, $newService);
        }
    }
}