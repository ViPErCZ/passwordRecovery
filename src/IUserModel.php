<?php
/**
 * Created by PhpStorm.
 * User: viper
 * Date: 17.1.16
 * Time: 8:12
 */

namespace Sandbox\PasswordRecovery;

/**
 * Interface IUserModel
 * @package Sandbox\PasswordRecovery
 */
interface IUserModel {
	public function isUserValid($email);
	public function isTokenValid($token, $expirationTime);
	public function saveToken($email, $token);
	public function resetPassword($token, $newPassword);
}