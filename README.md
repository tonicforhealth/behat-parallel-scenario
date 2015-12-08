# Install

Install via composer

    composer require tonicforhealth/behat-parallel-scenario

# Config

#### Load extenstion into config:

    default:
        extensions:
            Tonic\Behat\ParallelScenarioExtension: ~
#### or

    default:
        extensions:
            Tonic\Behat\ParallelScenarioExtension:
                options:
                    skip:
                        - any-behat-option-for-skiping-in-worker

#### Mark scenarios with tags
* run scenario in parallel


    @parallel-scenario

* wait all parallel scenarios are done


    @parallel-wait

* run examples in parallel


    @parallel-examples

# Run

    php bin/behat --parallel-process 2

When parameter is absent or equal to 1 then test will be run in usual mode

# Todo
* Different environments for each worker
* tests
