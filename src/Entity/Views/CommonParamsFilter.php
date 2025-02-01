<?php

namespace Ufo\EAV\Entity\Views;


use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ufo\EAV\Repositories\CommonParamsFilterRepository;

#[ORM\Entity(repositoryClass: CommonParamsFilterRepository::class, readOnly: true)]
#[ORM\Table(name: CommonParamsFilter::VIEW_NAME)]
readonly class CommonParamsFilter
{
    const string VIEW_NAME = 'eav_common_params_filter_view';

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING)]
    public string $uniqueId;

    #[ORM\Column(type: Types::STRING)]
    public string $paramName;

    #[ORM\Column(type: Types::STRING)]
    public string $paramTag;

    #[ORM\Column(type: Types::STRING)]
    public string $valueType;

    #[ORM\Column(type: Types::TEXT)]
    public string $value;

    #[ORM\Column(type: Types::INTEGER)]
    public int $specCount;

    #[ORM\Column(type: Types::INTEGER)]
    public int $totalCount;
}