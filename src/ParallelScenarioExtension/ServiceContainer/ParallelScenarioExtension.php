<?php

namespace Tonic\Behat\ParallelScenarioExtension\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\Specification\ServiceContainer\SpecificationExtension;
use Behat\Testwork\Suite\ServiceContainer\SuiteExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tonic\Behat\ParallelScenarioExtension\Cli\ParallelScenarioController;

/**
 * Class ParallelScenarioExtension.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ParallelScenarioExtension implements ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'parallel';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadController($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function loadController(ContainerBuilder $container)
    {
        $definition = new Definition(ParallelScenarioController::class, [
            new Reference(SuiteExtension::REGISTRY_ID),
            new Reference(SpecificationExtension::FINDER_ID),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, [
            'priority' => 0,
        ]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG.'.parallel-scenario', $definition);
    }
}
