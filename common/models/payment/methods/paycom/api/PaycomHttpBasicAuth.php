<?php
namespace common\models\payment\methods\paycom\api;

use yii\filters\auth\HttpBasicAuth;
use yii\web\UnauthorizedHttpException;

/**
 * Created by PhpStorm.
 * User: complex
 * Date: 1/8/16
 * Time: 4:57 PM
 */
class PaycomHttpBasicAuth extends HttpBasicAuth
{

    public function authenticate($user, $request, $response)
    {
        $username = $request->getAuthUser();
        $password = $request->getAuthPassword();

        if ($this->auth) {
            if ($username != null && $password != null) {
                $identity = call_user_func($this->auth, $username, $password);
                if ($identity !== null) {
                    $user->switchIdentity($identity);
                    return $identity;
                }
            }
        } elseif ($username !== null) {
            $identity = $user->loginByAccessToken($username, get_class($this));
            if ($identity !== null) {
                return $identity;
            }
        }

        $this->handleFailure($response);
    }

    public function handleFailure($response)
    {
        throw new UnauthorizedHttpException('You are requesting with an invalid credentials.');
    }
}