<?php

  require_once(__DIR__ . '/config.php');

  function getRoute($uri)
  {
    $routes = config('routes');
    $result = null;
    foreach ($routes as $route) {
      if (preg_match($route->pattern, $uri, $matches) === 1) {
        $result = $route;
        break;
      }
    }
    return $result;
  } // getRoute

?>
