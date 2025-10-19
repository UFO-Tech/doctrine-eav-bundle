<?php

namespace Ufo\EAV\Repositories;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Ufo\EAV\Entity\Discriminators\Values\ValueOption;
use Ufo\EAV\Entity\Option;
use Ufo\EAV\Entity\Spec;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ufo\EAV\Services\LocaleService;

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

    public function __construct(
        ManagerRegistry $registry,
        protected ParameterBagInterface $params,
        protected LocaleService $localeService
    )
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
        $locale = $this->localeService->getLocale();
        $isDefaultLocale = $this->localeService->isDefaultLocale();

        // Запит для опцій
        $optionsQB = $this->getEntityManager()->createQueryBuilder()
                          ->from(ValueOption::class, 'vo')
                          ->select('vo')
                          ->join('vo.options', 'op')

                          ->leftJoin('vo.options', 'opTrans', 'WITH', 'opTrans.param = op.param AND opTrans.value = op.value AND opTrans.locale = :locale')
                          ->leftJoin(Option::class, 'opAlt', 'WITH', 'opAlt.param = op.param AND opAlt.value = op.value AND opAlt.locale = :locale')

                          ->join('vo.specs', 'os')
                          ->setParameter('locale', $locale);


        // Основний запит для спеків
        $specsQB = $this->getEntityManager()->createQueryBuilder()
                        ->from(Spec::class, 's')
                        ->select([
                            's',
                            'p',
                            'eav',
                        ])
                        ->leftJoin('s.eav', 'eav')
                        ->leftJoin('s.params', 'p')

                        ->leftJoin('s.values', 'vDefault', 'WITH', 'vDefault.param = p AND vDefault.locale IS NULL')
                        ->leftJoin('s.values', 'vTrans', 'WITH', 'vTrans.param = p AND vTrans.locale = :locale')

                        ->setParameter('locale', $locale)
        ;

        // Фільтрація за локаллю
        if ($isDefaultLocale) {
            $specsQB->where('vDefault.id IS NOT NULL');
        } else {
            $specsQB->where('vTrans.id IS NOT NULL OR vDefault.id IS NOT NULL');
        }

        // Фільтрація за ID
        if (!is_null($specIds)) {
            $optionsQB->andWhere('os.id IN (:specIds)')->setParameter('specIds', $specIds);
            $specsQB->andWhere('s.id IN (:specIds)')->setParameter('specIds', $specIds);
        }

        $optionsQB->getQuery()->getResult();
        return $specsQB;
    }
}
