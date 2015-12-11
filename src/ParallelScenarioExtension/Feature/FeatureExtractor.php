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
    private $suiteRepository;

    /**
     * @var SpecificationFinder
     */
    private $specificationFinder;

    /**
     * FeatureExtractor constructor.
     *
     * @param SuiteRepository     $suiteRepository
     * @param SpecificationFinder $specificationFinder
     */
    public function __construct(SuiteRepository $suiteRepository, SpecificationFinder $specificationFinder)
    {
        $this->suiteRepository = $suiteRepository;
        $this->specificationFinder = $specificationFinder;
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
     * @param $locator
     *
     * @return FeatureNode[]
     */
    public function extract($locator)
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
}
