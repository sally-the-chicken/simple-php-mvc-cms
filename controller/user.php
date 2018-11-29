<?php

class Controller_User extends Controller_App
{
    public function init()
    {
        parent::init();
        $this->_home_location = APP_DIR . "user/";
    }

    public function action_index()
    {
        $this->_check_access('users.view');
        $model_accessrights = new Model_AccessRights();
        $roles_with_permissions = $model_accessrights->get_all_roles_with_permissions();
        $model_resources = new Model_Resources();
        $resources = $model_resources->get_all();

        $form_inputs = Util_Form::get_form_inputs('user');
        $inputs = $form_inputs['inputs'];
        $inputs['login']['required'] = true;
        $inputs['display_name']['required'] = true;
        $inputs['email']['required'] = true;
        $inputs['roles']['required'] = true;

        foreach ($roles_with_permissions as $roles_with_permission) {
            $inputs['roles']['options'][$roles_with_permission['id']] = array(
                'name' => $roles_with_permission['role_name'],
                'attributes' => array('resource_names=\'' . json_encode(array_keys($roles_with_permission['resources'])) . '\''),
            );
        }

        $model_users = new Model_Users();
        $users = $model_users->get_all_with_permissions();

        foreach ($users as &$user) {
            $user['id'] = '<a href="' . APP_DIR . 'user/index/id/' . $user['id'] . '">' . $user['id'] . '</a>';
            $user['permissions'] = implode('<br />', array_keys($user['permissions']));
            $user['roles'] = implode('<br />', array_map(
                function ($role) {return $role['role_name'];},
                $user['roles']));
        }

        if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
            $model_users->read($_REQUEST['id']);
            $inputs['login']['value'] = $model_users->login;
            $inputs['display_name']['value'] = $model_users->display_name;
            $inputs['email']['value'] = $model_users->email;
        }

        $this->_view
            ->set_tag_body_contents('class="user"')
            ->set_var('table_cols', $this->_get_user_list_table_cols())
            ->set_var('users', $users)
            ->set_var('user', $model_users)
            ->set_var('resources', $resources)
            ->set_var('inputs', $inputs)
            ->set_var('msg', $this->_msg)
            ->set_var('errmsg', $this->_errmsg)
            ->add_js('foot', array(
                'src' => "https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"))
            ->add_js('foot', array(
                'src' => "https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"))
            ->add_js('foot', array(
                'src' => "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"))
            ->add_js('foot', array(
                'src' => DIR_ASSETS_JS . 'user.js'))
            ->add_css("https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css")
            ->add_css("https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css")
            ->add_css("https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css");

    }

    public function action_add()
    {

        if (!isset($_POST)) {
            Util_Header::redirect($this->_home_location);
        }

        $result = array(
            'success' => false,
            'errmsgs' => null,
        );

        foreach ($_POST as &$param) {
            if (is_array($param)) {
                continue;
            }
            $param = trim($param);
        }

        // check access
        if (!$_SESSION[SESS_USER_VAR]->can('users.edit')) {
            $result['errmsgs'][] = 'You are not authorized to add user.';
        }

        $form_inputs = Util_Form::get_form_inputs('user');
        $inputs = $form_inputs['inputs'];
        foreach (array('login', 'display_name', 'email', 'roles') as $field) {
            if (empty($_POST[$field])) {
                $result['errmsgs'][] = 'Field [' . $inputs[$field]['th'] . '] is missing.';
            }
        }

        // TODO: put these in model
        $login = trim($_POST['login']);
        if (!preg_match("/^[A-Za-z0-9_\.]+$/", $login, $output_array)) {
            $result['errmsgs'][] = 'Login name should be alpha-numeric, underscore, period.';
        }

        $email = trim($_POST['email']);
        $email_pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

        if (!preg_match($email_pattern, $email)) {
            $result['success'] = false;
            $result['errmsgs'][] = 'Email format is incorrect.';
        }

        // check roles
        $model_accessRights = new Model_AccessRights();
        $roles_with_permissions = $model_accessRights->get_all_roles_with_permissions();
        $legal_roles = array_keys($roles_with_permissions);
        $role_ids = array();
        foreach ($_POST['roles'] as $role_id) {
            if (in_array($role_id, $legal_roles)) {
                $role_ids[] = $role_id;
            }
        }
        if (empty($role_ids)) {
            $result['errmsgs'][] = 'Please select role.';
        }

        // check if login or email has already exists
        $model_users = new Model_Users();
        if ($model_users->get_by_email($email)) {
            $result['errmsgs'][] = 'Email [' . $email . '] already exists.';
        }
        if ($model_users->get_by_login($login)) {
            $result['errmsgs'][] = 'Login [' . $login . '] already exists.';
        }
        $display_name = $_POST['display_name'];
        if (empty($result['errmsgs'])) {
            try {
                $model_users = new Model_Users();
                $model_users->open_new_account($login, $email, $role_ids, $display_name);
                if ($model_users->id) {
                    $this->_send_activation_email(
                        $model_users->activation_key,
                        $model_users->login,
                        $model_users->email);
                    $result['success'] = true;
                } else {
                    $result['success'] = false;
                    $result['errmsgs'][] = 'Unable to add new account.';
                }
            } catch (Exception $e) {
                $result['success'] = false;
                $result['errmsgs'][] = $e->getMessage();
            }
        }

        if ($result['success']) {
            $this->_msg = 'Email successfully sent to ' . $login . '(' . $email . ')!';
        } else {
            $this->_errmsg = 'Error: <br />' . implode('<br />', $result['errmsgs']);
        }
        $this->set_msgs();
        Util_Header::redirect($this->_home_location);

    }

    protected function _get_user_list_table_cols()
    {
        return array(
            'id' => 'ID',
            'login' => 'Login',
            'email' => 'Email',
            'display_name' => 'Display Name',
            'status' => 'Last Login',
            'roles' => 'Roles',
            'permissions' => 'Permissions',
        );
    }

    protected function _send_activation_email($activation_key, $login, $email)
    {
        $subject = USER_ACTIVATION_EMAIL_SUBJECT . $login;
        $activation_key_link = 'https://' . $_SERVER['SERVER_NAME'] . APP_DIR . "user/activate/key/$activation_key/login/$login/";

        $message = $this->_get_adduser_email_content(array(
            'activation_key_link' => $activation_key_link));
        Util_Email::send( array(
            'to' => $email, 
            'subject' => $subject, 
            'message' => $message, 
            'from' => USER_ACTIVATION_EMAIL_FROM, 
            'cc' => USER_ACTIVATION_EMAIL_CC
        ));

    }

    protected function _get_adduser_email_content(array $contents)
    {

        extract($contents);
        ob_start();
        include WEBROOT . 'view/users/activate_email.html';
        $returned = ob_get_contents();
        ob_end_clean();

        return $returned;

    }

}
