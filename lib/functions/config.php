<?php

  function config($key, $val = null)
  {
    static $config = array();
    $ret = (array_key_exists($key, $config)) ? $config[$key] : null;
    if ($val !== null) {
      $config[$key] = $val;
    }
    return $ret;
  } // config

?>