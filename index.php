<?php

  require_once(__DIR__ . '/conf/config.php');

  try {
    $session  = Session::instance();
    $route    = getRoute($_SERVER['REQUEST_URI']);
    $pdo      = Db::connect('figment', config('database'));
    $response = HtmlResponse::instance();
    if (!$route) {
      $response->error(404);
      exit(1);
    }
    $baseUri       = config('baseUri');
    $response->js  = $baseUri . "/client/js/jquery.js";
    $response->js  = $baseUri . "/client/js/figment.js";
    $response->css = $baseUri . "/client/css/figment.css"; 
    $controller    = Controller::create($route->controller);
    if ($route->method === null) {
      call_user_func(array($controller, 'index'));
    }
    else if (method_exists($controller, $route->method)) {
      call_user_func(array($controller, $route->method));
    }
    else {
      call_user_func_array(array($controller, 'index'), array($route->method));
    }
    $response->send();
    exit(0);
  }
  catch (Exception $e) {
    $response->error(500);
    exit(1);
  }

?>
