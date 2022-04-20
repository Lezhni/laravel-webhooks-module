<?php

namespace Modules\OuterServices\Services\Sources;

use App\Models\Application;

/**
 * Interface Source
 * @package Modules\OuterServices\Services\Sources
 */
interface Source
{
    /**
     * @param array $data
     * @return \App\Models\Application|null
     */
    public function convertApplication(array $data): ?Application;
}