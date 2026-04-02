<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Gestionnaire de rendu des vues.
 *
 * Permet de séparer le layout principal et le contenu des vues spécifiques,
 * et fournit un helper pour la pagination.
 */
class View
{
    private static $twig;


    public static function init(): void
    {
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        self::$twig = new Environment($loader, [
            'cache' => false,
        ]);
        // 🔥 VARIABLES GLOBALES
        self::$twig->addGlobal('APP_NAME', APP_NAME);
        self::$twig->addGlobal('CDN', CDN);
        self::$twig->addGlobal('PREFIX', PREFIX);
        self::$twig->addGlobal('STATIQUE', STATIQUE);

        self::$twig->addGlobal('PILOTE', PILOTE);
        self::$twig->addGlobal('POSTULE', POSTULE);
        self::$twig->addGlobal('ETUDIANT', ETUDIANT);


        // 🔥 MENU + AUTH
        self::$twig->addGlobal('menus', Menu::get());
        self::$twig->addGlobal('isLogged', Auth::check());
        self::$twig->addGlobal('user', Auth::check() ? Auth::user() : null);

    }

    /**
     * Rend une vue avec un layout.
     *
     * @param string $view Nom de la vue relative à /views (ex: 'home/index')
     * @param array $data Données à passer à la vue (variables disponibles via extract)
     * @return void
     */
    public static function render(string $view, array $data = []): void
    {
        $data['title'] = 'Web4All';
        $data['description'] = 'Site Web pour trouver des entreprises et stages';
        $data['robots'] = 'index,follow';

        $twigPath = __DIR__ . '/../views/' . $view . '.twig';
        $phpPath  = __DIR__ . '/../views/' . $view . '.php';

        // 👉 PRIORITÉ à Twig
        if (file_exists($twigPath)) {
            echo self::$twig->render($view . '.twig', $data);
            return;
        }

        // 👉 fallback PHP
        if (file_exists($phpPath)) {
            extract($data, EXTR_SKIP);

            ob_start();
            require $phpPath;
            $content = ob_get_clean();

            require __DIR__ . '/../views/layout.php';
            return;
        }

        die("Vue introuvable : " . htmlspecialchars($view));
    }


    /**
     * Construit un tableau de pagination pour affichage.
     *
     * Exemple : [1, "...", 8, 9, 10, 11, 12, "...", 50]
     *
     * @param int $currentPage Page courante
     * @param int $totalPages  Nombre total de pages
     * @param int $window      Nombre de pages visibles autour de la page courante
     * @return array Tableau de pages / ellipses
     */
    public static function buildPagination(int $currentPage, int $totalPages, int $window = 2): array
    {
        $pages = [];

        if ($totalPages < 1) {
            return $pages;
        }

        $pages[] = 1;

        $start = max(2, $currentPage - $window);
        $end = min($totalPages - 1, $currentPage + $window);

        if ($start > 2) {
            $pages[] = '...';
        }

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        if ($end < $totalPages - 1) {
            $pages[] = '...';
        }

        if ($totalPages > 1) {
            $pages[] = $totalPages;
        }

        return $pages;
    }

    public static function Dumper($data): void
    {
        echo '<pre>' . print_r($data, true) . '</pre>';
    }


    public static function button(array $config, bool $toprint = true): ?string
    {
        $roleId = Auth::roleId();
        if (!Auth::can($config['permission'], $roleId)) {
            return null;
        }

        // URL
        $url = $config['url'] ?? '#';
        $fullUrl = ($url === '#') ? '#' : (CDN . PREFIX . $url);

        // classes
        $class = 'btn-icon ' . ($config['class'] ?? '');

        // attributs dynamiques
        $attributes = '';

        if (!empty($config['attributes']) && is_array($config['attributes'])) {
            foreach ($config['attributes'] as $key => $value) {
                $attributes .= sprintf(' %s="%s"', $key, htmlspecialchars($value));
            }
        }

        // title
        if (!empty($config['title'])) {
            $attributes .= ' title="'.htmlspecialchars($config['title']).'"';
            $attributes .= ' data-tooltip="'.htmlspecialchars($config['title']).'"';
        }

        // contenu
        $content = $config['icon'] ?? '';
        if ($toprint) {
            echo sprintf(
                '<a href="%s" class="%s"%s>%s</a>',
                $fullUrl,
                $class,
                $attributes,
                $content
            );
            return null;
        } else {
            return sprintf(
                '<a href="%s" class="%s"%s>%s</a>',
                $fullUrl,
                $class,
                $attributes,
                $content
            );
        }
    }
}
