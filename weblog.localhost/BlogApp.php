<?php
class BlogApp extends AppBase {
  protected $_signinAction = array('account', 'signin');

  // データベースへの接続
  protected function doDbConnection() {
    $this->_connectModel->connect('master', array(
      'string'   => 'mysql:dbname=weblog;host=localhost; charset=utf8',
      'user'     => 'root',
      'password' => 'root',
    ));
  }

  // ルートディレクトリへのパスを返す
  public function getRootDirectory() {
      return dirname(__FILE__);
  }

  // ルーティング定義を返す
  protected function getRouteDefinition() {
    return array(
      // AccountControllerクラス関連のルーティング定義
      '/account'
          => array('controller' => 'account',
                   'action'     => 'index'),
      '/account/:action'
          => array('controller' => 'account'),
      '/follow'
          => array('controller' => 'account',
                   'action'     => 'follow'),
      // BlogControllerクラス関連のルーティング情報
      '/'
          => array('controller' => 'blog',
                   'action'     => 'index'),
      '/status/post'
          => array('controller' => 'blog',
                   'action'     => 'post'),
      '/user/:user_name'
          => array('controller' => 'blog',
                   'action'     => 'user'),
      '/user/:user_name/status/:id'
          => array('controller' => 'blog',
                   'action'     => 'specific'),
      );
  }
}
