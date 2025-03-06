<?php

namespace Ufo\EAV\Repositories;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Ufo\EAV\Entity\Discriminators\Values\ValueBoolean;
use Ufo\EAV\Entity\Discriminators\Values\ValueNumber;
use Ufo\EAV\Entity\Discriminators\Values\ValueOption;
use Ufo\EAV\Entity\Discriminators\Values\ValueString;
use Ufo\EAV\Entity\Option;
use Ufo\EAV\Entity\Spec;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ufo\EAV\Services\LocaleService;
use Ufo\EAV\Utils\ValueEntityMap;

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
//            ->select('vo, COALESCE(opTrans.value, opAlt.value, op.value) AS option_value')
            ->join('vo.options', 'op')

            // Основний переклад (прямий зв'язок)
            ->leftJoin('vo.options', 'opTrans', 'WITH', 'opTrans.param = op.param AND opTrans.value = op.value AND opTrans.locale = :locale')

            // Переклад з іншої спеки через сутність Option
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
            ->join('s.eav', 'eav')
            ->join('s.params', 'p')

            ->leftJoin(ValueNumber::class, 'vNum', 'WITH', 'vNum.param = p AND vNum.locale IS NULL')
            ->leftJoin(ValueNumber::class, 'vNumTrans', 'WITH', 'vNumTrans.param = p AND vNumTrans.locale = :locale')

            ->leftJoin(ValueString::class, 'vStr', 'WITH', 'vStr.param = p AND vStr.locale IS NULL')
            ->leftJoin(ValueString::class, 'vStrTrans', 'WITH', 'vStrTrans.param = p AND vStrTrans.locale = :locale')

            ->leftJoin(ValueBoolean::class, 'vBool', 'WITH', 'vBool.param = p AND vBool.locale IS NULL')
            ->leftJoin(ValueBoolean::class, 'vBoolTrans', 'WITH', 'vBoolTrans.param = p AND vBoolTrans.locale = :locale')

            ->setParameter('locale', $locale);

        // Фільтрація за локаллю
        if ($isDefaultLocale) {
            $specsQB->where('vNum.id IS NOT NULL OR vStr.id IS NOT NULL OR vBool.id IS NOT NULL');
        } else {
            $specsQB->where('(vNumTrans.id IS NOT NULL OR vStrTrans.id IS NOT NULL OR vBoolTrans.id IS NOT NULL)')
                    ->orWhere('(vNum.id IS NOT NULL OR vStr.id IS NOT NULL OR vBool.id IS NOT NULL)');
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
