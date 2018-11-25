<?php

class Controller_Login extends Controller_App
{
    public function init()
    {
        parent::init();
        $this->_home_location = APP_DIR . "login/";
    }

    public function action_index()
    {
        $this->_view
            ->set_tag_body_contents('class="text-center login"')
            ->set_var('msg', $this->_msg)
            ->set_var('errmsg', $this->_errmsg);
    }

    public function action_signin()
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Util_Header::redirect($this->_home_location);
        }

        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        $errmsgs = array();
        if (empty($username)) {
            $errmsgs[] = 'Username is empty.';
        }
        if (empty($password)) {
            $errmsgs[] = 'Password is empty.';
        }

        if (!empty($errmsgs)) {
            $this->_errmsg = 'Error: <br />' . implode('<br />', $errmsgs);
            $this->set_msgs();
            Util_Header::redirect($this->_home_location);
        }

        $user_model = new Model_Users();
        $user_model->authenticate($username, $password);
        $_SESSION[SESS_USER_VAR] = $user_model;
        if ($user_model->is_authenticated()) {
            Util_Header::redirect(APP_DIR . "home/");
        }
        $this->_errmsg = 'Error: <br />Invalid username or password.';
        $this->set_msgs();
        Util_Header::redirect($this->_home_location);
    }

    public function action_logout()
    {
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(), '', 0, '/');
        Util_Header::redirect($this->_home_location);
    }

}
