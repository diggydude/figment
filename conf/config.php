<?php

  error_reporting(E_ALL ^ E_NOTICE);
  set_time_limit(120);

  require_once(realpath(__DIR__ . '/../lib/functions/config.php'));

  config('siteName',   'Figment');
  config('sysopEmail', 'Sysop <sysop@example.com>');
  config('baseUri',    'http://example.com');
  config('timezone',   'America/Chicago');
  config('cookie',     (object) array(
                         'path' => '/'
                        )
  );
  config('database',   (object) array(
                         'host'     => 'localhost',
                         'schema'   => 'dbName',
                         'username' => 'username',
                         'password' => 'password',
                         'options'  => array(
                                         PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                                         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                                         PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                                       )
                       )
  );
  config('cache',      (object) array(
                         'session' => (object) array(
                                        'id'  => 'session',
                                        'ttl' => 18000
                                      ),
                         'query'   => (object) array(
                                        'id'  => 'query',
                                        'ttl' => 60
                                      ),
                         'html'    => (object) array(
                                        'id'  => 'html',
                                        'ttl' => 60
                                      ),
                         'servers' => array(
                                        array('127.0.0.1', 11311)
                                      )
                       )
  );
  config('routes',     array(
                         (object) array(
                           'pattern'    => '/^\/feed\/$/',
                           'controller' => 'feed'
                         ),
                         (object) array(
                           'pattern'    => '/^\/profile\/([a-zA-Z0-9]*)$/',
                           'controller' => 'profile'
                         ),
                         (object) array(
                           'pattern'    => '/^\/hashtag\/([a-zA-Z0-9]*)$/',
                           'controller' => 'feed'
                         ),
                         (object) array(
                           'pattern'    => '/^\/api\/([\w]*)/',
                           'controller' => 'api'
                         )
                       )
  );
  config('baseDir',    realpath(__DIR__ . '/..'));
  config('dataDir',    config('baseDir') . '/data/');
  config('tmpDir',     config('baseDir') . '/tmp/');
  config('logDir',     config('baseDir') . '/logs/');
  config('libDir',     config('baseDir') . '/lib/');
  config('ctrlDir',    config('baseDir') . '/controllers/');
  config('tplDir',     config('baseDir') . '/templates/');
  config('uploadDir',  config('baseDir') . '/client/images/uploads/');
  config('movedList',  config('dataDir') . 'moved.json');

  date_default_timezone_set(config('timezone'));

  require_once(config('libDir') . 'functions/getRequestHeaders.php');
  require_once(config('libDir') . 'classes/Db.php');
  require_once(config('libDir') . 'classes/Session.php');
  require_once(config('libDir') . 'classes/HtmlResponse.php');

?>
