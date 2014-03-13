<?php

namespace BitWebExtension\View\Helper;

use BitWebExtension\Util\StringUtil;

use Zend\Form\View\Helper\AbstractHelper;

class SubStringBrutal extends AbstractHelper {

	public function __invoke($str, $length) {
		
		return StringUtil::subStringBrutal($str, $length);
	}
	
}