<?php

namespace Ufo\EAV\Fillers;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Entity\Views\SpecDetail;
use Ufo\EAV\Fillers\Interfaces\IFiller;
use Ufo\EAV\Repositories\SpecRepository;
use Ufo\EAV\Repositories\ViewSpecDetailRepository;

abstract class AbstractFiller implements IFiller
{

    /**
     * @var SpecDetail[]
     */
    protected array $specDetails = [];
    protected array $specIds = [];

    protected ViewSpecDetailRepository|EntityRepository $viewSpecDetail;
    protected SpecRepository|EntityRepository $specRepository;

    public function __construct(protected EntityManagerInterface $em)
    {
        $this->viewSpecDetail = $this->em->getRepository(SpecDetail::class);
        $this->specRepository = $this->em->getRepository(Spec::class);
    }

    /**
     * @return SpecDetail[]
     */
    public function getSpecDetails(): array
    {
        return $this->specDetails;
    }

    /**
     * @return array
     */
    public function getSpecIds(): array
    {
        return $this->specIds;
    }

}