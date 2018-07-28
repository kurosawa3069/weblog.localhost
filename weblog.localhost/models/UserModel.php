<?php
class UserModel extends ExecuteModel {

  // ***insert()メソッド***
  public function insert($user_name, $password) {
      $password = password_hash($password,
                                PASSWORD_DEFAULT);
      $now = new DateTime();
      $sql = "INSERT INTO user(user_name, password, time_stamp)
              VALUES(:user_name, :password, :time_stamp)";
      $stmt = $this->execute($sql, array(
          ':user_name'  => $user_name,
          ':password'   => $password,
          ':time_stamp' => $now->format('Y-m-d H:i:s'),
      ));
  }

  // ***getUserRecord()メソッド***
  public function getUserRecord($user_name) {
      $sql = "SELECT *
              FROM   user
              WHERE  user_name = :user_name";

      $userData = $this->getRecord(
                         $sql,
                         array(':user_name' => $user_name));
      return $userData;
  }

  // ***isOverlapUserName()メソッド***
  public function isOverlapUserName($user_name) {
      $sql = "SELECT COUNT(id) as count
              FROM   user
              WHERE  user_name = :user_name";

      $row = $this->getRecord(
                    $sql,
                    array(':user_name' => $user_name));
      if ($row['count'] === '0') {
          return true;
      }
      return false;
  }

  // ***getFollowingUser()メソッド***
  public function getFollowingUser($user_id) {
    $sql = "SELECT    u.*
            FROM      user u
            LEFT JOIN followingUser f ON f.following_id = u.id
            WHERE     f.user_id = :user_id";
    $follows = $this->getAllRecord(
                      $sql,
                      array(':user_id' => $user_id));
    return $follows;
  }
}
