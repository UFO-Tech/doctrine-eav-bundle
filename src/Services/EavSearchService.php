<?php

namespace Ufo\EAV\Services;

use Ufo\EAV\Entity\Discriminators\Values\ValueNumber;
use Ufo\EAV\Entity\Discriminators\Values\ValueOption;
use Ufo\EAV\Entity\Discriminators\Values\ValueString;
use Ufo\EAV\Entity\EavEntity;
use Ufo\EAV\Entity\Option;
use Ufo\EAV\Entity\Value;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Exceptions\EavNotFoundException;
use Ufo\EAV\Traits\EavRepositoryAccess;

class EavSearchService
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

