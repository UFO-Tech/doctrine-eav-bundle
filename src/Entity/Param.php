<?php

namespace Ufo\EAV\Entity;

use App\Entity\Enums\ContractTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Repositories\ParamRepository;
use Ufo\EAV\Utils\DiscriminatorType;

#[ORM\Entity(repositoryClass: ParamRepository::class)]
#[ORM\Table(name: 'eav_params')]
#[ORM\Index(name: "param_tag_idx", columns: ["tag"])]
class Param
{
    #[ORM\ManyToMany(targetEntity: Spec::class, mappedBy: "params", cascade: ["persist"], fetch: 'LAZY')]
    protected Collection $specs;

    #[ORM\OneToMany(targetEntity: Value::class, mappedBy: "param", cascade: ["persist"], fetch: 'LAZY')]
    protected Collection $values;

    #[ORM\Column(type: DiscriminatorType::class, length: 255, nullable: true)]
    protected ?string $type;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: false)]
        protected string $tag,

        DiscriminatorType $type = DiscriminatorType::STRING,

        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        protected ?string $name = null,

        #[ORM\Column(type: Types::BOOLEAN)]
        protected bool $filtered = true,

        #[ORM\Column(type: Types::JSON, nullable: true)]
        protected array $context = []
    )
    {
        $this->type = $type->value;
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
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     */
    public function setContext(array $context): static
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return bool
     */
    public function addToContext(string $key, mixed $data): static
    {
        $this->context[$key] = $data;
        return $this;
    }

    public function getContextByKey(string $key): mixed
    {
        return $this->context[$key] ?? null;
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

    public function getType(): DiscriminatorType
    {
        return $this->type ? DiscriminatorType::tryFrom($this->type) : DiscriminatorType::STRING;
    }

    public function changeType(DiscriminatorType $type): static
    {
        $this->type = $type->value;
        return $this;
    }

}
