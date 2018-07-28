<?php
class Response{
  protected $_content;
  protected $_statusCode = 200;
  protected $_statusMsg = 'OK';
  protected $_headers = array();
  const HTTP = 'HTTP/1.1 ';

  // コンテンツを設定
  public function setContent($content){
    $this->_content = $content;
  }

  // ステータスコードを設定
  public function setStatusCode($code, $msg = ''){
    $this->_statusCode = $code;
    $this->_statusMsg = $msg;
  }

  // レスポンスヘッダーを設定
  public function setHeader($name, $value){
    $this->_headers[$name] = $value;
  }

  // レスポンスを送信
  public function send(){
    header(self::HTTP . $this->_statusCode . ' ' . $this->_statusMsg);
    foreach ($this->_headers as $name => $value) {
      header($name . ': ' . $value);
    }
    print $this->_content;
  }
}
