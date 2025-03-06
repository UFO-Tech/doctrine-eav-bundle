<?php

namespace  Ufo\EAV\AST\Functions\Mysql;

use Doctrine\ORM\Query\AST\ASTException;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;


final class PowerFunction extends FunctionNode
{
    public const string FUNCTION_NAME = 'POWER';

    private Node $firstArgument;
    private Node $secondArgument;

    /**
     * @throws QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->firstArgument = $parser->ArithmeticPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->secondArgument = $parser->ArithmeticPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    /**
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return self::FUNCTION_NAME . '(' .
               $this->firstArgument->dispatch($sqlWalker) . ', ' .
               $this->secondArgument->dispatch($sqlWalker) . ')';
    }
}