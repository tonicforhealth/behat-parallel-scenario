<?php

namespace Tonic\Behat\ParallelScenarioExtension\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Specification\SpecificationFinder;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Suite\SuiteRepository;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Tonic\Behat\ParallelScenarioExtension\ParallelScenarioFileLineExtractor;
use Tonic\Behat\ParallelScenarioExtension\ProcessManager;

/**
 * Class ParallelScenarioController.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ParallelScenarioController implements Controller
{
    const OPTION_PARALLEL_PROCESS = 'parallel-process';

    /**
     * @var SuiteRepository
     */
    private $suiteRepository;

    /**
     * @var SpecificationFinder
     */
    private $specificationFinder;

    /**
     * @var ParallelScenarioFileLineExtractor
     */
    private $scenarioFileLineExtractor;
    /**
     * @var ParallelScenarioCommandLineExtractor
     */
    private $baseCommandLineExtractor;
    /**
     * @var ProcessManager
     */
    private $processManager;

    /**
     * ParallelScenarioController constructor.
     *
     * @param SuiteRepository                   $suiteRepository
     * @param SpecificationFinder               $specificationFinder
     * @param ParallelScenarioFileLineExtractor $scenarioFileLineExtractor
     */
    public function __construct(SuiteRepository $suiteRepository, SpecificationFinder $specificationFinder, ParallelScenarioFileLineExtractor $scenarioFileLineExtractor)
    {
        $this->suiteRepository = $suiteRepository;
        $this->specificationFinder = $specificationFinder;

        $this->scenarioFileLineExtractor = $scenarioFileLineExtractor;
        $this->processManager = new ProcessManager();
    }

    /**
     * {@inheritdoc}
     */
    public function configure(SymfonyCommand $command)
    {
        $command->addOption(self::OPTION_PARALLEL_PROCESS, null, InputOption::VALUE_OPTIONAL, 'Max parallel processes amount', 1);
        $this->baseCommandLineExtractor = new ParallelScenarioCommandLineExtractor($command->getDefinition());
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $result = null;
        $maxProcessesAmount = $input->getOption(self::OPTION_PARALLEL_PROCESS);

        if ($maxProcessesAmount > 1) {
            $this->baseCommandLineExtractor->init($input);
            $this->processManager->setMaxParallelProcess($maxProcessesAmount);
            $this->processManager->setStopCallback(function (Process $process) use ($output) {
                $output->writeln($process->getOutput());
                $output->writeln($process->getErrorOutput());
            });

            $locator = $input->getArgument('paths');

            $specifications = $this->findSuitesSpecifications($locator);

            $result = 0;

            foreach (GroupedSpecificationIterator::group($specifications) as $iterator) {
                foreach ($iterator as $featureNode) {
                    $scenarioGroups = $this->scenarioFileLineExtractor->extract($featureNode);

                    foreach ($scenarioGroups as $scenarios) {
                        /** @var Process[] $processes */
                        $processes = array_map(function ($scenarioLineFile) {
                            return new Process($this->baseCommandLineExtractor->getCommand($scenarioLineFile));
                        }, $scenarios);

                        $this->processManager->runParallel($processes);

                        foreach ($processes as $process) {
                            $result = max($result, $process->getExitCode());
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Finds specification iterators for all provided suites using locator.
     *
     * @param null|string $locator
     *
     * @return SpecificationIterator[]
     */
    private function findSuitesSpecifications($locator)
    {
        return $this->specificationFinder->findSuitesSpecifications(
            $this->suiteRepository->getSuites(),
            $locator
        );
    }
}
