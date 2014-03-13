<?php
namespace BitWeb\Zend\View\Cache;

class Factory
{
    protected $cacheDir;

    public function __construct($config)
    {
        $this->cacheDir = $config['dir'];
    }

    /**
     * @param $cacheTimeStringOrSeconds
     * @param array $idFields
     * @param $path
     * @return Cache
     */
    public function createCache($cacheTimeStringOrSeconds, $idFields = array(), $path)
    {
        $cache = new Cache($cacheTimeStringOrSeconds, $idFields);
        $cache->dir = $this->cacheDir;
        $cache->path = $path;

        return $cache;
    }

}

?>