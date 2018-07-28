<?php
class AccountController extends Controller {
  protected $_authentication = array('index', 'signout');
  const SIGNUP = 'account/signup';
  const SIGNIN = 'account/signin';
  const FOLLOW = 'account/follow';

  public function signupAction() {
    if ($this->_session->isAuthenticated()) {
      $this->redirect('/account');
    }
    $signup_view = $this->render(array(
        'user_name' => '',
        'password'  => '',
        '_token'    => $this->getToken(self::SIGNUP),
    ));
    return $signup_view;
  }

  public function registerAction() {
    if (!$this->_request->isPost()) {
        $this->httpNotFound();
    }

    if ($this->_session->isAuthenticated()) {
      $this->redirect('/account');
    }

    $token = $this->_request->getPost('_token');
    if (!$this->checkToken(self::SIGNUP,
                           $token)
    ){
      return $this->redirect('/' . self::SIGNUP);
    }

    $user_name = $this->_request
                      ->getPost('user_name');
    $password  = $this->_request
                      ->getPost('password');

    $errors = array();

    if (!strlen($user_name)) {
      $errors[] = 'ユーザーIDが入力されていません。';
    } else if (
      !preg_match('/^\w{3,20}$/',
      $user_name)
    ){
      $errors[] = 'ユーザーIDは半角英数字3文字以上20字以内にしてください。';
    } else if (!$this->_connect_model
                     ->get('User')
                     ->isOverlapUserName($user_name)
    ){
      $errors[] = '入力したユーザーIDは他のユーザーが使用しています。';
    }

    if (!strlen($password)) {
      $errors[] = 'パスワードが入力されていません。';
    } else if (8 > strlen($password)
               || strlen($password) > 30
    ){
      $errors[] = 'パスワードは8文字以上30字以内であることが必要です。';
    }

    if (count($errors) === 0) {
      $this->_connect_model
           ->get('User')
           ->insert($user_name, $password);
      $this->_session
           ->setAuthenticateStaus(true);

      $user = $this->_connect_model
                   ->get('User')
                   ->getUserRecord($user_name);
      $this->_session
           ->set('user', $user);

      return $this->redirect('/');
    }

    return $this->render(array(
        'user_name' => $user_name,
        'password'  => $password,
        'errors'    => $errors,
        '_token'    => $this->getToken(self::SIGNUP),
    ), 'signup');
  }

  public function indexAction() {
    $user = $this->_session->get('user');
    $followingUsers = $this->_connect_model
                           ->get('User')
                           ->getFollowingUser($user['id']);

    $index_view = $this->render(array(
      'user'           => $user,
      'followingUsers' => $followingUsers,
    ));
    return $index_view;
  }

  public function signinAction() {
    if ($this->_session->isAuthenticated()) {
        return $this->redirect('/account');
    }

    $signin_view = $this->render(array(
        'user_name' => '',
        'password'  => '',
        '_token'    => $this->getToken(self::SIGNIN),
    ));
    return $signin_view;
	}

  public function authenticateAction() {
     if (!$this->_request->isPost()) {
      $this->httpNotFound();
    }

   if ($this->_session->isAuthenticated()) {
      return $this->redirect('/account');
    }

    $token = $this->_request
                  ->getPost('_token');
    if (!$this->checkToken(self::SIGNIN,
                           $token)
    ){
      return $this->redirect('/' . self::SIGNIN);
    }

    $user_name = $this->_request
                      ->getPost('user_name');
    $password  = $this->_request
                      ->getPost('password');

    $errors = array();
    if (!strlen($user_name)) {
      $errors[] = 'ユーザーIDを入力してください。';
    }

    if (!strlen($password)) {
      $errors[] = 'パスワードを入力してください。';
    }

    if (count($errors) === 0) {
			$user = $this->_connect_model
                   ->get('User')
                   ->getUserRecord($user_name);

      if (!$user
          || (!password_verify($password, $user['password']))
			){
 	     	$errors[] = '認証エラーです。';
      } else {
        $this->_session
             ->setAuthenticateStaus(true);
        $this->_session
             ->set('user', $user);
        return $this->redirect('/');
      }
    }

    return $this->render(array(
        'user_name' => $user_name,
        'password'  => $password,
        'errors'    => $errors,
        '_token'    => $this->getToken(self::SIGNIN),
    ), 'signin');
  }

	public function signoutAction(){
		$this->_session
         ->clear();
		$this->_session
         ->setAuthenticateStaus(false);
		return $this->redirect('/' . self::SIGNIN);
	}

  public function followAction() {
    if (!$this->_request->isPost()) {
      $this->httpNotFound();
    }

    $follow_user_name = $this->_request
                             ->getPost('follow_user_name');
    if (!$follow_user_name){
      $this->httpNotFound();
    }

    $token = $this->_request->getPost('_token');

    if (
      !$this->checkToken(self::FOLLOW, $token)
    ){
      return $this->redirect('/user/' . $follow_user_name);
    }

    $follow_user = $this->_connect_model
                        ->get('User')
                        ->getUserRecord($follow_user_name);
    if (!$follow_user) {
        $this->httpNotFound();
    }

    $user = $this->_session
                 ->get('user');

    $followTblConnection = $this->_connect_model
                                ->get('Following');

    if ($user['id'] !== $follow_user['id']
        && !$followTblConnection->isFollowedUser(
          $user['id'], $follow_user['id'])
    ){
      $followTblConnection
        ->registerFollowUser($user['id'],
                             $follow_user['id']);
    }

    return $this->redirect('/account');
  }
}
