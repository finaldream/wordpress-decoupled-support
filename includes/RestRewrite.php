<?php
/**
 * Rewrite Wordpress URLs for Decoupled
 */

use \DecoupledSupport\UrlUtils;

class RestRewrite
{

    /**
     * Upload URL
     * @var string
     */
    private $uploadDomain;


    /**
     * RestRewrite constructor.
     */
    public function __construct()
    {

        $this->uploadDomain = defined('DECOUPLED_UPLOAD_URL') ? DECOUPLED_UPLOAD_URL : null;
    }


    /**
     * Start rewrite URLs.
     */
    public function rewrite()
    {

        if (!empty($this->uploadDomain)) {
            add_filter('wp_get_attachment_url', [$this, 'getAttachmentURL'], 100);
            add_filter('wp_get_attachment_image_src', [$this, 'getAttachmentImageSrc'], 100);
            add_filter('wp_calculate_image_srcset', [$this, 'calculateImageSrcset'], 100);
            add_filter('the_content', [$this, 'filterUploadURL'], 100);
            add_filter('the_excerpt', [$this, 'filterUploadURL'], 100);
			add_filter('the_content', [$this, 'resolveSafeLinks'], 100);
			add_filter('the_excerpt', [$this, 'resolveSafeLinks'], 100);
        }
    }


    /**
     * Get attachment url
     *
     * @param string $url
     *
     * @return mixed
     */
    public function getAttachmentURL($url)
    {

        $new_url = $this->replaceDomain($url, $this->uploadDomain);

        return (false !== $new_url) ? $new_url : $url;
    }


    /**
     * Replace URL with wp_get_attachment_image_src
     *
     * @param $image
     *
     * @return mixed
     */
    public function getAttachmentImageSrc($image)
    {

        if (isset($image[0])) {
            $url      = $this->replaceDomain($image[0], $this->uploadDomain);
            $image[0] = $url;
        }

        return $image;
    }


    /**
     * Replace WP srcset attribute URLs
     *
     * @param $sources
     *
     * @return array
     */
    public function calculateImageSrcset($sources)
    {

        $newSources = [];

        foreach ($sources as $width => $source) {
            $source['url']      = $this->replaceDomain($source['url'], $this->uploadDomain);
            $newSources[$width] = $source;
        }

        return $newSources;
    }


    /**
     * Rewrite Upload URL
     *
     * @param $url
     * @param $newDomain
     *
     * @return string
     */
    public function replaceDomain($url, $newDomain)
    {

        $path = parse_url($url, PHP_URL_PATH);

        // Remove uploads directory from path. TODO: find better solution?
        $path = str_replace(['../uploads', '/uploads'], '', $path);

        return rtrim($newDomain, '/') . '/' . ltrim($path, '/');
    }


    /**
     * Filter upload URL in post content
     *
     * @param string $content
     *
     * @return string
     */
    public function filterUploadURL($content)
    {

        if (empty($content)) {
            return $content;
        }

        $urls         = $this->findURLs($content, 'img', 'src');
        $replacements = $this->prepareURLs($urls, $this->uploadDomain);
        $content      = $this->replaceURLs($content, $urls, $replacements);

        // Find and replace anchor with attachment links
        $urls = $this->findURLs($content, 'a', 'href', '(.jpg$|.jpeg$|.gif$|.png$|[?&]attachment_id=(\d+))');
        $newUrls = [];

        foreach ($urls as $index => $url) {
            if (preg_match('([?&]attachment_id=(\d+))', $url, $matches)) {
                $src = wp_get_attachment_image_src($matches[1], 'full')[0] ?? false;

                if ($src) {
                    $newUrls[] = $src;
                }
            }

            $newUrls[] = $url;
        }

        $replacements = $this->prepareURLs($newUrls, $this->uploadDomain);
        $content = $this->replaceURLs($content, $urls, $replacements);

        return $content;
    }


    /**
     * Find URLs from content
     *
     * @param string $content
     * @param string $tag
     * @param string $attribute
     * @param bool|string $filter
     *
     * @return array
     */
    protected function findURLs($content, $tag, $attribute, $filter = false)
    {

        $urls = [];

        if (!preg_match_all("/<{$tag} [^>]+>/", $content, $matches) || !isset($matches[0])) {
            return $urls; // No img tags found
        }

        $matches = array_unique($matches[0]);

        foreach ($matches as $match) {
            if (!preg_match("/{$attribute}=\\\?[\"\']+([^\"\'\\\]+)/", $match, $url) || !isset($url[1])) {
                continue; // Invalid
            }

            $domain = preg_replace('(^https?://)', '', site_url());
            if (!empty($domain) && strpos($url[1], $domain) === false) {
                continue; // External links
            }

            if (is_string($filter) && !preg_match($filter, $url[1], $matches)) {
                continue; // Filter not matched
            }

            $urls[] = $url[1];
        }

        return $urls;
    }


    /**
     * Prepare URLs for replacement
     *
     * @param array $urls
     * @param string $newDomain
     *
     * @return array
     */
    protected function prepareURLs($urls, $newDomain)
    {

        $newURLs = [];

        foreach ($urls as $url) {
            $newURLs[] = $this->replaceDomain($url, $newDomain);
        }

        return $newURLs;
    }


    /**
     * Replace URLs in content
     *
     * @param $content
     * @param $urls
     * @param $replacements
     *
     * @return string
     */
    protected function replaceURLs($content, $urls, $replacements)
    {

        if (empty($urls) || empty($replacements)) {
            return $content;
        }

        return str_replace($urls, $replacements, $content);
    }


	/**
	 * Resolve safe links
	 * @param $content
	 * @return mixed
	 */
    public function resolveSafeLinks($content)
	{
		preg_match_all("/<a (?:.*)href=[\"\']((?:(?![\"\'])[^>])*)[\"\'][^>]*>/siU", $content, $matches);

		if (!empty($matches[1])) {
			$links = array_unique($matches[1]);

			foreach ($links as $link) {
				preg_match("/\?p=(\d+)/", $link, $match);

				if (!empty($match[1])) {
					$content = str_replace($link, UrlUtils::getPostPermalink($match[1]), $content);
				}
			}
		}

		return $content;
	}

}
