<?php
abstract class Controller {
  protected $_application;
  protected $_controller;
  protected $_action;
  protected $_request;
  protected $_response;
  protected $_session;
  protected $_connect_model;
  protected $_authentication = array();
  const PROTOCOL = 'http://';
  const ACTION = 'Action';

  // ***コンストラクター***
  public function __construct($application){
    // $application → object(BlogApp)#2 (7) {
    $this->_controller     = strtolower(substr(get_class($this), 0, -10));
    // $this → object(AccountController)#9 (8) {
    // strtolower(小文字)、substr(文字列返す)、get_class(クラス名返す)
    $this->_application    = $application;
    $this->_request        = $application->getRequestObject();
    $this->_response       = $application->getResponseObject();
    $this->_session        = $application->getSessionObject();
    $this->_connect_model  = $application->getConnectModelObject();
  }

  // ***dispatch()メソッド***
  // signupAction→$this->$action_method→HTML
  public function dispatch($action, $params = array()) {
    $this->_action = $action;
    $action_method = $action . self::ACTION;  //signupAction

    if (!method_exists($this, $action_method)) {
        $this->httpNotFound();
    }
    if ($this->isAuthentication($action)
        && !$this->_session->isAuthenticated()
    ){
      throw new AuthorizedException();
    }
    $content = $this->$action_method($params);
    //AccountController::signupAction()
    return $content;
  }
/*
//$parameters
array(4) {
  ["controller"]=>
  string(7) "account"
  [0]=>
  string(15) "/account/signup"
  ["action"]=>
  string(6) "signup"
  [1]=>
  string(6) "signup"
}
*/
  // ***httpNotFound()メソッド***
  protected function httpNotFound() {
    throw new FileNotFoundException('FILE NOT FOUND '
        . $this->_controller . '/' . $this->_action);
  }

  // ***needsAuthentication()メソッド***
  protected function isAuthentication($action) {
    if ($this->_authentication === true
        || (is_array($this->_authentication)
        && in_array($action, $this->_authentication))
    ){
      return true;
    }
    return false;
  }

  // ***render()メソッド***
  protected function render(
    $param = array(), $viewFile = null, $template = null
  ){
    $info = array(
        'request'  => $this->_request,
        'base_url' => $this->_request->getBaseUrl(),
        'session'  => $this->_session,
    );
    $view = new View($this->_application
                          ->getViewDirectory(),
                     $info);

    if (is_null($viewFile)) {
        $viewFile = $this->_action;
    }

    if (is_null($template)) {
        $template = 'template';
    }

    $path = $this->_controller . '/' .$viewFile;
    $contents = $view->render($path,
                              $param,
                              $template);
    return $contents;
  }


  // ***redirect()メソッド***
  protected function redirect($url) {
    $host     = $this->_request->getHostName();
    $base_url = $this->_request->getBaseUrl();
    $url      = self::PROTOCOL . $host . $base_url . $url;
    $this->_response
         ->setStatusCode(302, 'Found');
    $this->_response
         ->setHeader('Location', $url);
  }

  // ***getToken()メソッド***
  protected function getToken($form) {
    $key      = 'token/' . $form;
    $tokens   = $this->_session
                     ->get($key, array());
    if (count($tokens) >= 10) {
        array_shift($tokens);
    }
    $password = session_id() . $form;
    $token    = password_hash($password,
                              PASSWORD_DEFAULT);
    $tokens[] = $token;

    $this->_session->set($key, $tokens);

    return $token;
  }

  // ***checkToken()メソッド***
  protected function checkToken($form, $token) {
    $key    = 'token/' . $form;
    $tokens = $this->_session->get($key, array());

    if (false !== ($present = array_search($token,
                                           $tokens,
                                           true))
    ){
      unset($tokens[$present]);
      $this->_session
           ->set($key, $tokens);

      return true;
    }
    return false;
  }
}
