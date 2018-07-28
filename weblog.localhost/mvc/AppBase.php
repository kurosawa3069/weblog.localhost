<?php
abstract class AppBase{
  // Requestクラスのインスタンスを保持するプロパティ
  protected $_request;
  // Responseクラスのインスタンスを保持するプロパティ
  protected $_response;
  // Sessionクラスのインスタンスを保持するプロパティ
  protected $_session;
  // ConnectModelクラスのインスタンスを保持するプロパティ
  protected $_connectModel;
  // Routerクラスのインスタンスを保持するプロパティ
  protected $_router;
  // サインイン時のコントローラーとアクションの組合せを保持するプロパティ
  protected $_signinAction = array();
  // エラー表示のオン／オフを保持するプロパティ
  protected $_displayErrors;
  // コントローラークラス名のベース部分
  const CONTROLLER = 'Controller';
  // viewsフォルダーのディレクトリ
  const VIEWDIR = '/views';
  // modelsフォルダーのディレクトリ
  const MODELSDIR = '/models';
  // ドキュメントルートのディレクトリ
  const WEBDIR = '/mvc_htdocs';
  // controllersフォルダーのディレクトリ
  const CONTROLLERSDIR = '/controllers';

  // ***コンストラクター__construct()***
  public function __construct($dspErr){
    $this->setDisplayErrors($dspErr);
    $this->initialize();
    $this->doDbConnection();
  }

  // ***initialize()メソッド***
  protected function initialize(){
    $this->_router       = new Router($this->getRouteDefinition());
    $this->_connectModel = new ConnectModel();
    $this->_request      = new Request();
    $this->_response     = new Response();
    $this->_session      = new Session();
  }

  // ***setDisplayErrors()メソッド
  protected function setDisplayErrors($dspErr){
    if ($dspErr) {
      $this->_displayErrors = true;
      ini_set('display_errors', 1);
      ini_set('error_reporting', E_ALL);
    } else {
      $this->_displayErrors = false;
      ini_set('display_errors', 0);
    }
  }

  // ***isDisplayErrors()メソッド***
  public function isDisplayErrors(){
    return $this->_displayErrors;
  }

  // ***run()メソッド***
  public function run(){
    try {
      $parameters = $this->_router
                            ->getRouteParams(
                                $this->_request->getPath());
      if ($parameters === false) {
        throw new FileNotFoundException(
          'NO ROUTE ' . $this->_request->getPath());
      }

      $controller = $parameters['controller'];
      $action     = $parameters['action'];
      $this->getContent($controller, $action, $parameters);
    } catch (FileNotFoundException $e) {
      $this->dispErrorPage($e);
    } catch (AuthorizedException $e) {
      list($controller, $action) = $this->_signinAction;
      $this->getContent($controller, $action);
    }
    $this->_response->send();
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
//$this->_request->getPath();
  string(15) "/account/signup"
*/

  // ***getContent()メソッド***
  public function getContent($controllerName,
                             $action,
                             $parameters = array()
  ){
    $controllerClass = ucfirst($controllerName) . self::CONTROLLER;
    $controller      = $this->getControllerObject($controllerClass);

    if ($controller === false) {
        throw new FileNotFoundException(
          $controllerClass . ' NOT FOUND.');
    }

    $content = $controller->dispatch($action, $parameters);
    $this->_response
              ->setContent($content);
  }

  // ***getControllerObject()メソッド***
  protected function getControllerObject($controllerClass){
      if (!class_exists($controllerClass)) {
          $controllerFile =
            $this->getControllerDirectory() . '/' . $controllerClass . '.php';
          if (!is_readable($controllerFile)) {
            return false;
          } else {
            require_once $controllerFile;
            if (!class_exists($controllerClass)) {
              return false;
            }
          }
      }
      $controller = new $controllerClass($this);
      return $controller;
  }

  // ***dispErrorPage()メソッド***
  protected function dispErrorPage($e){
    $this->_response
            ->setStatusCode(404, 'FILE NOT FOUND.');
    $errMessage = $this->isDisplayErrors() ? $e->getMessage() : 'FILE NOT FOUND.';
    $errMessage = htmlspecialchars($errMessage, ENT_QUOTES, 'UTF-8');
    $html = "
<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8' />
<title>HTTP 404 Error</title>
</head>
<body>
{$errMessage}
</body>
</html>
";
    $this->_response->setContent($html);
  }

  // ***getRouteDefinition()メソッド***
  abstract protected function getRouteDefinition();

  // ***doDbConnection()メソッド***
  protected function doDbConnection() {}

  // ***getRequestObject()メソッド***
  public function getRequestObject(){
      return $this->_request;
  }

  // ***getResponseObject()メソッド***
  public function getResponseObject(){
      return $this->_response;
  }

  // ***getSessionObject()メソッド***
  public function getSessionObject(){
      return $this->_session;
  }

  // ***getConnectModelObject()メソッド***
  public function getConnectModelObject(){
      return $this->_connectModel;
  }

  // ***getViewDirectory()メソッド***
  public function getViewDirectory(){
      return $this->getRootDirectory() . self::VIEWDIR;
  }

  // ***getModelDirectory()メソッド***
  public function getModelDirectory(){
      return $this->getRootDirectory() . self::MODELSDIR;
  }

  // ***getDocDirectory()メソッド***
  public function getDocDirectory(){
      return $this->getRootDirectory() . self::WEBDIR;
  }

  // ***抽象メソッドgetRootDirectory()***
  abstract public function getRootDirectory();

  // ***getControllerDirectory()メソッド***
	public function getControllerDirectory(){
	  return $this->getRootDirectory() . self::CONTROLLERSDIR;
	}
}
