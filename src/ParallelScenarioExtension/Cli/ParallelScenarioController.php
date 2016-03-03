<?php

namespace Tonic\Behat\ParallelScenarioExtension\Cli;

use Behat\Testwork\Cli\Controller;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tonic\Behat\ParallelScenarioExtension\Feature\FeatureExtractor;
use Tonic\Behat\ParallelScenarioExtension\Feature\FeatureRunner;
use Tonic\Behat\ParallelScenarioExtension\Listener\OutputPrinter;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcessFactory;

/**
 * Class ParallelScenarioController.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ParallelScenarioController implements Controller
{
    const OPTION_PARALLEL_PROCESS = 'parallel-process';

    /**
     * @var FeatureRunner
     */
    private $featureRunner;
    /**
     * @var FeatureExtractor
     */
    private $featureExtractor;
    /**
     * @var ScenarioProcessFactory
     */
    private $processFactory;
    /**
     * @var OutputPrinter
     */
    private $outputPrinter;
    /**
     * @var InputDefinition
     */
    private $inputDefinition;

    /**
     * ParallelScenarioController constructor.
     *
     * @param FeatureRunner          $featureRunner
     * @param FeatureExtractor       $featureExtractor
     * @param ScenarioProcessFactory $processFactory
     * @param OutputPrinter          $outputPrinter
     */
    public function __construct(FeatureRunner $featureRunner, FeatureExtractor $featureExtractor, ScenarioProcessFactory $processFactory, OutputPrinter $outputPrinter)
    {
        $this->featureRunner = $featureRunner;
        $this->featureExtractor = $featureExtractor;
        $this->processFactory = $processFactory;
        $this->outputPrinter = $outputPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(SymfonyCommand $command)
    {
        $command->addOption(self::OPTION_PARALLEL_PROCESS, null, InputOption::VALUE_OPTIONAL, 'Max parallel processes amount', 1);
        $this->inputDefinition = $command->getDefinition();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $result = null;

        $maxProcessesAmount = max(1, $input->getOption(self::OPTION_PARALLEL_PROCESS));
        $locator = $input->getArgument('paths');

        if ($maxProcessesAmount > 1) {
            $this->outputPrinter->init($output);
            $this->processFactory->init($this->inputDefinition, $input);
            $this->featureRunner->setMaxParallelProcess($maxProcessesAmount);

            $result = 0;

            foreach ($this->featureExtractor->extract($locator) as $featureNode) {
                $result = max($result, $this->featureRunner->run($featureNode));
            }
        }

        return $result;
    }
}
