<?php

namespace Ufo\EAV\Repositories;

use Doctrine\ORM\EntityRepository;
use Ufo\EAV\Entity\Option;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Exceptions\EavNotFoundException;

class OptionRepository extends EntityRepository
{
    /**
     * @throws EavNotFoundException
     */
    public function get(Param $param, string $value, ?string $locale = null): Option
    {
        if (!$option = $this->findOneBy(['param' => $param, 'value' => $value, 'locale' => $locale])) {
            try {
                $option = $this->fromIdentityMap($param,  $value);
            } catch (\Throwable) {
                throw new EavNotFoundException("Option '{$value}' for param '{$param->getTag()}' in locale '{$locale}' not found");
            }
        }
        return $option;
    }

    /**
     * @throws EavNotFoundException
     */
    protected function fromIdentityMap(Param $param, string $value): Option
    {
        $persistedEntity = $this->getEntityManager()->getUnitOfWork()->getScheduledEntityInsertions();

        foreach ($persistedEntity as $entity) {
            if ($entity instanceof Option
                && $entity->getParam() === $param
                && $entity->getValue() === $value
            ) {
                return $entity;
            }
        }
        throw new EavNotFoundException();
    }
}