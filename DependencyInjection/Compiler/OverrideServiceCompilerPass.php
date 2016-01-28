<?php

namespace PUGX\MultiUserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->changeService(
                $container, 
                'fos_user.registration.form.factory', 
                'pugx_multi_user.registration_form_factory');
        
        $this->changeService(
                $container, 
                'fos_user.profile.form.factory', 
                'pugx_multi_user.profile_form_factory');

        $this->changeService(
                $container,
                'fos_user.registration.form.type',
                'pugx_multi_user.registration.form.type');

        $this->changeService(
                $container,
                'fos_user.profile.form.type',
                'pugx_multi_user.profile.form.type');
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