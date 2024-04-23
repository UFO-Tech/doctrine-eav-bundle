<?php

namespace Ufo\EAV\Services;

use Ufo\EAV\Entity\EavEntity;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Value;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Entity\Views\SpecDetailsJson;
use Ufo\EAV\Interfaces\IHaveSpecAccess;
use Ufo\EAV\Traits\EavRepositoryAccess;
use Ufo\EAV\VO\Filter;

class EavFilterService
{
    use EavRepositoryAccess;

    /**
     * @return Filter
     */
    public function getAll(): Filter
    {
        $filter = new Filter();
        $filter->setParams($this->paramRepo->all());
        $this->fillFilter($filter, true);
        return $filter;
    }


    /**
     * @param IHaveSpecAccess[] $eavEntities
     * @return Filter
     */
    public function fetchFacetsByEavEntities(array $eavEntities): Filter
    {
        $filter = new Filter();
        $eavIds = array_map(function(IHaveSpecAccess $eavDetail) use ($filter) {
            $filter->addSpec($eavDetail->getSpec());
            return $eavDetail->getSpec()->getEav()->getId();
        }, $eavEntities);

        $filter->setParams($this->paramRepo->getCommonParamsByEavIds($eavIds));

        $this->fillFilter($filter);
        return $filter;
    }

    protected function fillFilter(Filter $filter, bool $all = false): void
    {
        $params = $filter->getParams();
        foreach ($params as $param) {
            $values = $param->getValues();
            $filter->setValues($param,
               $values->filter(function (Value $v) use ($filter, $all) {

                   $res = $v->getSpecs()->filter(function (Spec $spec) use ($filter) {
                       return in_array($spec->getId(), $filter->getSpecsIds());
                   });
                   return $all || $res->count() > 0;

               })->toArray()
            );
        }
    }

}