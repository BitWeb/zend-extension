<?php

namespace BitWeb\Zend\View\Helper;

use Zend\View\Helper\AbstractHelper;

class LinkUrls extends AbstractHelper
{

    //protected $pattern = '/^(http|https|ftp)\:\/\/[a-zA-Z0-9.-]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?\/\?([a-zA-Z0-9\-\._\?\,\'}\/\\\+&amp;%\$#\=~])*[^\.\,\)\(\s]$/mi';
    //(:[a-zA-Z0-9]*)?\/?([a-zA-Z0-9\-\._\?\,\'\/\\\+&amp;%\$#\=~])*[^\.\,\)\(\s])
    protected $pattern = '/((http|https|ftp)\:\/\/[a-z0-9\-\.]+\.[^\s]*)/i';
    protected $template = '<a href="%1$s" [attributes]>%2$s</a>';
    protected $attributedTemplate = null;
    protected $attributes = array();

    public function __invoke($text, array $attributes = array())
    {

        $this->prepareTemplate($attributes);

        return preg_replace_callback($this->pattern, array($this, 'replaceLink'), $text);
    }

    protected function prepareTemplate(array $attributes = array())
    {
        $attributes = array_merge($this->attributes, $attributes);

        $this->attributedTemplate = str_replace('[attributes]', $this->assembleAttributes($attributes), $this->template);
    }

    protected function assembleAttributes(array $attributes = array())
    {
        $attributesArray = array();
        foreach ($attributes as $key => $value) {
            $attributesArray[] = $key . '="' . $value . '"';
        }

        return implode(' ', $attributesArray);
    }

    protected function replaceLink($match)
    {
        if ($this->attributedTemplate == null) {
            $this->prepareTemplate();
        }

        return sprintf($this->attributedTemplate, $match[0], urldecode($match[0]));
    }
}