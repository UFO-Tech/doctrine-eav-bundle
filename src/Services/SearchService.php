<?php

namespace Ufo\EAV\Services;

use Ufo\EAV\Entity\EavEntity;
use Ufo\EAV\Exceptions\EavNotFoundException;
use Ufo\EAV\Filters\FilterRow\FilterData;
use Ufo\EAV\Traits\EavRepositoryAccess;

class SearchService
{
    use EavRepositoryAccess;

    /**
     * @param string $queryString
     * @param FilterData|null $filterData
     * @return EavEntity[]
     */
    public function search(string $queryString, ?FilterData $filterData = null): array
    {
        return $this->viewSpecDetailJson->search($queryString, $filterData);
    }
}

