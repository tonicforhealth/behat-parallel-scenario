<?php

namespace Tonic\Behat\ParallelScenarioExtension\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\EventDispatcher\TestworkEventDispatcher;
use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Specification\SpecificationFinder;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Suite\SuiteRepository;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Tonic\Behat\ParallelScenarioExtension\ProcessExtractor;
use Tonic\Behat\ParallelScenarioExtension\ProcessManager\ProcessBeforeStartEvent;
use Tonic\Behat\ParallelScenarioExtension\ProcessManager\ProcessEvent;
use Tonic\Behat\ParallelScenarioExtension\ProcessManager\ProcessManager;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfoExtractor;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess;

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
     * @var ScenarioInfoExtractor
     */
    private $scenarioFileLineExtractor;
    /**
     * @var ProcessExtractor
     */
    private $processExtractor;
    /**
     * @var ProcessManager
     */
    private $processManager;
    /**
     * @var InputDefinition
     */
    private $inputDefinition;
    /**
     * @var TestworkEventDispatcher
     */
    private $eventDispatcher;
    /**
     * @var array
     */
    private $profiles = [];

    /**
     * ParallelScenarioController constructor.
     *
     * @param SuiteRepository         $suiteRepository
     * @param SpecificationFinder     $specificationFinder
     * @param ProcessExtractor        $processExtractor
     * @param TestworkEventDispatcher $eventDispatcher
     */
    public function __construct(SuiteRepository $suiteRepository, SpecificationFinder $specificationFinder, ProcessExtractor $processExtractor, TestworkEventDispatcher $eventDispatcher)
    {
        $this->suiteRepository = $suiteRepository;
        $this->specificationFinder = $specificationFinder;
        $this->processExtractor = $processExtractor;
        $this->eventDispatcher = $eventDispatcher;

        $this->scenarioFileLineExtractor = new ScenarioInfoExtractor();
        $this->processManager = new ProcessManager();
    }

    /**
     * @param array $profiles
     */
    public function setProfiles(array $profiles)
    {
        $this->profiles = $profiles;
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
        if (!$this->profiles && $profile = $input->getOption('profile')) {
            $this->setProfiles([
                $profile,
            ]);
        }

        $result = null;
        $maxProcessesAmount = $input->getOption(self::OPTION_PARALLEL_PROCESS);

        if ($maxProcessesAmount > 1) {
            $this->processExtractor->init($this->inputDefinition, $input);
            $this->processManager->setMaxParallelProcess($maxProcessesAmount);
            $this->processManager->getEventDispatcher()->addListener(ProcessManager::EVENT_PROCESS_STOP, function (ProcessEvent $event) use ($output) {
                $process = $event->getProcess();
                if ($process->getExitCode()) {
                    $output->writeln(sprintf('<comment>%s</comment>', $process->getOutput()));
                    $output->writeln(sprintf('<error>%s</error>', $process->getErrorOutput()));
                } else {
                    $output->writeln(sprintf('<info>%s</info>', $process->getOutput()));
                }
            });
            $this->processManager->getEventDispatcher()->addListener(ProcessManager::EVENT_PROCESS_BEFORE_START, function (ProcessBeforeStartEvent $event) use ($output, $maxProcessesAmount) {
                /** @var ScenarioProcess $process */
                $process = $event->getProcess();
                $process->setProfile($this->getProfileByWorkerIndex($event->getProcessIndex()));
                $output->writeln(sprintf('START ::: %s', $process->getCommandLine()));
            });

            $locator = $input->getArgument('paths');

            $specifications = $this->findSuitesSpecifications($locator);

            $result = 0;

            foreach (GroupedSpecificationIterator::group($specifications) as $iterator) {
                foreach ($iterator as $featureNode) {
                    $this->eventDispatcher->dispatch('parallel_scenario.feature_tested.before');
                    $scenarioGroups = $this->scenarioFileLineExtractor->extract($featureNode);

                    foreach ($scenarioGroups as $scenarios) {
                        /** @var Process[] $processes */
                        $processes = array_map(function ($scenarioLineFile) {
                            return $this->processExtractor->extract($scenarioLineFile);
                        }, $scenarios);

                        $this->processManager->runParallel($processes);

                        foreach ($processes as $process) {
                            $result = max($result, $process->getExitCode());
                        }
                    }
                    $this->eventDispatcher->dispatch('parallel_scenario.feature_tested.after');
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

    /**
     * @param int $workerIndex
     *
     * @return string|null
     */
    private function getProfileByWorkerIndex($workerIndex)
    {
        $profile = null;
        if ($environmentsCount = count($this->profiles)) {
            $environmentIndex = $workerIndex % $environmentsCount;
            $profile = $this->profiles[$environmentIndex];
        }

        return $profile;
    }
}
