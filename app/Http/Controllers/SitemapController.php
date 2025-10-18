<?php

namespace App\Http\Controllers;

use App\Models\Bible;

class SitemapController extends Controller
{
    /**
     * Generate and return the sitemap.xml
     */
    public function index()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Home page
        $sitemap .= $this->addUrl(route('home'), '1.0', 'daily');

        // Static pages
        $sitemap .= $this->addUrl(route('bibles'), '0.9', 'daily');
        $sitemap .= $this->addUrl(route('login'), '0.5', 'monthly');
        $sitemap .= $this->addUrl(route('register'), '0.5', 'monthly');

        // Dynamic Bible pages - get all bibles
        $bibles = Bible::all();
        foreach ($bibles as $bible) {
            $sitemap .= $this->addUrl(route('bible_show', $bible->id), '0.8', 'weekly');
        }

        $sitemap .= '</urlset>';

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Helper method to add a URL to the sitemap
     */
    private function addUrl($url, $priority = '0.5', $changefreq = 'monthly')
    {
        $lastmod = now()->toDateString();

        return sprintf(
            '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%s</priority></url>',
            htmlspecialchars($url),
            $lastmod,
            $changefreq,
            $priority
        );
    }
}
