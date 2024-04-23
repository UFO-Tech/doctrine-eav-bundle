<?php

namespace Ufo\EAV\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class UfoEavExtension extends Extension
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

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('doctrine.orm.default_entity_manager')) {
            return;
        }

        $def = $container->getDefinition('doctrine.orm.default_entity_manager');

        // Отримання існуючих мапінгів
        $mappings = $def->getArgument(1) ?? [];

        // Додавання нових мапінгів для EAV сутностей
        $mappings['EAV'] = [
            'is_bundle' => false,
            'type' => 'annotation',
            'prefix' => 'Ufo\EAV\Entity',
            'dir' => '%kernel.project_dir%/vendor/ufo-tech/doctrine-eav-bundle/src/Entity',
            'alias' => 'EAV'
        ];

        $def->replaceArgument(1, $mappings);
    }

    public function getAlias(): string
    {
        return 'ufo_eav';
    }

}
