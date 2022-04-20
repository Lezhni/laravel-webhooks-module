<?php

namespace Modules\OuterServices\Services;

use Modules\OuterServices\Services\Sources\Source;

/**
 * Class ApplicationsConverter
 * @package Modules\OuterServices\Services
 */
class ApplicationsConverter
{
    /**
     * @var \Modules\OuterServices\Services\Sources\Source
     */
    protected $source;

    /**
     * @param \Modules\OuterServices\Services\Sources\Source $source
     */
    public function setSource(Source $source)
    {
        $this->source = $source;
    }

    /**
     * @param array $data
     * @throws \Throwable
     */
    public function run(array $data)
    {
        $application = $this->source->convertApplication($data);
        $application->saveOrFail();
    }
}