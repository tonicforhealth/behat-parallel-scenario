<?php

namespace Tonic\Behat\ParallelScenarioExtension\Feature;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Specification\SpecificationFinder;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Suite\SuiteRepository;

/**
 * Class FeatureExtractor.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class FeatureExtractor
{
    /**
     * @var SuiteRepository
     */
    protected $suiteRepository;

    /**
     * @var SpecificationFinder
     */
    protected $specificationFinder;

    /**
     * FeatureExtractor constructor.
     */
    public function __construct(SuiteRepository $suiteRepository, SpecificationFinder $specificationFinder)
    {
        $this->suiteRepository = $suiteRepository;
        $this->specificationFinder = $specificationFinder;
    }

    /**
     * @return FeatureNode[]
     */
    public function extract(string $locator): array
    {
        $features = [];

        $specifications = $this->findSuitesSpecifications($locator);

        foreach (GroupedSpecificationIterator::group($specifications) as $iterator) {
            foreach ($iterator as $featureNode) {
                $features[] = $featureNode;
            }
        }

        return $features;
    }

    /**
     * Finds specification iterators for all provided suites using locator.
     *
     * @return SpecificationIterator[]
     */
    private function findSuitesSpecifications(?string $locator): array
    {
        return $this->specificationFinder->findSuitesSpecifications(
            $this->suiteRepository->getSuites(),
            $locator
        );
    }
}
