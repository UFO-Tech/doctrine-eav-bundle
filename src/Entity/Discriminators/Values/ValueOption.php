<?php

namespace Ufo\EAV\Entity\Discriminators\Values;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Entity\Option;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Entity\Value;

#[ORM\Entity]
class ValueOption extends Value
{

    #[ORM\ManyToMany(targetEntity: Option::class, cascade: ["persist"])]
    #[ORM\JoinTable(name: 'eav_value_options',
        joinColumns: [new ORM\JoinColumn(name: 'value_option_id', referencedColumnName: 'id', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'param_option_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    )]
    protected Collection $options;

    public function __construct(
        Param $param,
        protected array $content,
        ?string $locale = null,
        ?Value $baseValue = null,
    )
    {
        parent::__construct($param, $locale, $baseValue);
        $this->options = new ArrayCollection();
        foreach ($this->content as $option) {
            if ($option instanceof Option){
                $this->addOption($option);
            }
        }
    }

    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(Option $option): void
    {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
        }
    }

    public function removeOption(Option $option): void
    {
        if ($this->options->contains($option)) {
            $this->options->removeElement($option);
        }
    }

    public function getContent(): array
    {
        return $this->getOptions()
            ->map(
                function (Option $option) {
                    return $option->getValue();
                }
            )
            ->toArray();
    }
}
