<?php
/**
 * Created by PhpStorm.
 * User: poiz
 * Date: 06.07.18
 * Time: 09:41
 */

namespace App\Extensions;
use \Twig_TokenParser;
use \Twig_Token;

class PoizTokenParser extends Twig_TokenParser
{
	public function parse(Twig_Token $token)
	{
		$parser = $this->parser;
		$stream = $parser->getStream();


		$name = $stream->expect(Twig_Token::NAME_TYPE)->getValue();
		$stream->expect(Twig_Token::OPERATOR_TYPE, '>');
		$value = $parser->getExpressionParser()->parseExpression();
		$stream->expect(Twig_Token::BLOCK_END_TYPE);

		return new PoizBaseNode($name, $value, $token->getLine(), $this->getTag());
	}

	public function getTag()
	{
		return 'php';
	}
}