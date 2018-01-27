<?php

  require_once(__DIR__ . '/conf/config.php');

  $session = Session::instance();
  $route   = getRoute($_SERVER['REQUEST_URI']);

  if (!$route) {
    $response->error(404);
    exit(1);
  }

  $pdo           = Db::connect('figment', config('database'));
  $response      = HtmlResponse::instance();
  $baseUri       = config('baseUri');
  $response->js  = $baseUri . "/client/js/jquery.js";
  $response->js  = $baseUri . "/client/js/figment.js";
  $response->css = $baseUri . "/client/css/figment.css"; 

  try {
    $controller = Controller::create($route->controller);
    $method     = (property_exists($controller, $method)) ? $route->method : "index";
    call_user_func(array($controller, $method));
    $response->send();
  }
  catch (Exception $e) {
    $response->error(500);
    exit(1);
  }

?>