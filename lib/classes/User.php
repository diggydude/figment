<?php

  class User
  {

    protected

      $userId,
      $username,
      $password,
      $question,
      $answer,
      $joined,
      $isAdministrator,
      $isModerator,
      $displayName,
      $biography,
      $birthday,
      $gender,
      $location,
      $avatar,
      $banner,
      $colorScheme,
      $pinnedMessage;

    public static function register($params)
    {
    } // register

    public static function login($username, $password, $verify)
    {
    } // login

    public static function logout($username)
    {
    } // logout

    public static function load($userId)
    {
    } // load

    public static function block($blockerId, $blockedId)
    {
    } // block

    public static function ban($moderatorId, $bannedId, $reason)
    {
    } // ban

    public function __construct($userId = 0)
    {
    } // __construct

    public function save()
    {
    } // save

    public function saveProfile()
    {
    } // saveProifle

  } // User

?>
