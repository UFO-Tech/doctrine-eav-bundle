<?php

namespace Ufo\EAV\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class UfoEAVExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var ContainerBuilder
     */
    protected ContainerBuilder $container;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->container = $container;

        $loader = new Loader\YamlFileLoader($this->container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $dir = __DIR__ . '/../Entity';
        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'schema_filter' => '~^(?!.*_view$)~'
            ],
            'orm' => [
                'mappings' => [
                    'EAV' => [
                        'is_bundle' => false,
                        'prefix' => 'Ufo\EAV\Entity',
                        'dir' => $dir,
                        'alias' => 'EAV'
                    ]
                ],
                'dql' => [
                    'string_functions' => [
                        'JSON_EXTRACT' => 'Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonExtract',
                        'JSON_SEARCH' => 'Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonSearch',
                    ]
                ]
            ],
        ]);
    }

    public function getAlias(): string
    {
        return 'ufo_eav';
    }

}
