<?php
namespace BitWeb\Zend\View\Renderer;

use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\ResolverInterface;

class CachedViewRenderer implements RendererInterface
{

    private $fallbackRenderer;

    public function __construct($fallbackRenderer)
    {
        $this->fallbackRenderer = $fallbackRenderer;
    }

    public function getEngine()
    {
        return parent::getEngine();
    }

    public function render($model, $values = null)
    {
        $cache = $model->getCache();
        $render = false;

        if ($cache != null) {
            $cache->setPath($model->getTemplate());
            $render = $cache->getRender();
        }

        if ($render === false) {
            if ($model->getRenderCallback() != null) {
                $populatingFunction = $model->getRenderCallback();
                $populatingFunction($model);
            }
            $render = $this->fallbackRenderer->render($model, $values);
            if ($cache != null) {
                $lastError = error_get_last();
                if ($lastError == null || $lastError ^ E_STRICT == 0) {
                    $cache->setRender($render);
                } else {
                    throw new \RuntimeException('View cache rendering failed: ' . print_r($lastError, true));
                }
            }
        }

        return $render;
    }

    public function setResolver(ResolverInterface $resolver)
    {
        parent::setResolver($resolver);
    }

}
