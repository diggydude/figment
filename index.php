<?php

  require_once(__DIR__ . '/conf/config.php');

  $pdo      = Db::connect('figment', config('database'));
  $session  = Session::instance();
  $response = HtmlResponse::instance();
  $route    = getRoute($_SERVER['REQUEST_URI']);

  if (!$_route) {
    $response->error(404);
    exit(1);
  }

  try {
    $controller = $route->controller;
    $method     = $route->method;

    require_once(config('ctrlDir') . $controller . '.php');

    $controller = new $controller();
    $controller->$method();
  }
  catch (Exception $e) {
    $response->error(500);
    exit(1);
  }

?>
