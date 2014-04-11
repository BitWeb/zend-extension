<?php

namespace BitWeb\Zend\View\Helper;

use BitWeb\Stdlib\Util\StringUtil;
use Zend\Form\View\Helper\AbstractHelper;

class SubString extends AbstractHelper
{

    public function __invoke($str, $length, $minWord = 3)
    {
        return StringUtil::subString($str, $length, $minWord);
    }
}
