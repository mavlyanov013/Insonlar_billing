<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

/**
 * Created by PhpStorm.
 * Date: 11/28/17
 * Time: 6:33 PM
 */

namespace frontend\components;


use common\models\Auth;
use common\models\User;
use Yii;
use yii\authclient\ClientInterface;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class AuthHandler extends BaseObject
{
    /** @var  ClientInterface */
    public $client;

    public function init()
    {
        if (empty($this->client)) {
            throw new InvalidConfigException('Property "$client" must be set.');
        }
    }

    public function handle()
    {
        $attributes = $this->client->getUserAttributes();
        $email      = ArrayHelper::getValue($attributes, 'email');
        $id         = ArrayHelper::getValue($attributes, 'id');
        $login      = ArrayHelper::getValue($attributes, 'login');
        $fullname   = ArrayHelper::getValue($attributes, 'name');
        $avatar_url = Yii::$app->view->getImageUrl('user-icon.png');

        if ($this->client->getName() === 'twitter') {
            $login      = ArrayHelper::getValue($attributes, 'screen_name');
            $avatar_url = ArrayHelper::getValue($attributes, 'profile_image_url');
            $avatar_url = str_replace('_normal', '', $avatar_url);
        }

        if ($this->client->getName() === 'facebook') {
            $login      = ArrayHelper::getValue($attributes, 'id');
            $avatar     = ArrayHelper::getValue($attributes, 'picture');
            $avatar_url = ArrayHelper::getValue($avatar['data'], 'url');
        }

        /** @var Auth $auth */
        $auth = Auth::findOne([
                                  'source'    => $this->client->getId(),
                                  'source_id' => $id,
                              ]);

        if ($auth) {
            if ($auth->user == null) {
                $auth->delete();
                $auth = null;
            }
        }
        if (Yii::$app->user->isGuest) {
            if ($auth) { // login
                /** @var User $user */
                $user = $auth->user;
                $user->updateAttributes([
                                            'login'      => $login,
                                            'fullname'   => $fullname,
                                            'avatar_url' => $avatar_url,
                                            'email'      => !empty($email) ? $email : $user->email,
                                        ]);
                $user->saveImageFromSocial();
                Yii::$app->user->login($user, Yii::$app->params['user.loginDuration']);
                Yii::$app->getSession()->addFlash('success',
                                                  __("Siz {client} orqali kirdingiz.", [
                                                      'client' => $this->client->getTitle(),
                                                  ])
                );
            } else { // signup
                if ($email !== null && User::find()->where(['email' => $email])->exists()) {
                    $user = User::findByEmail($email);

                    if ($this->client->getName() === 'twitter') {
                        $user->twitter = $login;
                    }

                    if ($this->client->getName() === 'facebook') {
                        $user->facebook = $login;
                    }

                    if ($this->client->getName() === 'google') {
                        $user->google = $login;
                    }
                    $user->avatar_url = $avatar_url;
                    $user->login      = $login;
                    if ($user->save()) {
                        $auth = new Auth([
                                             '_user'        => $user->id,
                                             'source'       => $this->client->getId(),
                                             'source_id'    => $id,
                                             'source_login' => $login,
                                         ]);
                        if ($auth->save()) {
                            $user->saveImageFromSocial();
                            Yii::$app->user->login($user, Yii::$app->params['user.loginDuration']);
                            Yii::$app->getSession()->addFlash('success',
                                                              __('Siz {client} orqali kirdingiz.', [
                                                                  'client' => $this->client->getTitle(),
                                                              ])
                            );
                        } else {
                            Yii::$app->getSession()->addFlash('error',
                                                              __('Unable to save {client} account: {errors}', [
                                                                  'client' => $this->client->getTitle(),
                                                                  'errors' => Json::encode($auth->getErrors()),
                                                              ])
                            );
                        }
                    } else {
                        Yii::$app->getSession()->addFlash('error',
                                                          __('Unable to save {client} user: {errors}', [
                                                              'client' => $this->client->getTitle(),
                                                              'errors' => Json::encode($user->getErrors(), JSON_UNESCAPED_UNICODE),
                                                          ])
                        );
                    }
                } elseif ($login !== null && User::find()->where(['login' => $login])->exists()) {
                    Yii::$app->session->addFlash('error', __("User with the same login as in {client} account already exists but isn't linked to it. Login using login first to link it.", ['client' => $this->client->getTitle()]));
                } else {
                    $user = new User();

                    $user->login      = $login;
                    $user->fullname   = $fullname;
                    $user->email      = $email;
                    $user->status     = User::STATUS_ENABLE;
                    $user->avatar_url = $avatar_url;
                    $user->setPassword(\Yii::$app->security->generateRandomString(6));

                    if ($this->client->getName() === 'twitter') {
                        $user->twitter = $login;
                    }

                    if ($this->client->getName() === 'facebook') {
                        $user->facebook = $login;
                    }

                    if ($this->client->getName() === 'google') {
                        $user->google = $login;
                    }

                    $user->generatePasswordResetToken();

                    if ($user->save()) {
                        $auth = new Auth([
                                             '_user'        => $user->id,
                                             'source'       => $this->client->getId(),
                                             'source_id'    => $id,
                                             'source_login' => $login,
                                         ]);
                        if ($auth->save()) {
                            $user->saveImageFromSocial();
                            Yii::$app->user->login($user, Yii::$app->params['user.loginDuration']);
                            Yii::$app->getSession()->addFlash('success',
                                                              __('Siz {client} orqali kirdingiz.', [
                                                                  'client' => $this->client->getTitle(),
                                                              ])
                            );
                        } else {
                            Yii::$app->getSession()->addFlash('error',
                                                              __('Unable to save {client} account: {errors}', [
                                                                  'client' => $this->client->getTitle(),
                                                                  'errors' => Json::encode($auth->getErrors()),
                                                              ])
                            );
                        }
                    } else {
                        Yii::$app->getSession()->addFlash('error',
                                                          __('Unable to save {client} user: {errors}', [
                                                              'client' => $this->client->getTitle(),
                                                              'errors' => Json::encode($user->getErrors(), JSON_UNESCAPED_UNICODE),
                                                          ])
                        );
                    }
                }
            }
        } else { // user already logged in
            if (!$auth) { // add auth provider
                $auth = new Auth([
                                     '_user'        => Yii::$app->user->id,
                                     'source'       => $this->client->getId(),
                                     'source_id'    => $id,
                                     'source_login' => $login,
                                 ]);
                if ($auth->save()) {
                    /** @var User $user */
                    $user = $auth->user;
                    Yii::$app->getSession()->addFlash('success',
                                                      __('Linked {client} account.', ['client' => $this->client->getTitle()])
                    );
                } else {
                    Yii::$app->getSession()->addFlash('error',
                                                      __('Unable to link {client} account: {errors}', [
                                                          'client' => $this->client->getTitle(),
                                                          'errors' => Json::encode($auth->getErrors()),
                                                      ])
                    );
                }
            } else { // there's existing auth
                if ($auth->_user == Yii::$app->user->id) {
                    if ($auth->delete()) {
                        $user                           = $auth->user;
                        $user->{$this->client->getId()} = null;
                        if ($user->save()) {
                            Yii::$app->getSession()->addFlash('success',
                                                              __('Social network {client} has been successfully disabled.', [
                                                                  'client' => $this->client->getTitle(),
                                                              ])
                            );
                        }
                    }
                } else {
                    Yii::$app->getSession()->addFlash('error',
                                                      __('Unable to link {client} account. There is another user using it.', ['client' => $this->client->getTitle()])
                    );
                }
            }
        }
    }

}