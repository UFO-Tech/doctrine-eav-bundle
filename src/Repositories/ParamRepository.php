<?php

namespace Ufo\EAV\Repositories;

use Doctrine\ORM\EntityRepository;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Exceptions\EavNotFoundException;

class ParamRepository extends EntityRepository
{
    public function all(bool $filteredOnly = true): array
    {
        if ($filteredOnly) {
            $params = $this->findBy(['filtered' => true]);
        } else {
            $params = $this->findAll();
        }
        return $params;
    }

    /**
     * @throws EavNotFoundException
     */
    public function get(string $tag): Param
    {
        if (!$param = $this->findOneBy(['tag' => $tag])) {
            try {
                $param = $this->fromIdentityMap($tag);
            } catch (\Throwable) {
                throw new EavNotFoundException("Param with tag '{$tag}' is not found");
            }
        }
        return $param;
    }

    public function getCommonParamsByEavIds(array $eavIds): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p')
            ->distinct(true)
            ->join('p.specs', 's')
            ->where($qb->expr()->in('s.eav', $eavIds))
            ->groupBy('p.tag')
            ->having('COUNT(DISTINCT s.eav) = :count')
            ->setParameter('count', count($eavIds))

        ;
        return $qb->getQuery()->getResult();
    }

    protected function fromIdentityMap(string $tag): Param
    {
        $im = $this->getEntityManager()->getUnitOfWork()->getIdentityMap();
        return $im[Param::class][$tag];
    }
}