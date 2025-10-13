<?php
namespace Ufo\EAV\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Interfaces\IHaveParamsAccess;
use Ufo\EAV\Interfaces\IHaveValuesAccess;
use Ufo\EAV\Traits\ArticleHolder;
use Ufo\EAV\Traits\SpecValuesAccessors;


#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class EavEntity implements IHaveParamsAccess, IHaveValuesAccess
{
    use SpecValuesAccessors, ArticleHolder;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    protected int $id;

    #[ORM\OneToMany(targetEntity: Spec::class, mappedBy: "eav", cascade: ["persist", "remove"], fetch: 'EAGER')]
    protected Collection $specifications;

    #[ORM\OneToOne(targetEntity: Spec::class, cascade: ["persist", "remove"], fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'main_spec_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Spec $mainSpecification = null;

    #[ORM\ManyToOne(targetEntity: EavCategory::class, cascade: ['persist'], fetch: 'LAZY', inversedBy: 'entities')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?EavCategory $category = null;

    public function __construct(
        string $article = '',
    )
    {
        $this->changeArticle($article);
        $this->specifications = new ArrayCollection();
        $this->mainSpecification = $this->addSpecification(article: $article);
        $this->onPostLoad();

    }

    public function getCategory(): ?EavCategory
    {
        return $this->category;
    }

    public function setCategory(?EavCategory $category): static
    {
        $this->category = $category;
        return $this;
    }

    #[ORM\PostLoad]
    public function onPostLoad(): void
    {
        $this->state = $this->mainSpecification;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection|Spec[]
     */
    public function getSpecifications(): Collection|array
    {
        return $this->specifications;
    }

    public function addSpecification(string $name = Spec::DEFAULT, string $article = ''): Spec
    {
        $spec = new Spec($this, $name, $article);
        $this->specifications->add($spec);
        return $spec;
    }

    /**
     * @return ?Spec
     */
    public function getMainSpecification(): ?Spec
    {
        return $this->mainSpecification;
    }

    /**
     * @param Spec $mainSpecification
     */
    public function changeMainSpecification(Spec $mainSpecification): void
    {
        $this->mainSpecification = $mainSpecification;
        $this->state = $mainSpecification;
    }

}