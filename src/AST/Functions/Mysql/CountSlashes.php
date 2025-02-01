<?php
namespace  Ufo\EAV\AST\Functions\Mysql;

use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

final class CountSlashes extends FunctionNode
{
    public const string FUNCTION_NAME = 'COUNT_SLASHES';

    private readonly Node $stringExpression;

    public function parse(Parser $parser): void
    {

        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->stringExpression = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        // Повертаємо SQL для підрахунку кількості слешів
        $pathExpression = $this->stringExpression->dispatch($sqlWalker);

        return sprintf(
            "(LENGTH(%s) - LENGTH(REPLACE(%s, '/', '')))",
            $pathExpression,
            $pathExpression
        );
    }
}