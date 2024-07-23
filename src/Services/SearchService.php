<?php

namespace Ufo\EAV\Services;

use Ufo\EAV\Entity\EavEntity;
use Ufo\EAV\Exceptions\EavNotFoundException;
use Ufo\EAV\Traits\EavRepositoryAccess;

class SearchService
{
    use EavRepositoryAccess;

    /**
     * @param string $queryString
     * @return EavEntity[]
     * @throws EavNotFoundException
     */
    public function search(string $queryString): array
    {
        $specsDetail = $this->viewSpecDetail->search($queryString);
        if (empty($specsDetail)) {
            throw new EavNotFoundException("Search by '$queryString' not have result");
        }
        return $specsDetail;
    }



}

