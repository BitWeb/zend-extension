<?php
namespace BitWebExtension\View;

use Zend\View\Model\ViewModel;

class CachedViewModel extends ViewModel {
	
	protected static $cacheFactory;
	protected $cache;
	protected $renderCallback;
	
	public static function setDefaultCacheFactory(Cache\Factory $cacheFactory) {
		self::$cacheFactory = $cacheFactory;
	}

	public function setCache($idFields = array(), $cacheTimeStringOrSeconds = null) {
		$this->cache = self::$cacheFactory->createCache($cacheTimeStringOrSeconds, $idFields, $this->getTemplate());
		if ($this->getTemplate() != null) {
			$this->cache->setPath($this->getTemplate());
		}
		
		return $this;
	}
	
	/**
	 * @return \BitWebExtension\View\Cache\Cache
	 */
	public function getCache() {
		
		return $this->cache;
	}
	
	public function setRenderCallback(\Closure $renderCallback) {
		$this->renderCallback = $renderCallback;
	}
	
	public function getRenderCallback() {
		return $this->renderCallback;
	}
}

?>