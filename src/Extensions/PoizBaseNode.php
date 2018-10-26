<?php
/**
 * Created by PhpStorm.
 * User: poiz
 * Date: 06.07.18
 * Time: 09:41
 */

namespace App\Extensions;

use App\Extensions\PoizTokenParser;
use \Twig_Node;
use \Twig_Node_Expression;
use \Twig_Compiler;

class PoizBaseNode extends Twig_Node
{

	public function __construct($name, Twig_Node_Expression $value, $line, $tag = null)
	{
		parent::__construct(array('value' => $value), array('name' => $name), $line, $tag);
	}

	public function compile(Twig_Compiler $compiler)
	{
		$compiler
			->addDebugInfo($this)
			->write('$context[\''.$this->getAttribute('name').'\'] = ')
			->subcompile($this->getNode('value'))
			->raw(";\n")
		;
	}
}