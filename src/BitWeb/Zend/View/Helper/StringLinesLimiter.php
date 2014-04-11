<?php

namespace BitWeb\Zend\View\Helper;

use BitWeb\Stdlib\Util\StringUtil;
use Zend\View\Helper\AbstractHelper;

class StringLinesLimiter extends AbstractHelper
{

    public function __invoke($string, $limit, $removeEmptyLines = false)
    {

        return StringUtil::stringLinesLimiter($string, $limit, $removeEmptyLines);
    }
}
