<?php

namespace Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Exception;

/**
 * Class NotProcessException.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class NotProcessException extends AbstractProcessException
{
    private $object;

    /**
     * NotProcessException constructor.
     *
     * @param $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }
}
