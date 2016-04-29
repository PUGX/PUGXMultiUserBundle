<?php

namespace PUGX\MultiUserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PUGXMultiUserExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $users = $config['users'];
        $container->setParameter('pugx_user_discriminator_users', $users);

        if (!isset($config['registration_controller'])) {
          throw new \InvalidArgumentException('The "registration_controller" option must be set');
        }

        $container->setParameter('registration_controller.class',$config['registration_controller']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $loader->load(sprintf('%s.yml', $config['db_driver']));
    }
}
