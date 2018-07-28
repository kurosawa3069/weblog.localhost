<?php
class Session{
  protected static $_session_flag = false;
  protected static $_generated_flag = false;

  // コンストラクター
  public function __construct(){
    if (!self::$_session_flag) {
      session_start();
      self::$_session_flag = true;
    }
  }

  // ***set()メソッド***
  // セッションに値を設定
  public function set($key, $value){
    $_SESSION[$key] = $value;
  }

  // ***get()メソッド***
  // $_SESSIONから値を取得
  public function get($key, $par = null){
    if (isset($_SESSION[$key])) {
      return $_SESSION[$key];
    }
    return $par;
  }

  // ***generateSession()メソッド***
  // セッションIDを生成する
  public function generateSession($del = true){
    if (!self::$_generated_flag) {
        session_regenerate_id($del);

        self::$_generated_flag = true;
    }
  }

  // ***setAuthenticateStaus()メソッド***
  // サインインの状態を登録する
  public function setAuthenticateStaus($flag){
    $this->set('_authenticated', (bool)$flag);
    $this->generateSession();
  }

  // ***isAuthenticated()メソッド***
  // 認証済みか判定する
  public function isAuthenticated(){
    return $this->get('_authenticated', false);
  }
  
  public function remove($name) {
  unset($_SESSION[$name]);
  }

  // ***clear()メソッド***
  // $_SESSIONを空にする
  public function clear(){
    $_SESSION = array();
  }
}
