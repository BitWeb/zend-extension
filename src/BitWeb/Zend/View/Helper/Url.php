<?php

namespace BitWeb\Zend\View\Helper;


class Url extends \Zend\View\Helper\Url
{

    public function __invoke($name = null, array $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        return urldecode(parent::__invoke($name, $params, $options, $reuseMatchedParams));
    }

}
