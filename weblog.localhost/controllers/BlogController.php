<?php
class BlogController extends Controller {
  protected $_authentication = array('index', 'post');
  const POST = 'status/post';
  const FOLLOW = 'account/follow';

  public function indexAction(){
    $user = $this->_session->get('user');
    $dat  = $this->_connect_model
                 ->get('Status')
                 ->getUserData($user['id']);

    $index_view = $this->render(array(
      'statuses' => $dat,
      'message'  => '',
      '_token'   => $this->getToken(self::POST),
    ));
    return $index_view;
  }
 
   public function postAction(){
    if (!$this->_request->isPost()) {
        $this->httpNotFound();
    }

    $token = $this->_request
                  ->getPost('_token');
    if (!$this->checkToken(self::POST, $token)) {
        return $this->redirect('/');
    }

    $message = $this->_request
                    ->getPost('message');

    $errors = array();

    if (!strlen($message)) {
        $errors[] = '投稿記事を入力してください';
    } else if (mb_strlen($message) > 200) {
        $errors[] = '投稿記事は最大200文字までです。';
    }

    if (count($errors) === 0) {
        $user = $this->_session->get('user');
        $this ->_connect_model
              ->get('Status')
              ->insert($user['id'], $message);

        return $this->redirect('/');
    }

    $user = $this->_session->get('user');
    $dat = $this ->_connect_model
                 ->get('Status')
                 ->getUserData($user['id']);

    $result = $this->render(array(
        'errors'   => $errors,
        'message'  => $message,
        'statuses' => $dat,
        '_token'   => $this->getToken(self::POST),
    ), 'index');
    return $result;
  }
  
   public function userAction($par){
    $user = $this->_connect_model
                 ->get('User')
                 ->getUserRecord($par['user_name']);
    if (!$user) {
      $this->httpNotFound();
    }

    $dat = $this->_connect_model
                ->get('Status')
                ->getPostedMessage($user['id']);
    $state = null;
    
    if ($this->_session->isAuthenticated()){
      $loginUser = $this->_session->get('user');
      if ($loginUser['id'] !== $user['id']) {
        $state = $this->_connect_model
                      ->get('Following')
                      ->isFollowedUser(
                        $loginUser['id'],
                        $user['id']);
      }
    }
    $user_view = $this->render(array(
      'user'        => $user,
      'statuses'    => $dat,
      'followstate' => $state,
      '_token'      => $this->getToken(self::FOLLOW),
    ));
    return $user_view;
}

  public function specificAction($par){
    $dat = $this->_connect_model
                ->get('Status')
                ->getSpecificMessage(
                  $par['id'],
                  $par['user_name']);

    if (!$dat) {
        $this->httpNotFound();
    }

    $specific_view = $this->render(
                       array('status' => $dat));
    return $specific_view;
  }
 
}
