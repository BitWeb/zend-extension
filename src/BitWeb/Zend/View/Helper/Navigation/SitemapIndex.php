<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BitWeb\Zend\View\Helper\Navigation;

use DOMDocument;
use RecursiveIteratorIterator;
use Zend\Navigation\AbstractContainer;
use Zend\Navigation\Page\AbstractPage;
use Zend\Uri;
use Zend\View\Exception;
use Zend\View;
use Zend\View\Helper\Navigation\AbstractHelper;

/**
 * Helper for printing site maps
 *
 * Use this by adding following lines to Module.php initializeNavigation() method:
 *    //Add sitemapIndex plugin to navigation plugins
 *    $navigationPluginManager = $locator->get('ViewHelperManager')->get('Navigation')->getPluginManager();
 *    $navigationPluginManager->setInvokableClass('sitemapIndex', '\BitWebExtension\View\Helper\Navigation\SitemapIndex');
 *
 * @link http://www.sitemaps.org/protocol.php
 */
class SiteMapIndex extends AbstractHelper
{
    /**
     * Namespace for the <urlset> tag
     *
     * @var string
     */
    const SITEMAP_NS = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    /**
     * Server url
     *
     * @var string
     */
    protected $serverUrl;

    /**
     * List of urls in the site map
     *
     * @var array
     */
    protected $urls = array();

    /**
     * Helper entry point
     *
     * @param  string|AbstractContainer $container container to operate on
     * @return $this
     */
    public function __invoke($container = null)
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

    /**
     * Sets server url (scheme and host-related stuff without request URI)
     *
     * E.g. http://www.example.com
     *
     * @param  string $serverUrl server URL to set (only scheme and host)
     * @return $this fluent interface, returns self
     * @throws Exception\InvalidArgumentException if invalid server URL
     */
    public function setServerUrl($serverUrl)
    {
        $uri = Uri\UriFactory::factory($serverUrl);
        $uri->setFragment('');
        $uri->setPath('');
        $uri->setQuery('');

        if ($uri->isValid()) {
            $this->serverUrl = $uri->toString();
        } else {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid server URL: "%s"',
                $serverUrl
            ));
        }

        return $this;
    }

    /**
     * Returns server URL
     *
     * @return string  server URL
     */
    public function getServerUrl()
    {
        if (!isset($this->serverUrl)) {
            $serverUrlHelper = $this->getView()->plugin('serverUrl');
            $this->serverUrl = $serverUrlHelper();
        }

        return $this->serverUrl;
    }

    // Helper methods:

    /**
     * Escapes string for XML usage
     *
     * @param  string $string string to escape
     * @return string          escaped string
     */
    protected function xmlEscape($string)
    {
        $escaper = $this->view->plugin('escapeHtml');
        return $escaper($string);
    }

    // Public methods:

    /**
     * Returns an escaped absolute URL for the given page
     *
     * @param  AbstractPage $page page to get URL from
     * @return string
     */
    public function url(AbstractPage $page)
    {
        $href = $page->getHref();

        if (!isset($href{0})) {
            // no href
            return '';
        } elseif ($href{0} == '/') {
            // href is relative to root; use serverUrl helper
            $url = $this->getServerUrl() . $href;
        } elseif (preg_match('/^[a-z]+:/im', (string)$href)) {
            // scheme is given in href; assume absolute URL already
            $url = (string)$href;
        } else {
            // href is relative to current document; use url helpers
            $basePathHelper = $this->getView()->plugin('basepath');
            $curDoc = $basePathHelper();
            $curDoc = ('/' == $curDoc) ? '' : trim($curDoc, '/');
            $url = rtrim($this->getServerUrl(), '/') . '/'
                . $curDoc
                . (empty($curDoc) ? '' : '/') . $href;
        }

        if (!in_array($url, $this->urls)) {

            $this->urls[] = $url;
            return $this->xmlEscape($url);
        }

        return null;
    }

    /**
     * Returns a DOMDocument containing the Sitemap XML for the given container
     *
     * @param  AbstractContainer $container [optional] container to get
     *                                               breadcrumbs from, defaults
     *                                               to what is registered in the
     *                                               helper
     * @return DOMDocument                           DOM representation of the
     *                                               container
     * @throws Exception\RuntimeException            if schema validation is on
     *                                               and the sitemap is invalid
     *                                               according to the sitemap
     *                                               schema, or if sitemap
     *                                               validators are used and the
     *                                               loc element fails validation
     */
    public function getDomSitemapIndex(AbstractContainer $container = null)
    {
        // Reset the urls
        $this->urls = array();

        if (null === $container) {
            $container = $this->getContainer();
        }

        // create document
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;

        // ...and sitemapIndex (root) element
        $sitemapIndex = $dom->createElementNS(self::SITEMAP_NS, 'sitemapindex');
        $dom->appendChild($sitemapIndex);

        // create iterator
        $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);

        $maxDepth = $this->getMaxDepth();
        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }
        $minDepth = $this->getMinDepth();
        if (!is_int($minDepth) || $minDepth < 0) {
            $minDepth = 0;
        }

        // iterate container
        foreach ($iterator as $page) {
            if ($iterator->getDepth() < $minDepth || !$this->accept($page)) {
                // page should not be included
                continue;
            }

            // get absolute url from page
            if (!$url = $this->url($page)) {
                // skip page if it has no url (rare case)
                // or already is in the sitemap
                continue;
            }

            // create url node for this page
            $sitemapNode = $dom->createElementNS(self::SITEMAP_NS, 'sitemap');
            $sitemapIndex->appendChild($sitemapNode);

            // put url in 'loc' element
            $sitemapNode->appendChild($dom->createElementNS(self::SITEMAP_NS, 'loc', $url));

            // add 'lastmod' element if a valid lastmod is set in page
            if (isset($page->lastmod)) {
                $lastmod = strtotime((string)$page->lastmod);

                // prevent 1970-01-01...
                if ($lastmod !== false) {
                    $lastmod = date('Y-m-d', $lastmod);
                }
                $sitemapNode->appendChild($dom->createElementNS(self::SITEMAP_NS, 'lastmod', $lastmod));
            }
        }

        return $dom;
    }

    // Zend_View_Helper_Navigation_Helper:

    /**
     * Renders helper
     *
     * Implements {@link HelperInterface::render()}.
     *
     * @param  AbstractContainer $container [optional] container to render. Default is
     *                           to render the container registered in the
     *                           helper.
     * @return string            helper output
     */
    public function render($container = null)
    {
        $dom = $this->getDomSitemapIndex($container);
        $xml = $dom->saveXML();

        return rtrim($xml, PHP_EOL);
    }
}
