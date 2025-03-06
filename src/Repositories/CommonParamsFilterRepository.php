<?php

namespace Ufo\EAV\Repositories;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ufo\EAV\Entity\Views\CommonParamsFilter;
use Ufo\EAV\Services\LocaleService;

/**
 * @method CommonParamsFilter|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommonParamsFilter|null findOneBy(array $criteria, array $orderBy = null)
 */
class CommonParamsFilterRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        protected LocaleService $localeService
    )
    {
        parent::__construct($registry, CommonParamsFilter::class);
    }

    public function findAll(): array
    {
        return $this->findBy($this->localeService->getLocaleCriteria());
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return parent::findBy($this->localeService->addLocaleToCriteria($criteria), $orderBy, $limit, $offset);
    }
}
