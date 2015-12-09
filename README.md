# Install

Install via composer
```bash
composer require tonicforhealth/behat-parallel-scenario
```
# Config

#### Load extenstion into config:
```yml
default:
    extensions:
        Tonic\Behat\ParallelScenarioExtension: ~
```
#### or
```yml
default:
    extensions:
        Tonic\Behat\ParallelScenarioExtension:
            profiles:
                - profile_name_for_worker_1
                - profile_name_for_worker_2
                - profile_name_for_worker_3
            options:
                skip:
                    - any-behat-option-for-skiping-in-worker
```
#### Mark scenarios with tags
* run scenario in parallel

```
    @parallel-scenario
```

* wait all parallel scenarios are done

```
    @parallel-wait
```
* run examples in parallel

```
    @parallel-examples
```
# Run
```bash
    php bin/behat --parallel-process 2
```
When parameter is absent or equal to 1 then test will be run in usual mode

# Todo
* tests
* add support for --stop-on-failure option
