<?php

namespace Ufo\EAV\Entity\Views;


use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Entity\Spec;
use Ufo\EAV\Interfaces\IHaveSpecAccess;
use Ufo\EAV\Repositories\ViewSpecDetailRepository;


#[ORM\Entity(repositoryClass:ViewSpecDetailRepository::class, readOnly:true)]
#[ORM\Table(name: SpecDetailsJson::VIEW_NAME)]
class SpecDetailsJson implements IHaveSpecAccess
{
    const VIEW_NAME = 'eav_spec_details_json_view';

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    protected int $specId;

    #[ORM\Column(type: Types::STRING)]
    protected string $specName;

    #[ORM\Column(type: Types::JSON)]
    protected array $specValues;

    #[ORM\ManyToOne(targetEntity: Spec::class, fetch: 'LAZY')]
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
     * @return array
     */
    public function getSpecValues(): array
    {
        return $this->specValues;
    }

    /**
     * @return Spec
     */
    public function getSpec(): Spec
    {
        return $this->spec;
    }

}