<?php

  require_once(__DIR__ . '/config.php');

  function getRoute($uri)
  {
    $path   = trim(parse_url($uri, PHP_URL_PATH), "/");
    $routes = config('routes');
    $result = null;
    foreach ($routes as $route) {
      if (preg_match($route->pattern, $path, $matches) === 1) {
        $result = array('controller' => $matches[0]);
        for ($i = 0; $i < count($route->components); $i++) {
          $index = $i + 1;
          if (isset($matches[$index])) {
            $result[$route->components[$i]] = $matches[$index];
          }
        }
        $result = (object) $result;
        break;
      }
    }
    return $result;
  } // getRoute

?>
