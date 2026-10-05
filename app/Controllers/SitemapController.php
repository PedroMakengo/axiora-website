<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Artigo;
use App\Models\CategoriaBlog;

class SitemapController extends Controller
{
    public function index(): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        $urls = [
            ['loc' => URL_BASE . '/',     'prioridade' => '1.0', 'frequencia' => 'weekly'],
            ['loc' => URL_BASE . '/blog', 'prioridade' => '0.8', 'frequencia' => 'daily'],
        ];

        foreach ((new CategoriaBlog())->ativasComContagem() as $categoria) {
            if ((int) $categoria['total_artigos'] === 0) {
                continue;
            }
            $urls[] = [
                'loc'        => URL_BASE . '/blog/categoria/' . $categoria['slug'],
                'prioridade' => '0.5',
                'frequencia' => 'weekly',
            ];
        }

        foreach ((new Artigo())->paraSitemap() as $artigo) {
            $urls[] = [
                'loc'        => URL_BASE . '/blog/' . $artigo['slug'],
                'prioridade' => '0.7',
                'frequencia' => 'monthly',
                'lastmod'    => $artigo['atualizado_em'] ?: $artigo['publicado_em'],
            ];
        }

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            if (!empty($url['lastmod'])) {
                echo "    <lastmod>" . date('Y-m-d', strtotime($url['lastmod'])) . "</lastmod>\n";
            }
            echo "    <changefreq>{$url['frequencia']}</changefreq>\n";
            echo "    <priority>{$url['prioridade']}</priority>\n";
            echo "  </url>\n";
        }
        echo '</urlset>';
        exit;
    }
}
