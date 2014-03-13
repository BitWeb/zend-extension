<?php

namespace BitWebExtension\View\Helper;

use BitWebExtension\Util\StringUtil;

use Zend\Form\View\Helper\AbstractHelper;

class SubString extends AbstractHelper {

	public function __invoke($str, $length, $minword = 3) {
		
		return StringUtil::subString($str, $length, $minword);
	}
	
}