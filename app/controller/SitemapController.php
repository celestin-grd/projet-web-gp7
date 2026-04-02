<?php

class SitemapController extends Controller
{
    public function index()
    {
        header('Content-Type: application/xml; charset=utf-8');

        $pages = [];

        // Ajouter la racine
        $pages[] = ['url' => ''];

        // Récupérer toutes les pages accessibles via le menu (tous les menus, toutes les URL)
        $menu = Menu::get(4); // retourne [ 'MenuName' => [ ['label'=>..,'url'=>..], ... ] ]
        foreach ($menu as $group => $items) {
            foreach ($items as $item) {
                // on retire le slash initial pour concaténation
                $url = ltrim($item['url'], '/');
                if (!in_array(['url' => $url], $pages)) {
                    $pages[] = ['url' => $url];
                }
            }
        }

        // Génération XML
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $page): ?>
    <url>
        <loc><?= 'https://web4all.local/' . htmlspecialchars($page['url']) ?></loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority><?= $page['url'] === '' ? '1.0' : '0.8' ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
