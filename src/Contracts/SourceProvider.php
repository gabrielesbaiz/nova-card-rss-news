<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Contracts;

use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Illuminate\Support\Collection;

interface SourceProvider
{
    /**
     * @return Collection<int, Source>
     */
    public function sources(): Collection;
}
