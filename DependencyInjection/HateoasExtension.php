<?php

namespace Kmfk\Bundle\HateoasBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Hateoas\Bundle\HateoasBundle\DependencyInjection\Configuration;

class HateoasExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        if (!isset($config['host'])) {
            throw new \InvalidArgumentException('The "host" option must be set');
        }

        // Lets be sure we have the correct trailing slashses
        $config['host'] = rtrim($config['domain'], '/') . '/';

        if (!empty($config['prefix'])) {
            $config['prefix'] = rtrim(ltrim($config['prefix'], '/'), '/') . '/';
        }

        $container->setParameter('kmfk.hateoas', $config);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
