# Install

Install via composer

    composer require tonicforhealth/behat-parallel-scenario

# Config

Load extenstion into config:

    default:
        extensions:
            Tonic\Behat\ParallelScenarioExtension: ~

# Run

    php bin/behat --parallel-process 2

When parameter is ebsent or equal to 1 then test will be run in usual mode

# Todo
* Different environments for each worker
* tests