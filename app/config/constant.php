<?php


define('ENV', 'dev'); // dev | prod
define('APP_NAME', 'CESI1STAGE');
if ($_ENV['APP_ENV'] == 'dev') {
    define('BASE_URL', '');
    define('CDN', 'https://web4all.local');
    define('STATIQUE', 'https://static.web4all.local');
    define('PREFIX', '');
} else {
    define('BASE_URL', '/web4all');
    define('CDN', 'https://services.hashment.com');
    define('STATIQUE', 'https://services.hashment.com');
    define('PREFIX', '/web4all');
}
define('ITEM_PER_PAGES', 3);
define('GUEST', 4);
define('ETUDIANT', 3);
define('POSTULE', [ 3 ]);
define('PILOTE', [ 2 ]);

define('UPLOAD_DIR', __DIR__ . '/../../uploads');
