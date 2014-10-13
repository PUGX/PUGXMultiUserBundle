<?php

namespace PUGX\MultiUserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pugx_multi_user');

        $supportedDrivers = array('orm');

        $rootNode->
            children()
                ->scalarNode('db_driver')
                    ->defaultValue('orm')
                    ->validate()
                        ->ifNotInArray($supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode($supportedDrivers))
                    ->end()
                ->end();

        $rootNode->
            children()
                ->scalarNode('user_manager')
                    ->defaultValue('pugx_user.manager.orm_user_manager.default')
                    ->end()
                ->end();

        $rootNode->
                children()
                    ->arrayNode('users')->prototype('array')
                        ->children()
                            ->arrayNode('entity')
                                ->children()
                                    ->scalarNode('class')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('factory')->defaultValue('PUGX\MultiUserBundle\Model\UserFactory')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->children()
                            ->arrayNode('registration')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->arrayNode('form')
                                    ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('type')->defaultValue(null)->end()
                                            ->scalarNode('name')->defaultValue('fos_user_registration_form')->end()
                                            ->arrayNode('validation_groups')
                                                ->prototype('scalar')->end()
                                                ->defaultValue(array('Registration', 'Default'))
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->scalarNode('template')->defaultValue(null)->end()
                                 ->end()
                            ->end()
                        ->end()
                        ->children()
                            ->arrayNode('profile')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->arrayNode('form')
                                    ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('type')->defaultValue(null)->end()
                                            ->scalarNode('name')->defaultValue('fos_user_profile_form')->end()
                                            ->arrayNode('validation_groups')
                                                ->prototype('scalar')->end()
                                                ->defaultValue(array('Profile', 'Default'))
                                            ->end()
                                        ->end()
                                    ->end()
                                 ->end()
                            ->end()
                        ->end()

                    ->end()
                ->end()
                ->end();

        return $treeBuilder;
    }
}
