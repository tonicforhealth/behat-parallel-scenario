<?php

namespace Tonic\Behat\Tests\ParallelScenarioExtension\ScenarioProcess;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfo;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcessFactory;

/**
 * Class ScenarioProcessFactoryTest.
 *
 * @coversDefaultClass \Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcessFactory
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ScenarioProcessFactoryTest extends TestCase
{
    public function providerMake(): array
    {
        $inputDefinition = new InputDefinition();
        $inputDefinition->addOption(new InputOption('option_optional_default', null, InputOption::VALUE_OPTIONAL, '', 'default'));
        $inputDefinition->addOption(new InputOption('option_array_default_array', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY));
        $inputDefinition->addOption(new InputOption('option_none', null, InputOption::VALUE_NONE));
        $inputDefinition->addOption(new InputOption('out', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY));

        return [
            'option_optional_default_default' => [
                $inputDefinition,
                new ArrayInput([
                    '--option_optional_default' => 'default',
                ], $inputDefinition),
            ],

            'option_optional_default_value' => [
                $inputDefinition,
                new ArrayInput([
                    '--option_optional_default' => 'value',
                ], $inputDefinition),
                '--option_optional_default \'value\'',
            ],

            'option_array_default_array_default' => [
                $inputDefinition,
                new ArrayInput([
                    '--option_array_default_array' => [],
                ], $inputDefinition),
            ],

            'option_array_default_array_value' => [
                $inputDefinition,
                new ArrayInput([
                    '--option_array_default_array' => ['value1', 'value2'],
                ], $inputDefinition),
                '--option_array_default_array \'value1\' --option_array_default_array \'value2\'',
            ],

            'option_none_default' => [
                $inputDefinition,
                new ArrayInput([], $inputDefinition),
            ],

            'option_none_default_value' => [
                $inputDefinition,
                new ArrayInput([
                    '--option_none' => 'any',
                ], $inputDefinition),
                '--option_none',
            ],

            'out_folder_skip' => [
                $inputDefinition,
                new ArrayInput([
                    '--out' => ['folder', 'std'],
                ], $inputDefinition),
                '',
                ['out'],
            ],

            'out_folder' => [
                $inputDefinition,
                new ArrayInput([
                    '--out' => ['folder', 'std'],
                ], $inputDefinition),
                '--out \'folder/59adaf3f0820898ecf0da97ceab30eab\' --out \'std\'',
            ],
        ];
    }

    /**
     * @covers       ::init
     * @covers      ::make
     *
     * @dataProvider providerMake
     */
    public function testMake(InputDefinition $inputDefinition, InputInterface $input, string $expectedCommandLine = '', array $skipOptions = []): void
    {
        $scenarioProcessFactory = new ScenarioProcessFactory('bin/behat');
        $scenarioProcessFactory->addSkipOptions($skipOptions);
        $scenarioProcessFactory->init($inputDefinition, $input);

        $scenarioInfo = new ScenarioInfo('file', 1);
        $process = $scenarioProcessFactory->make($scenarioInfo);

        self::assertEquals(trim(sprintf('%s bin/behat \'file:1\' %s', PHP_BINARY, $expectedCommandLine)), $process->getCommandLine());
    }
}
