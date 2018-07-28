<?php
class ConnectModel {
  // PDOクラスのインスタンスを配列で保持するプロパティ
  protected $_dbConnections = array();
  // モデルクラスのインスタンスを保持するプロパティ
  protected $_modelList = array();
  // 接続名を保持するプロパティ
  protected $_connectName;
  // モデルクラス名を格納する定数
  const MODEL = 'Model';

  // ***connect()メソッド**
  public function connect($name, $connection_strings) {
    try{
      $cnt = new PDO(
        $connection_strings['string'],      // 接続文字列
        $connection_strings['user'],        // ユーザー名
        $connection_strings['password']     // パスワード
      );
    }catch(PDOException $e){
      exit("データベースの接続に失敗しました。 : {$e->getMessage()}");
    }
    $cnt->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->_dbConnections[$name] = $cnt;
    $this->_connectName = $name;
  }

  // ***getConnection()メソッド***
  public function getConnection($name = null) {
    // 接続名が渡されなかったときの処理
    if (is_null($name)) {
        // _dbConnectionsプロパティに格納されている先頭の要素の値を返す
        return current($this->_dbConnections);
    }
    // _dbConnectionsプロパティに格納されているPDOオブジェクトを返す
    return $this->_dbConnections[$name];
  }

  // ***getModelConnection()メソッド***
  public function getModelConnection(){
    if (isset($this->_connectName)) {
        // $model_nameキーの値を接続名として$nameに格納
        $name = $this->_connectName;
        // 接続名$nameのPDOオブジェクトを取得
        $cnt = $this->getConnection($name);
    } else {
        // _connectNameプロパティに値がなければ、先頭のPDOオブジェクトを取得
        $cnt = $this->getConnection();
    }
    // PDOオブジェクトを返す
    return $cnt;
  }

  // ***get()メソッド***
  public function get($model_name) {
      // _modelListプロパティの$model_nameキーに
      // データモデル名が存在しなければPDOオブジェクトを取得
      if (!isset($this->_modelList[$model_name])) {
          // データモデル名に'Model'を連結し、これをクラス名として代入
          $mdl_class = $model_name . self::MODEL;
          // PDOオブジェクトを取得
          $cnt = $this->getModelConnection();
          // $mdl_classに格納されたモデルクラスをインスタンス化
          $obj = new $mdl_class($cnt);
          // _modelListプロパティに「データモデル名=>データモデルクラスのインスタンス」を格納
          $this->_modelList[$model_name] = $obj;
      }
      // 戻り値としてデータモデルクラスのインスタンスを返す
      $modelObj = $this->_modelList[$model_name];
      return $modelObj;
  }

   // ***デストラクター__destruct（）***
  public function __destruct() {
      foreach ($this->_modelList as $model) {
          unset($model);  // $modelを破棄
      }
      foreach ($this->_dbConnections as $cnt) {
          unset($cnt);    // $cntを破棄
      }
  }
}