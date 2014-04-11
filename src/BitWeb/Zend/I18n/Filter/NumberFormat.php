<?php

namespace BitWeb\Zend\I18n\Filter;

use Zend\I18n\Filter\NumberFormat as ZendNumberFormat;

class NumberFormat extends ZendNumberFormat
{

    const TYPE_FLOAT = 'float'; /* float is alias for double */
    const TYPE_INTEGER = 'integer';

    protected $type = self::TYPE_FLOAT;
    protected $decimalPlaces = 2;
    protected $thousandsSeparator = '';
    protected $decimalPoint = '.';

    /**
     * @param string $type
     * @param int $decimalPlaces
     * @param string $decimalPoint
     * @param string $thousandsSeparator
     */
    public function __construct(
        $type = null,
        $decimalPlaces = null,
        $decimalPoint = null,
        $thousandsSeparator = null
    )
    {
        if ($type != null) {
            $this->type = $type;
        }

        if ($decimalPlaces != null) {
            $this->decimalPlaces = $decimalPlaces;
        }

        if ($decimalPoint != null) {
            $this->decimalPoint = $decimalPoint;
        }

        if ($thousandsSeparator != null) {
            $this->thousandsSeparator = $thousandsSeparator;
        }
    }

    /**
     * @param int|float|string $number
     * @return mixed
     * @see Zend\Filter.FilterInterface::filter()
     */
    public function filter($number)
    {
        $number = str_replace(',', $this->getDecimalPoint(), $number); // Replace ',' with defined decimal point

        if ($this->getType() == self::TYPE_FLOAT) {

            $number = (float)$number;

            return number_format($number, $this->getDecimalPlaces(), $this->getDecimalPoint(), $this->getThousandsSeparator());

        } else if ($this->getType() == self::TYPE_INTEGER) {

            if (is_int($number)) {

                return $number;
            }

            return $number;
        }

        return $number;
    }

    /**
     * @return string
     */
    public function getType()
    {

        return $this->type;
    }

    /**
     * @return int
     */
    public function getDecimalPlaces()
    {

        return $this->decimalPlaces;
    }

    /**
     * @return string
     */
    public function getDecimalPoint()
    {

        return $this->decimalPoint;
    }

    /**
     * @return string
     */
    public function getThousandsSeparator()
    {

        return $this->thousandsSeparator;
    }
}
