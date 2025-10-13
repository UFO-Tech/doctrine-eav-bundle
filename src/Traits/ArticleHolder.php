<?php

namespace Ufo\EAV\Traits;

use Doctrine\ORM\Mapping as ORM;

use function rand;
use function time;

trait ArticleHolder
{
    #[ORM\Column(type: "string", length: 50, unique: true)]
    protected string $article = '';

    public function getArticle(): string
    {
        return $this->article;
    }

    public function changeArticle(string $article): void
    {
        $this->article = $article;
        $this->defaultArticle();
    }

    protected function defaultArticle(): void
    {
        if (empty($this->article)) {
            $this->article = 'tmp_' . time() . '_' . rand();
        }
    }
}