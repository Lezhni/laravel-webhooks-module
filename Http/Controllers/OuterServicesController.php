<?php

namespace Modules\OuterServices\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\OuterServices\Services\ApplicationsConverter;
use Modules\OuterServices\Services\SourcesFactory;
use Throwable;

/**
 * Class OuterServicesController
 * @package Modules\OuterServices\Http\Controllers
 */
class OuterServicesController extends Controller
{
    /**
     * @var \Modules\OuterServices\Services\ApplicationsConverter
     */
    protected $converter;

    /**
     * @var \Modules\OuterServices\Services\SourcesFactory
     */
    protected $factory;

    /**
     * OuterServicesController constructor.
     * @param \Modules\OuterServices\Services\ApplicationsConverter $converter
     * @param \Modules\OuterServices\Services\SourcesFactory $factory
     */
    public function __construct(ApplicationsConverter $converter, SourcesFactory $factory) {
        $this->converter = $converter;
        $this->factory = $factory;
    }

    /**
     * @param string $serviceAlias
     * @param \Illuminate\Http\Request $request
     */
    public function __invoke(string $serviceAlias, Request $request)
    {
        $data = $request->json();
        if (!is_array($data) || count($data) == 0) {
            return;
        }

        try {
            $source = $this->factory->make($serviceAlias);
            $this->converter->setSource($source);
            $this->converter->run($data);
        } catch (Throwable $e) {
            report($e);
            return;
        }
    }
}
