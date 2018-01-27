<?php

  require_once(__DIR__ . '/config.php');

  function getRoute($uri)
  {
    $routes = config('routes');
    $result = null;
    foreach ($routes as $route) {
      if (preg_match($route->pattern, $uri, $matches) === 1) {
        $result = (object) array(
                    'controller' => $route->controller,
                    'method'     => (count($matches) > 0) ? $matches[1] : null
                  );
        break;
      }
    }
    return $result;
  } // getRoute

?>
