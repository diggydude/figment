<?php

  require_once(__DIR__ . '/conf/config.php');

  try {
    $session       = Session::instance();
    $route         = getRoute($_SERVER['REQUEST_URI']);
    $pdo           = Db::connect('figment', config('database'));
    $response      = HtmlResponse::instance();
    $baseUri       = config('baseUri');
    $response->js  = $baseUri . "/client/js/jquery.js";
    $response->js  = $baseUri . "/client/js/figment.js";
    $response->css = $baseUri . "/client/css/figment.css"; 
    if (($route === null) || !property_exists($route, 'controller')) {
      if ($session->username == "") {
        $response->redirect(config('baseUri') . '/user/login');
        exit(0);
      }
      $controller = Controller::create('feed');
      call_user_func(array($controller, 'index'));
      $response->send();
      exit(0);
    }
    if (property_exists($route, 'method')) {
      $method = $route->method;
      unset($route->method);
    }
    else {
      $method = null;
    }
    $controller = $route->controller;
    unset($route->controller);
    $params     = $route;
    $controller = Controller::create($controller, $params);
    if (($method !== null) && method_exists($controller, $method)) {
      call_user_func(array($controller, $method));
    }
    else {
      call_user_func(array($controller, 'index'));
    }
    $response->send();
    exit(0);
  }
  catch (Exception $e) {
    $response->error(500);
    exit(1);
  }

?>
