<?php
namespace BitWebExtension\View\Strategy;

use Zend\EventManager\EventManagerInterface;

use BitWebExtension\View\Renderer\CachedViewRenderer;

use BitWebExtension\View\CachedViewModel;

use Zend\View\ViewEvent;

use Zend\View\Renderer\PhpRenderer;

use Zend\EventManager\ListenerAggregateInterface;

class CachedViewStrategy implements ListenerAggregateInterface {
	
	/**
	 * @var \Zend\Stdlib\CallbackHandler[]
	 */
	protected $listeners = array();
	
	/**
	 * @var CachedViewRenderer
	 */
	protected $renderer;
	
	public function __construct(CachedViewRenderer $renderer)
	{
		$this->renderer = $renderer;
	}
	
	public function attach(EventManagerInterface $events, $priority = 1) {
		$this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, array($this, 'selectRenderer'), $priority);
	}

	public function detach(EventManagerInterface $events) {
		foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
	}
	
	/**
	 * Detect if we should use the JsonRenderer based on model type and/or
	 * Accept header
	 *
	 * @param  ViewEvent $e
	 * @return null|JsonRenderer
	 */
	public function selectRenderer(ViewEvent $e) {
		$model = $e->getModel();
		if ($model instanceof CachedViewModel) {
			if ($model->getCache() != null) {
			
				return $this->renderer;
			}
		}
	}
}

?>