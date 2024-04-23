<?php

namespace Ufo\EAV\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Repositories\ParamRepository;

#[ORM\Entity(repositoryClass: ParamRepository::class)]
#[ORM\Table(name: 'eav_params')]
class Param
{
    #[ORM\ManyToMany(targetEntity: Spec::class, mappedBy: "params", cascade: ["persist"])]
    protected Collection $specs;

    #[ORM\OneToMany(mappedBy: "param", targetEntity: Value::class, cascade: ["persist"])]
    protected Collection $values;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: "string", length: 255, unique: true, nullable: false)]
        protected string $tag,

        #[ORM\Column(type: "string", length: 255, nullable: true)]
        protected ?string $name = null,

        #[ORM\Column(type: "boolean")]
        protected bool $filtered = true,

        #[ORM\Column(type: "json", nullable: true)]
        protected array $jsonSchema = []
    )
    {
        $this->name = $name ?? $this->tag;
        $this->specs = new ArrayCollection();
        $this->values = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function changeTag(string $tag): void
    {
        $this->tag = $tag;
    }

    public function getSpecs(): Collection
    {
        return $this->specs;
    }

    /**
     * @return array
     */
    public function getJsonSchema(): array
    {
        return $this->jsonSchema;
    }

    /**
     * @param array $jsonSchema
     */
    public function setJsonSchema(array $jsonSchema): void
    {
        $this->jsonSchema = $jsonSchema;
    }

    /**
     * @return bool
     */
    public function isFiltered(): bool
    {
        return $this->filtered;
    }

    public function setFilteredTrue(): void
    {
        $this->filtered = true;
    }
    public function setFilteredFalse(): void
    {
        $this->filtered = false;
    }

    /**
     * @return Collection
     */
    public function getValues(): Collection
    {
        return $this->values;
    }
}
