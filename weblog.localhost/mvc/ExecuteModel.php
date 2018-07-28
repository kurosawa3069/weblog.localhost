<?php
// 抽象クラス
abstract class ExecuteModel{
    // PDOオブジェクトを保持するプロパティ
    protected $_pdo;

    // ***コンストラクター***
    public function __construct($pdo){
        $this->setPdo($pdo);
    }

    // ***setPdo()メソッド***
    public function setPdo($pdo){
        $this->_pdo = $pdo;
    }

    // ***execute()メソッド***
    public function execute($sql, $parameter = array()){
      // プリペアドステートメントを生成
      $stmt = $this->_pdo
                   ->prepare(
                       $sql,
                       array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      // プリペアドステートメントを実行
      $stmt->execute($parameter);
      // 戻り値としてPDOStatementオブジェクトを返す
      return $stmt;
    }

    // ***getAllRecord()メソッド***
    public function getAllRecord($sql,
                                 $parameter = array()
    ){
      $all_rec = $this->execute($sql, $parameter)
                      ->fetchAll(PDO::FETCH_ASSOC);
      return $all_rec;
    }

    // ***getRecord()メソッド***
    public function getRecord($sql, $parameter = array()){
      $rec = $this->execute($sql, $parameter)
                  ->fetch(PDO::FETCH_ASSOC);
      return $rec;
    }
}