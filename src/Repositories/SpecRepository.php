<?php

namespace Ufo\EAV\Repositories;

use Doctrine\ORM\QueryBuilder;
use Ufo\EAV\Entity\Discriminators\Values\ValueOption;
use Ufo\EAV\Entity\Spec;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use function array_map;
use function is_null;

/**
 * @method Spec|null find($id, $lockMode = null, $lockVersion = null)
 * @method Spec|null findOneBy(array $criteria, array $orderBy = null)
 * @method Spec[] findAll()
 * @method Spec[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpecRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Spec::class);
    }

    public function getBySpecIds(?array $specIds = null): array
    {
        return $this->getQB($specIds)->getQuery()->getResult();
    }

    public function getList(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        $specs = $this->findBy($criteria, $orderBy, $limit, $offset);

        $ids = array_map(function (Spec $v) {
            return $v->getId();
        }, $specs);
        $this->getQB($ids)->getQuery()->getResult();
        return $specs;
    }

    protected function getQB(?array $specIds = null): QueryBuilder
    {
        $optionsQB = $this->getEntityManager()->createQueryBuilder()
                          ->from(ValueOption::class, 'vo')
                          ->select('vo, op')
                          ->join('vo.options', 'op')
                          ->join('vo.specs', 'os')
        ;


        $specsQB = $this->getEntityManager()->createQueryBuilder()
                        ->from(Spec::class, 's')
                        ->select('s, p, v, eav')
                        ->join('s.eav', 'eav')
                        ->join('s.params', 'p')
                        ->join('s.values', 'v')
        ;
        if (!is_null($specIds)) {
            $optionsQB->where('os.id in (:specIds)')->setParameter('specIds', $specIds);
            $specsQB->where('s.id in (:specIds)')->setParameter('specIds', $specIds);
        }
        $optionsQB->getQuery()->getResult();
        return $specsQB;
    }
}
