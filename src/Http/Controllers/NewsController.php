<?php

namespace Gabrielesbaiz\NovaCardRssNews\Http\Controllers;

use SimpleXMLElement;
use Illuminate\Support\Facades\Cache;

class NewsController
{
    public function index()
    {
        $data = Cache::remember('news-affariitaliani-motori::news', now()->addMinutes(5), function () {
            $rss = new SimpleXMLElement(file_get_contents('https://it.motor1.com/rss/articles/all/'), LIBXML_NOCDATA);

            return json_encode($rss);
        });

        return response()->json($data);
    }
}
