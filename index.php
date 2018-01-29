<?php

  require_once(__DIR__ . '/conf/config.php');

  try {
    $session = Session::instance();
    $pdo     = Db::connect('figment', config('database'));
    $route   = getRoute($_SERVER['REQUEST_URI']);
    if (($route === null) || !property_exists($route, 'controller')) {
      if ($session->username == "") {
        $response = HtmlResponse::instance();
        $response->redirect(config('baseUri') . '/user/login');
        exit(0);
      }
      $controller = Controller::create('feed');
    }
    else {
      $controller = Controller::create($route->controller, $route);
    }
    $controller->run();
    exit(0);
  }
  catch (Exception $e) {
    $response = HtmlResponse::instance();
    $response->error(500);
    exit(1);
  }

?>
