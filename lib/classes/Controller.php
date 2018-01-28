<?php

  class Controller
  {

    public static function create($type, $params = null)
    {
      $file = config('ctrlDir') . "/" . $type . ".php";
      if (!file_exists($file)) {
        throw new Exception(__METHOD__ . ' > Unknown controller: ' . $type);
      }
      require_once($file);
      $controller = new $type();
      foreach (get_object_vars($params) as $k => $v) {
        $controller->$k = $v;
      }
      return $controller;
    } // create

  } // Controller

?>
