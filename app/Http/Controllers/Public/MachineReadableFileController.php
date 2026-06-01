<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\FeedBuilder;
use App\Services\LlmsTxtBuilder;
use App\Services\RobotsBuilder;
use App\Services\SitemapBuilder;
use Illuminate\Http\Response;

class MachineReadableFileController extends Controller
{
    public function sitemapIndex(SitemapBuilder $sitemapBuilder): Response
    {
        return $this->xml($sitemapBuilder->index());
    }

    public function sitemapPosts(SitemapBuilder $sitemapBuilder): Response
    {
        return $this->xml($sitemapBuilder->posts());
    }

    public function sitemapCategories(SitemapBuilder $sitemapBuilder): Response
    {
        return $this->xml($sitemapBuilder->categories());
    }

    public function sitemapTags(SitemapBuilder $sitemapBuilder): Response
    {
        return $this->xml($sitemapBuilder->tags());
    }

    public function sitemapPages(SitemapBuilder $sitemapBuilder): Response
    {
        return $this->xml($sitemapBuilder->pages());
    }

    public function feed(FeedBuilder $feedBuilder): Response
    {
        return response($feedBuilder->rss(), 200, [
            'Content-Type' => 'application/rss+xml',
        ]);
    }

    public function robots(RobotsBuilder $robotsBuilder): Response
    {
        return $this->plainText($robotsBuilder->text());
    }

    public function llms(LlmsTxtBuilder $llmsTxtBuilder): Response
    {
        return $this->plainText($llmsTxtBuilder->text());
    }

    private function xml(string $content): Response
    {
        return response($content, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    private function plainText(string $content): Response
    {
        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
