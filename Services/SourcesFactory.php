<?php

namespace Modules\OuterServices\Services;

use http\Exception\UnexpectedValueException;
use Modules\OuterServices\Services\Sources\Creatium;
use Modules\OuterServices\Services\Sources\EmbedGames;
use Modules\OuterServices\Services\Sources\Marquiz;
use Modules\OuterServices\Services\Sources\Source;

/**
 * Class SourcesFactory
 * @package Modules\OuterServices\Services
 */
final class SourcesFactory
{
    /**
     * @param string $sourceAlias
     * @return \Modules\OuterServices\Services\Sources\Source
     */
    public function make(string $sourceAlias): Source
    {
        switch ($sourceAlias) {
            case 'marquiz':
                return new Marquiz;
            case 'embedgames':
                return new EmbedGames;
            case 'creatium':
                return new Creatium;
            default:
                throw new UnexpectedValueException("Source with alias {$sourceAlias} not found");
        }
    }
}