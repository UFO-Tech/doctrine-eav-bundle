<?php

namespace Ufo\EAV\Entity\Views;


use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Interfaces\IHaveSpecAccess;
use Ufo\EAV\Repositories\ViewSpecDetailRepository;


#[ORM\Entity(repositoryClass:ViewSpecDetailRepository::class, readOnly:true)]
#[ORM\Table(name: SpecDetail::VIEW_NAME)]
readonly class SpecDetail implements IHaveSpecAccess
{
    const string VIEW_NAME = 'eav_spec_details_view';

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING)]
    public string $uniqueId;

    #[ORM\Column(type: Types::INTEGER)]
    public int $specId;

    #[ORM\Column(type: Types::STRING)]
    public string $specName;

    #[ORM\Column(type: Types::STRING)]
    public string $paramName;

    #[ORM\Column(type: Types::STRING)]
    public string $paramTag;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $paramFiltered;

    #[ORM\Column(type: Types::STRING)]
    public string $valueType;

    #[ORM\Column(type: Types::TEXT)]
    public string $value;

    #[ORM\ManyToOne(targetEntity: Spec::class, fetch: 'LAZY')]
    public Spec $spec;

    #[ORM\Column(type: Types::JSON)]
    public array $context;

    public function getSpec(): Spec
    {
        return $this->spec;
    }
}