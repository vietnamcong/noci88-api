<?php

namespace Core\Providers\Collective;

use Collective\Html\HtmlBuilder;
use Illuminate\Support\HtmlString;

class BaseHtmlBuilder extends HtmlBuilder
{
    /**
     * @param string $url
     * @param array $attributes
     * @param null $secure
     * @return HtmlString
     */
    public function script($url, $attributes = [], $secure = null): HtmlString
    {
        $attributes['src'] = buildVersion($this->url->asset($url, $secure));

        return $this->toHtmlString('<script' . $this->attributes($attributes) . '></script>');
    }

    /**
     * @param string $url
     * @param array $attributes
     * @param null $secure
     * @return HtmlString
     */
    public function style($url, $attributes = [], $secure = null): HtmlString
    {
        $defaults = ['media' => 'all', 'type' => 'text/css', 'rel' => 'stylesheet'];
        $attributes = array_merge($defaults, $attributes);
        $attributes['href'] = buildVersion($this->url->asset($url, $secure));

        return $this->toHtmlString('<link' . $this->attributes($attributes) . '>');
    }

    /**
     * @param string $url
     * @param null $alt
     * @param array $attributes
     * @param null $secure
     * @return HtmlString
     */
    public function image($url, $alt = null, $attributes = [], $secure = null): HtmlString
    {
        $attributes['alt'] = $alt;
        return $this->toHtmlString('<img src="' . buildVersion($this->url->asset($url, $secure)) . '"' . $this->attributes($attributes) . '>');
    }
}
