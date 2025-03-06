<?php

namespace Ufo\EAV\Entity\Translations;


use Doctrine\ORM\Mapping as ORM;
use Ufo\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Ufo\DoctrineBehaviors\Model\Translatable\TranslationTrait;
use Ufo\EAV\Entity\EavCategory;

#[ORM\Entity]
class EavCategoryTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public static function getTranslatableEntityClass(): string
    {
        return EavCategory::class;
    }

}