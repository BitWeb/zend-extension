<?php
namespace BitWeb\Zend\View\Cache;

class Cache extends Factory
{
    const DEFAULT_CACHE_INTERVAL_STRING = '1 week';

    protected static $cachedFiles;

    protected $dir;
    protected $path;
    protected $cacheFileFullPath;
    protected $cacheTimeStringOrSeconds;
    protected $idFields;

    public function __construct($cacheTimeStringOrSeconds, $idFields = array())
    {
        $this->cacheTimeStringOrSeconds = ($cacheTimeStringOrSeconds != null) ? $cacheTimeStringOrSeconds : self::DEFAULT_CACHE_INTERVAL_STRING;
        $this->idFields = $idFields;
    }

    /**
     * Set the Template path. Usually renderer calls this, but can also be set specifically in the action.
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    public function isValid()
    {

        if ($this->isCacheFileCached()) {
            return true;
        }

        if ($this->path == null) {
            return false;
        }

        if (!file_exists($this->getCacheFileFullPath())) {

            return false;
        }

        if (is_int($this->cacheTimeStringOrSeconds)) {
            $expirationTimestamp = time() - $this->cacheTimeStringOrSeconds;
        } else {
            $expirationDate = new \DateTime();
            $expirationDate->modify($this->cacheTimeStringOrSeconds . ' ago');
            $expirationTimestamp = $expirationDate->getTimestamp();
        }
        //If file was modified (created) more than 1 week ago (is older then 1 week) - do not use cache
        if (filemtime($this->getCacheFileFullPath()) < $expirationTimestamp) {
            //echo filemtime($this->getCacheFileFullPath()).' < '.$expirationDate->getTimestamp().'<br>';

            return false;
        }

        return true;
    }

    public function getRender()
    {
        if ($this->isValid() === false) {

            return false;
        }

        //NOTE: When subviews are received via forward(), they are rendered twice. We do not read the same file twice, cache results are cached in an array.
        //$cachedFileFullPath = ;

        if (!$this->isCacheFileCached()) {
            $cachedFile = $this->loadAndPutCacheFileToCache();
        } else {
            $cachedFile = $this->getCacheFileFromCache();
        }

        return $cachedFile;
    }

    public function setRender($rendering)
    {
        if (!file_exists($this->getFullPath())) {
            mkdir($this->getFullPath(), 0777, true);
        }

        file_put_contents($this->getCacheFileFullPath(), $rendering);
    }

    protected function isCacheFileCached()
    {

        return isset(self::$cachedFiles[$this->getCacheFileFullPath()]);
    }

    protected function getCacheFileFromCache()
    {

        return self::$cachedFiles[$this->getCacheFileFullPath()];
    }

    protected function loadAndPutCacheFileToCache()
    {
        $cachedFile = file_get_contents($this->getCacheFileFullPath());
        self::$cachedFiles[$this->getCacheFileFullPath()] = $cachedFile;

        return $cachedFile;
    }

    protected function getCacheFileFullPath()
    {
        if (!isset($this->cacheFileFullPath)) {
            $this->cacheFileFullPath = $this->getFullPath() . DIRECTORY_SEPARATOR . $this->getCacheId();
        }

        return $this->cacheFileFullPath;
    }

    protected function getFullPath()
    {

        return $this->dir . DIRECTORY_SEPARATOR . $this->path;
    }

    protected function getCacheId()
    {

        return sha1($this->path . serialize($this->idFields));
    }

}
