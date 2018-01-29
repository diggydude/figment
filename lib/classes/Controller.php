<?php

  class Controller
  {

    protected

      $_method,
      $_response,
      $_template,
      $_currUser;

    public static function create($type, $params = null)
    {
      $file = config('ctrlDir') . "/" . $type . ".php";
      if (!file_exists($file)) {
        throw new Exception(__METHOD__ . ' > Unknown controller type: ' . $type);
      }
      require_once($file);
      $className  = $type . "Controller";
      $controller = new $className($params);
      return $controller;
    } // create

    protected function __construct($params = null)
    {
      if ($params === null) {
        $params = (object) array();
      }
      $baseUri         = config('baseUri');
      $session         = Session::instance();
      $this->_method   = (property_exists($params, 'method') && method_exists($this, $params->method))
                       ? $params->method   : "index";
      $this->_template = (property_exists($params, 'template')) ? $params->template : "";
      $this->_currUser = new User($session->username);
      switch ($params->responseType) {
        case "html":
        default:
          $this->_response      = new HtmlResponse();
          $this->_response->js  = $baseUri . "/client/js/jquery.js";
          $this->_response->js  = $baseUri . "/client/js/figment.js";
          $this->_response->css = $baseUri . "/client/css/figment.css";
          break;
        case "json":
          $this->_response = new JsonResponse();
          break;
      }
    } // __construct

    public function run()
    {
      call_user_func(array($this, $this->_method));
      $this->_response->send();
    } // run

  } // Controller

?>
