<?php

namespace Ufo\EAV\Repositories;

use Doctrine\ORM\EntityRepository;
use Ufo\EAV\Entity\Option;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Value;
use Ufo\EAV\Exceptions\EavNotFoundException;

class ValueRepository extends EntityRepository
{
    /**
     * @throws EavNotFoundException
     */
    public function get(Spec $spec, Param $param, ?string $locale = null): Value
    {
        $value = $this->createQueryBuilder('v')
              ->where('v.param = :param')
              ->andWhere(':specId MEMBER OF v.specs')
              ->andWhere('(v.locale = :locale OR v.locale IS NULL)')
              ->setParameter(':param', $param->getTag())   // Передаємо ID
              ->setParameter(':specId', $spec->getId())   // Передаємо ID
              ->setParameter(':locale', $locale)
              ->getQuery()
              ->getOneOrNullResult();
        if (!$value) {
            throw new EavNotFoundException("Value for param '{$param->getTag()}' in locale '{$locale}' not found");
        }
        return $value;
    }


}