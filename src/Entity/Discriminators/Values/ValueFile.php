<?php

namespace Ufo\EAV\Entity\Discriminators\Values;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Ufo\EAV\Entity\Param;
use Ufo\EAV\Entity\Value;

#[ORM\Entity]
#[ORM\Table(name: 'eav_value_file')]
class ValueFile extends Value
{
    const int T_LOCAL = 1;
    const int T_CLOUD = 2;

    #[ORM\Column(name: "file_val_name", type: Types::STRING)]
    protected string $filename;

    #[ORM\Column(name: "file_val_mime_type", type: Types::STRING)]
    protected string $mimeType;


    #[ORM\Column(name: "file_val_size", type: Types::INTEGER)]
    protected int $size;

    public function __construct(
        Param            $param,
        UploadedFile     $file,
        #[ORM\Column(name: "file_val_url", type: Types::STRING)]
        protected string $url,

        #[ORM\Column(name: "file_val_storage", type: Types::INTEGER)]
        protected int    $storageType = self::T_LOCAL,

        ?string $locale = null,
        ?Value $baseValue = null,
    )
    {
        parent::__construct($param, $locale, $baseValue);
        $this->filename = $file->getClientOriginalName();
        $this->mimeType = $file->getMimeType();
        $this->size = $file->getSize();
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getStorageType(): int
    {
        return $this->storageType;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function isStoredLocally(): bool
    {
        return $this->storageType === self::T_LOCAL;
    }

    public function isStoredInCloud(): bool
    {
        return $this->storageType === self::T_CLOUD;
    }

    public function getContent(): mixed
    {
        return'';
    }
}
