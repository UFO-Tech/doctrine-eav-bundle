<?php

namespace Ufo\EAV\DependencyInjection;

use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonContains;
use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonExtract;
use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonLength;
use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonObject;
use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonSearch;
use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonUnquote;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Ufo\EAV\AST\Functions\Mysql\CountSlashes;
use Ufo\EAV\AST\Functions\Mysql\PowerFunction;

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
                        JsonExtract::FUNCTION_NAME => JsonExtract::class,
                        JsonSearch::FUNCTION_NAME => JsonSearch::class,
                        JsonUnquote::FUNCTION_NAME => JsonUnquote::class,
                        JsonLength::FUNCTION_NAME => JsonLength::class,
                        JsonContains::FUNCTION_NAME => JsonContains::class,
                        JsonObject::FUNCTION_NAME => JsonObject::class,
                        CountSlashes::FUNCTION_NAME => CountSlashes::class,
                    ],
                    'numeric_functions' => [
                        PowerFunction::FUNCTION_NAME => PowerFunction::class,
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
