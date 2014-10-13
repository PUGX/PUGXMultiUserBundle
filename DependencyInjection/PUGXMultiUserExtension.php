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

        /** Extract parameters from config file */
        $users = $config['users'];
        /** Default users */
        $container->setParameter('pugx_user_discriminator_users', $users);

        /** Build Conf from parameters in config file */
        $conf = $this->buildConf($users);
        $container->setParameter('pugx_user.discriminator.conf', $conf);

        /** Build User Types from parameters in config file */
        $userTypes = $this->buildUserTypes($users);
        $container->setParameter('pugx_user.discriminator.user_types', $userTypes);

        /** Alias default manager */
        $container->setAlias('pugx_user.manager.orm_user_manager', $config['user_manager']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $loader->load(sprintf('%s.yml', $config['db_driver']));
    }


        /**
     *
     * @param array $entities
     * @param array $registrationForms
     * @param array $profileForms
     */
    protected function buildConf(array $users)
    {
        foreach ($users as $user) {

            $class = $user['entity']['class'];

            if (!class_exists($class)) {
                throw new \InvalidArgumentException(sprintf('UserDiscriminator, configuration error : "%s" not found', $class));
            }

            $conf[$class] = array(
                    'factory' => $user['entity']['factory'],
                    'registration' => array(
                        'form' => array(
                            'type' => $user['registration']['form']['type'],
                            'name' => $user['registration']['form']['name'],
                            'validation_groups' => $user['registration']['form']['validation_groups'],
                        ),
                        'template' => $user['registration']['template'],
                    ),
                    'profile' => array(
                        'form' => array(
                            'type' => $user['profile']['form']['type'],
                            'name' => $user['profile']['form']['name'],
                            'validation_groups' => $user['profile']['form']['validation_groups'],
                        ),
                        'template' => $user['profile']['template'],
                    ),
                );
        }

        return $conf;

    }

    /**
     * Extract the user types from the pugx multi user configuration parameters into an array
     * 'type' => 'class'
     * e.g. array(
     *         'user_one' => 'Acme\UserBundle\Entity\UserOne',
     *         'user_two' => 'Acme\UserBundle\Entity\UserTwo',
     *     )
     *
     * @param array $entities
     */
    protected function buildUserTypes(array $parameters)
    {

        $userTypes = array();
        while ($user = current($parameters)) {
            $class = $user['entity']['class'];
            if (!class_exists($class)) {
                throw new \InvalidArgumentException(sprintf('ControllerListener, configuration error : "%s" not found', $class));
            }
            $userType = strtolower(trim(key($parameters)));
            $userTypes[$userType]= $class;
            next($parameters);
        }
        return $userTypes;
    }

}
