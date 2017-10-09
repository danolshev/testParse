<?php

namespace app\app\Parse;

/**
* 
*/
class Parser
{
    protected $url;

    function __construct($url)
    {
        $this->url = $url;
    }

    public function parse()
    {
        $siteContent = file_get_contents($this->url);
        $siteUrl = parse_url($this->url, PHP_URL_HOST);

        $title = [];
        preg_match('/<h1 class="detail">(.*)<\/h1>/s', $siteContent, $title);
        $description = [];
        preg_match('/id="productTabContent".*<p>(.*)<\/p>/Us', $siteContent, $description);
        $carousel = [];
        preg_match('/id="detail-photo-carousel"(.*)id="product_desc"/Us', $siteContent, $carousel);
        $imagesSrc = [];
        
        preg_match_all('/<img.*src="(.*)"/Us', $carousel[1], $imagesSrc);
        $imagesUrl = [];
        foreach ($imagesSrc[1] as $imageSrc) {
            $imagesUrl[] = $siteUrl . $imageSrc;
        }
        $desc = [];
        preg_match('/id="product_desc".*table>/Us', $siteContent, $desc);
        
        $attrs = [];
        preg_match_all('/<tr>.*<td>(.*)\((.*)\)<\/td>.*<td>(.*)<\/td>/Us', $desc[0], $attrs, PREG_SET_ORDER);
        $resultAttrs = [];
        foreach ($attrs as $attr) {
            $temp = [];
            $temp['name'] = trim($attr[1]);
            $temp['value'] = trim($attr[3]);
            $temp['unit'] = trim($attr[2]);
            $resultAttrs[] = $temp;
        }
        $result = [
            'id' => preg_replace('/\//', '', parse_url($this->url, PHP_URL_PATH)),
            'url' => $this->url,
            'created_at' => '',
            'title' => $title[1],
            'description' => $description[1],
            'sku' => '',
            'price' => '',
            'currency' => '',
            'images' => $imagesUrl,
            'attrs' => $resultAttrs,
        ];

        return json_encode($result);
    }
}