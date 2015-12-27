<?php

namespace App\Controller;

use App\Etc\Controller as Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Security\PasswordManager;
use THCFrame\Events\Events as Event;
use THCFrame\Core\Rand;
use THCFrame\Registry\Registry;
use THCFrame\Core\StringMethods;

/**
 * 
 */
class UserController extends Controller
{

    private function _checkEmailActToken($token)
    {
        $exists = \App\Model\UserModel::first(array('emailActivationToken = ?' => $token));

        if ($exists === null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * App module login.
     */
    public function login()
    {
        $this->_disableView();

        if ($this->getSecurity()->getCsrf()->verifyRequest() !== true) {
            $this->ajaxResponse($this->lang('ACCESS_DENIED'), true, 403);
        }

        $email = RequestMethods::post('email');
        $password = RequestMethods::post('password');
        $error = false;

        try {
            $this->getSecurity()->authenticate($email, $password);
            $daysToExpiration = $this->getSecurity()->getUser()->getDaysToPassExpiration();

            if ($daysToExpiration !== false) {
                if ($daysToExpiration < 14 && $daysToExpiration > 1) {
                    $error = $this->lang('PASS_EXPIRATION', array($daysToExpiration));
                } elseif ($daysToExpiration < 5 && $daysToExpiration > 1) {
                    $error = $this->lang('PASS_EXPIRATION', array($daysToExpiration));
                } elseif ($daysToExpiration <= 1) {
                    $error = $this->lang('PASS_EXPIRATION_TOMORROW');
                }
            }

            //attendance

            
            //response
            $this->ajaxResponse($this->lang('COMMON_SUCCESS'), false, 200, array('loggedIn' => true));
        } catch (\Exception $e) {
            Event::fire('app.log', array('fail', 'Login Exception: ' . $e->getMessage()));
            $this->ajaxResponse($this->lang('LOGIN_COMMON_ERROR'), true, 401, array('loggedIn' => false));
        }
    }

    /**
     * App module logout.
     */
    public function logout()
    {
        $this->_disableView();

        if ($this->getSecurity()->getCsrf()->verifyRequest() !== true) {
            $this->ajaxResponse($this->lang('ACCESS_DENIED'), true, 403);
        }

        $this->getSecurity()->logout();

        $this->ajaxResponse($this->lang('COMMON_SUCCESS'), false, 200, array('loggedOut' => true));
    }

    /**
     * Edit user currently logged in.
     * 
     * @before _secured, _member
     */
    public function profile()
    {
        $view = $this->getActionView();
        $errors = array();

        $canonical = 'http://' . $this->getServerHost() . '/profil';

        $user = \App\Model\UserModel::first(array('id = ?' => $this->getUser()->getId()));
        $myActions = \App\Model\AttendanceModel::fetchActionsByUserId($this->getUser()->getId(), true);

        if (!empty($myActions)) {
            foreach ($myActions as &$action) {
                $action->latestComments = \App\Model\CommentModel::fetchByTypeAndCreated(
                                \App\Model\CommentModel::RESOURCE_ACTION, $action->getId(), Registry::get('session')->get('userLastLogin')
                );
                unset($action);
            }
        }

        $this->getLayoutView()
                ->set('metatile', 'Hastrman - Můj profil')
                ->set('canonical', $canonical);
        $view->set('user', $user)
                ->set('myactions', $myActions);

        if (RequestMethods::post('editProfile')) {
            if ($this->getSecurity()->getCsrf()->verifyRequest() !== true) {
                self::redirect('/muj-profil');
            }

            if (RequestMethods::post('password') !== RequestMethods::post('password2')) {
                $errors['password2'] = array($this->lang('PASS_DOESNT_MATCH'));
            }

            if (RequestMethods::post('email') != $user->email) {
                $email = \App\Model\UserModel::first(
                                array('email = ?' => RequestMethods::post('email', $user->email)), array('email')
                );

                if ($email) {
                    $errors['email'] = array($this->lang('EMAIL_IS_TAKEN'));
                }
            }

            $oldPassword = RequestMethods::post('oldpass');
            $newPassword = RequestMethods::post('password');

            if (!empty($oldPassword) && !empty($newPassword)) {
                try {
                    $user = $user->changePassword($oldPassword, $newPassword);
                } catch (\THCFrame\Security\Exception\WrongPassword $ex) {
                    $errors['oldpass'] = array($this->lang('PASS_ORIGINAL_NOT_CORRECT'));
                } catch (\THCFrame\Security\Exception\WeakPassword $ex) {
                    $errors['password'] = array($this->lang('PASS_WEAK'));
                } catch (\THCFrame\Security\Exception\PasswordInHistory $ex) {
                    $errors['password'] = array($this->lang('PASS_IN_HISTORY'));
                }
            } elseif (empty($oldPassword) && !empty($newPassword)) {
                $errors['oldpass'] = array($this->lang('PASS_ORIGINAL_NOT_CORRECT'));
            }

            $user->firstname = RequestMethods::post('firstname');
            $user->lastname = RequestMethods::post('lastname');
            $user->email = RequestMethods::post('email');
            $user->getNewActionNotification = RequestMethods::post('actionNotification');
            $user->team = RequestMethods::post('team');

            if (empty($errors) && $user->validate()) {
                $user->save();
                $this->getSecurity()->setUser($user);

                $view->successMessage($this->lang('UPDATE_SUCCESS'));
                self::redirect('/muj-profil');
            } else {
                Event::fire('app.log', array('fail', 'User id: ' . $user->getId(),
                    'Errors: ' . json_encode($errors + $user->getErrors()),));
                $view->set('errors', $errors + $user->getErrors());
            }
        }
    }

}
