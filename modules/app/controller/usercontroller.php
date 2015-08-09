<?php

namespace App\Controller;

use App\Etc\Controller as Controller;
use THCFrame\Request\RequestMethods;
use THCFrame\Security\PasswordManager;
use THCFrame\Events\Events as Event;
use THCFrame\Core\Rand;

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
     * App module login
     */
    public function login()
    {
        $view = $this->getActionView();
        
        $canonical = 'http://' . $this->getServerHost() . '/prihlasit';

        $this->getLayoutView()
                ->setTitle('Sokol - Přihlásit se')
                ->set('canonical', $canonical);

        if (RequestMethods::post('submitLogin')) {
            
            $email = RequestMethods::post('email');
            $password = RequestMethods::post('password');
            $error = false;

            if (empty($email)) {
                $view->set('account_error', $this->lang('LOGIN_EMAIL_ERROR'));
                $error = true;
            }

            if (empty($password)) {
                $view->set('account_error', $this->lang('LOGIN_PASS_ERROR'));
                $error = true;
            }

            if (!$error) {
                try {
                    $this->getSecurity()->authenticate($email, $password);
                    $daysToExpiration = $this->getSecurity()->getUser()->getDaysToPassExpiration();
                    
                    if($daysToExpiration !== false){
                        if($daysToExpiration < 14 && $daysToExpiration > 1){
                            $view->infoMessage($this->lang('PASS_EXPIRATION', array($daysToExpiration)));
                        }elseif($daysToExpiration < 5 && $daysToExpiration > 1){
                            $view->warningMessage($this->lang('PASS_EXPIRATION', array($daysToExpiration)));
                        }elseif($daysToExpiration >= 1){
                            $view->errorMessage($this->lang('PASS_EXPIRATION', array($daysToExpiration)));
                        }
                    }
                    
                    self::redirect('/');
                } catch (\THCFrame\Security\Exception\UserBlocked $ex) {
                    $view->set('account_error', $this->lang('ACCOUNT_LOCKED'));
                    Event::fire('app.log', array('fail', sprintf('Account locked for %s', $email)));
                } catch (\THCFrame\Security\Exception\UserInactive $ex) {
                    $view->set('account_error', $this->lang('ACCOUNT_INACTIVE'));
                    Event::fire('app.log', array('fail', sprintf('Account inactive for %s', $email)));
                } catch (\THCFrame\Security\Exception\UserExpired $ex) {
                    $view->set('account_error', $this->lang('ACCOUNT_EXPIRED'));
                    Event::fire('app.log', array('fail', sprintf('Account expired for %s', $email)));
                } catch (\Exception $e) {
                    Event::fire('app.log', array('fail', 'Exception: ' . $e->getMessage()));
                    
                    if (ENV == 'dev') {
                        $view->set('account_error', $e->getMessage());
                    } else {
                        $view->set('account_error', $this->lang('LOGIN_COMMON_ERROR'));
                    }
                }
            }
        }
    }

    /**
     * App module logout
     */
    public function logout()
    {
        $this->_willRenderActionView = false;
        $this->_willRenderLayoutView = false;

        $this->getSecurity()->logout();
        self::redirect('/');
    }

    /**
     * Registration. Create only members without access into administration
     */
    public function registration()
    {
        $view = $this->getActionView();
        $user = null;

        $canonical = 'http://' . $this->getServerHost() . '/registrace';

        $view->set('user', $user);

        $this->getLayoutView()
                ->set('metatitle', 'TJ Sokol - Registrace')
                ->set('canonical', $canonical);

        if (RequestMethods::post('register')) {
            if ($this->_checkCSRFToken() !== true &&
                    $this->_checkMutliSubmissionProtectionToken() !== true) {
                self::redirect('/');
            }
            $errors = array();

            if (RequestMethods::post('password') !== RequestMethods::post('password2')) {
                $errors['password2'] = array($this->lang('PASS_DOESNT_MATCH'));
            }

            $email = \App\Model\UserModel::first(
                            array('email = ?' => RequestMethods::post('email')), array('email')
            );

            if ($email) {
                $errors['email'] = array($this->lang('EMAIL_IS_TAKEN'));
            }

            if (PasswordManager::strength(RequestMethods::post('password')) <= 0.5) {
                $errors['password'] = array($this->lang('PASS_WEAK'));
            }

            $salt = PasswordManager::createSalt();
            $hash = PasswordManager::hashPassword(RequestMethods::post('password'), $salt);
            $verifyEmail = $this->getConfig()->registration_verif_email;

            if ($verifyEmail) {
                $active = false;
            } else {
                $active = true;
            }

            $actToken = Rand::randStr(50);
            for ($i = 1; $i <= 75; $i+=1) {
                if ($this->_checkEmailActToken($actToken)) {
                    break;
                } else {
                    $actToken = Rand::randStr(50);
                }

                if ($i == 75) {
                    $errors['email'] = array($this->lang('UNKNOW_ERROR') . ' Zkuste registraci opakovat později');
                    break;
                }
            }

            $user = new \App\Model\UserModel(array(
                'firstname' => RequestMethods::post('firstname'),
                'lastname' => RequestMethods::post('lastname'),
                'email' => RequestMethods::post('email'),
                'phoneNumber' => RequestMethods::post('phone'),
                'password' => $hash,
                'salt' => $salt,
                'role' => 'role_member',
                'active' => $active,
                'emailActivationToken' => $actToken
            ));

            if (empty($errors) && $user->validate()) {
                $uid = $user->save();

                if ($verifyEmail) {
                    $emailTemplate = \Admin\Model\EmailTemplateModel::first(array('urlkey = ?' => 'account-activation'));
                    $emailBody = str_replace('{TOKEN}', $actToken, $emailTemplate->getBody());

                    if ($this->_sendEmail($emailBody, 'TJ Sokol - Registrace', $user->getEmail(), 'registrace@hastrman.cz')) {
                        Event::fire('app.log', array('success', 'User Id with email activation: ' . $uid));
                        $view->successMessage($this->lang('REGISTRATION_EMAIL_SUCCESS'));
                    } else {
                        Event::fire('app.log', array('fail', 'Email not send for User Id: ' . $uid));
                        $user->delete();
                        $view->errorMessage($this->lang('REGISTRATION_EMAIL_FAIL'));
                        self::redirect('/');
                    }
                } else {
                    Event::fire('app.log', array('success', 'User Id: ' . $uid));
                    $view->successMessage($this->lang('REGISTRATION_SUCCESS'));
                }

                self::redirect('/');
            } else {
                $view->set('errors', $errors + $user->getErrors())
                        ->set('user', $user);
            }
        }
    }

    /**
     * Edit user currently logged in
     * 
     * @before _secured, _member
     */
    public function profile()
    {
        $view = $this->getActionView();
        $errors = array();

        $canonical = 'http://' . $this->getServerHost() . '/profil';

        $user = \App\Model\UserModel::first(array('id = ?' => $this->getUser()->getId()));

        $this->getLayoutView()
                ->set('metatile', 'TJ Sokol - Můj profil')
                ->set('canonical', $canonical);
        $view->set('user', $user);

        if (RequestMethods::post('editProfile')) {
            if ($this->_checkCSRFToken() !== true) {
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
            if (!empty($oldPassword)) {
                $newPass = RequestMethods::post('password');
                
                try{
                    $user = $user->changePassword($oldPassword, $newPass);
                } catch (\THCFrame\Security\Exception\WrongPassword $ex) {
                    $errors['oldpass'] = array($this->lang('PASS_ORIGINAL_NOT_CORRECT'));
                }  catch (\THCFrame\Security\Exception\WeakPassword $ex){
                    $errors['password'] = array($this->lang('PASS_WEAK'));
                }
            }

            $user->firstname = RequestMethods::post('firstname');
            $user->lastname = RequestMethods::post('lastname');
            $user->email = RequestMethods::post('email');
            $user->phoneNumber = RequestMethods::post('phone');

            if (empty($errors) && $user->validate()) {
                $user->update();
                $this->getSecurity()->setUser($user);

                $view->successMessage($this->lang('UPDATE_SUCCESS'));
                self::redirect('/muj-profil');
            } else {
                $view->set('errors', $errors + $user->getErrors());
            }
        }
    }

    /**
     * Activate account via activation link send by email
     * 
     * @param string    $key    activation token
     */
    public function activateAccount($key)
    {
        $view = $this->getActionView();

        $user = \App\Model\UserModel::first(array('active = ?' => false, 'emailActivationToken = ?' => $key));

        if (null === $user) {
            $view->warningMessage($this->lang('NOT_FOUND'));
            self::redirect('/');
        }

        if ($user->activateAccount()) {
            Event::fire('app.log', array('success', 'User Id: ' . $user->getId()));
            $view->successMessage($this->lang('ACCOUNT_ACTIVATED'));
            self::redirect('/');
        } else {
            Event::fire('app.log', array('fail', 'User Id: ' . $user->getId(),
                'Errors: ' . json_encode($user->getErrors())));
            $view->warningMessage($this->lang('COMMON_FAIL'));
            self::redirect('/');
        }
    }

}
