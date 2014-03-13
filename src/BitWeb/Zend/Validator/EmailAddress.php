<?php

namespace BitWeb\Zend\Validator;

class EmailAddress extends \Zend\Validator\EmailAddress
{


    public function isValid($value)
    {
        $isValid = parent::isValid($value);

        if (!$isValid) {
            $this->abstractOptions['messages'] = array();
            $this->error(self::INVALID_FORMAT);
        }

        return $isValid;
    }


}