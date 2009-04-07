<?php
  define('HTTP_SERVER', 'http://127.0.0.1');
  define('HTTPS_SERVER', 'http://127.0.0.1');
  define('ENABLE_SSL', false);
  define('HTTP_COOKIE_DOMAIN', '127.0.0.1');
  define('HTTPS_COOKIE_DOMAIN', '127.0.0.1');
  define('HTTP_COOKIE_PATH', '/gameStore/');
  define('HTTPS_COOKIE_PATH', '/gameStore/');
  define('DIR_WS_HTTP_CATALOG', '/gameStore/');
  define('DIR_WS_HTTPS_CATALOG', '/gameStore/');
  define('DIR_WS_IMAGES', 'images/');

  define('DIR_WS_DOWNLOAD_PUBLIC', 'pub/');
  define('DIR_FS_CATALOG', '/export/gameStore/');
  define('DIR_FS_WORK', '/export/gameStore/includes/work/');
  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
  define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');
  define('DIR_FS_BACKUP', '/export/gameStore/admin/backups/');

  define('DB_SERVER', 'localhost');
  define('DB_SERVER_USERNAME', 'root');
  define('DB_SERVER_PASSWORD', '');
  define('DB_DATABASE', 'gameStore');
  define('DB_DATABASE_CLASS', 'mysqli');
  define('DB_TABLE_PREFIX', 'gs_');
  define('USE_PCONNECT', 'false');
  define('STORE_SESSIONS', 'database');
?>