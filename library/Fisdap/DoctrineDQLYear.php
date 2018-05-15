<?php
/**
 * Created by PhpStorm.
 * User: sctap_000
 * Date: 8/25/2014
 * Time: 11:58 AM
 */

namespace Fisdap;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

class DoctrineDQLYear extends FunctionNode
{
    private $arg;

    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf('YEAR(%s)', $this->arg->dispatch($sqlWalker));
    }

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->arg = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
