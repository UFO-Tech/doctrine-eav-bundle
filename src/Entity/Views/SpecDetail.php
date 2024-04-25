<?php

namespace Ufo\EAV\Entity\Views;


use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Interfaces\IHaveSpecAccess;
use Ufo\EAV\Repositories\ViewSpecDetailRepository;


#[ORM\Entity(repositoryClass:ViewSpecDetailRepository::class, readOnly:true)]
#[ORM\Table(name: SpecDetail::VIEW_NAME)]
class SpecDetail implements IHaveSpecAccess
{
    const VIEW_NAME = 'eav_spec_details_view';

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    protected int $specId;

    #[ORM\Column(type: "string")]
    protected string $specName;

    #[ORM\Column(type: "string")]
    protected string $paramName;

    #[ORM\Column(type: "string")]
    protected string $paramTag;

    #[ORM\Column(type: "string")]
    protected string $valueType;

    #[ORM\Column(type: "text")]
    protected string $value;

    #[ORM\ManyToOne(targetEntity: Spec::class,fetch: 'LAZY')]
    protected Spec $spec;

    /**
     * @return int
     */
    public function getSpecId(): int
    {
        return $this->specId;
    }

    /**
     * @return string
     */
    public function getSpecName(): string
    {
        return $this->specName;
    }

    /**
     * @return string
     */
    public function getParamName(): string
    {
        return $this->paramName;
    }

    /**
     * @return string
     */
    public function getParamTag(): string
    {
        return $this->paramTag;
    }

    /**
     * @return string
     */
    public function getValueType(): string
    {
        return $this->valueType;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public function getSpec(): Spec
    {
        return $this->spec;
    }
}