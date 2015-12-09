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
use Tonic\Behat\ParallelScenarioExtension\ProcessExtractor;

/**
 * Class ParallelScenarioExtension.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ParallelScenarioExtension implements ExtensionInterface
{
    const PROCESS_EXTRACTOR = 'parallel_scenario.process_extractor';

    const CONFIG_OPTIONS = 'options';
    const CONFIG_SKIP = 'skip';
    const CONFIG_PROFILES = 'profiles';

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
        return 'parallel_scenario';
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
        $builder
            ->children()
            ->arrayNode(self::CONFIG_OPTIONS)
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode(self::CONFIG_SKIP)
            ->prototype('scalar')
            ->end()
            ->defaultValue([])
            ->end();

        $builder
            ->children()
            ->arrayNode(self::CONFIG_PROFILES)
            ->prototype('scalar')
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadCommandLineExtractor($container, $config);
        $this->loadController($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function loadCommandLineExtractor(ContainerBuilder $container, array $config)
    {
        $skipOptions = $config[self::CONFIG_OPTIONS][self::CONFIG_SKIP];

        $definition = new Definition(ProcessExtractor::class);
        $definition->addMethodCall('addSkipOptions', [
            $skipOptions,
        ]);

        $container->setDefinition(self::PROCESS_EXTRACTOR, $definition);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function loadController(ContainerBuilder $container, array $config)
    {
        $profiles = $config[self::CONFIG_PROFILES];

        $definition = new Definition(ParallelScenarioController::class, [
            new Reference(SuiteExtension::REGISTRY_ID),
            new Reference(SpecificationExtension::FINDER_ID),
            new Reference(self::PROCESS_EXTRACTOR),
            new Reference('event_dispatcher'),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, [
            'priority' => 1,
        ]);
        $definition->addMethodCall('setProfiles', [
            $profiles,
        ]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG.'.parallel-scenario', $definition);
    }
}
