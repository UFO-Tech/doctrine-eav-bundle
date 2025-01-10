<?php

namespace Ufo\EAV\Repositories;

use Doctrine\ORM\EntityRepository;
use Ufo\EAV\Entity\Views\CommonParamsFilter;

/**
 * @method CommonParamsFilter|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommonParamsFilter|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommonParamsFilter[] findAll()
 * @method CommonParamsFilter[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommonParamsFilterRepository extends EntityRepository
{

}
