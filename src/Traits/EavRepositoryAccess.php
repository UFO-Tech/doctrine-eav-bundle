<?php

namespace Ufo\EAV\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Ufo\EAV\Entity\Option;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Entity\Value;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Entity\Views\SpecDetailsJson;
use Ufo\EAV\Repositories\OptionRepository;
use Ufo\EAV\Repositories\ParamRepository;
use Ufo\EAV\Repositories\SpecDetailsJsonRepository;
use Ufo\EAV\Repositories\ValueRepository;
use Ufo\EAV\Repositories\ViewSpecDetailRepository;

trait EavRepositoryAccess
{
    protected ParamRepository|EntityRepository $paramRepo;
    protected ValueRepository|EntityRepository $valueRepo;
    protected OptionRepository|EntityRepository $optionRepo;

    protected ViewSpecDetailRepository|EntityRepository $viewSpecDetail;
    protected SpecDetailsJsonRepository|EntityRepository $viewSpecDetailJson;

    public function __construct(protected EntityManagerInterface $em)
    {
        $this->paramRepo = $this->em->getRepository(Param::class);
        $this->valueRepo = $this->em->getRepository(Value::class);
        $this->optionRepo = $this->em->getRepository(Option::class);
        $this->viewSpecDetail = $this->em->getRepository(SpecDetail::class);
        $this->viewSpecDetailJson = $this->em->getRepository(SpecDetailsJson::class);
    }

}