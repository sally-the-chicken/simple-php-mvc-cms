<?php

class Model_Users extends Base_Model
{
    protected $_id;
    protected $_login;
    protected $_password;
    protected $_email;
    protected $_display_name;
    protected $_activation_key;
    protected $_status;
    protected $_last_login_time;
    protected $_last_login_ip;
    protected $_created_date;
    protected $_modified_date;
    protected $_created_by;
    protected $_modified_by;

    protected $_is_authenticated = false;
    protected $_permissions;
    protected $_roles;

    protected $_salt = USER_PASSWORD_SALT;

    public function is_authenticated()
    {
        return $this->_is_authenticated;
    }

    public function can($access_right)
    {

        $access_right = strtolower($access_right);
        return isset($this->_permissions[$access_right]);

    }

    public function get_by_email($email) {
        $email = $this->_sanitize($email);
        return $this->_get_all(" user.`email` LIKE '{$email}'");
    }

    public function get_by_login($login) {
        $login = $this->_sanitize($login);
        return $this->_get_all(" user.`login` LIKE '{$login}'");
    }

    public function get_all_with_permissions()
    {

        $all_users = array();
        $select = $this->_select_sql();
        $mysqli = $this->_get_db_connection();
        $result = $mysqli->query($select);
        if ($result->num_rows) {
            while ($row = $result->fetch_assoc()) {
                $all_users[$row['id']] = $row;
            }
        }
        $mysqli->close();
        foreach ($all_users as $user_id => &$one) {
            $one['permissions'] = $this->_find_permissions($user_id);
            $one['roles'] = $this->_find_roles($user_id);
        }
        return $all_users;
    }

    public function authenticate($login, $password)
    {
        if (null !== $this->_fetch_by_login_password($login, $password) && $this->_status != 0) {
            $this->_is_authenticated = true;
            $this->_last_login_ip = $_SERVER['REMOTE_ADDR'];
            $this->_last_login_time = date('Y-m-d H:i:s');
            $this->save();
        }
        if (empty($this->_permissions)) {
            $this->_permissions = $this->_find_permissions();
        }
        if (empty($this->_roles)) {
            $this->_roles = $this->_find_roles();
        }
        return $this;
    }

    public function open_new_account($login, $email, $role_ids, $display_name){

        if (empty($login) || empty($email) || empty($role_ids) || empty($display_name)){
            throw new Exception("Required params empty for open new account.\n\n");
        }
        if (empty($_SESSION[SESS_USER_VAR])){
            throw new Exception('Session expired. Please log in again.');
        }
        $this->_login           = $login;
        $this->_email           = $email;
        $this->_activation_key  = sha1($login.$email.time());
        $this->_status          = 0;
        $this->_display_name    = $display_name;
        $this->_created_by      = $_SESSION[SESS_USER_VAR]->id;
        $this->save();
        if (empty($this->_id)){
            throw new Exception("Failed to create new account.\n\n");
        }
        
        // save roles
        $this->_add_user_accessrights($role_ids);
        
        return $this;
        
    }
    
    protected function _add_user_accessrights($access_rights){
    
        if (empty($access_rights)){
            throw new Exception("Cannot add user access right: accessrights is empty.\n\n");
        }
    
        if (!is_array($access_rights)){
            $access_rights = array($access_rights);
        }
        foreach ($access_rights as $access_right){
            if (!is_numeric($access_right)){
                throw new Exception("Cannot add user access right: accessright $access_right is not numeric.\n\n");
            }
        }
        
        $user_id = $this->_id;
    
        $mysqli = $this->_get_db_connection();
        foreach ($access_rights as $access_right){
            $delete = "DELETE FROM `acl_user_accessrights` ".
                    "WHERE `user_id` = '$user_id' ".
                    "AND `accessright_id` = '$access_right' ";
            $mysqli->query($delete);
            $insert = "INSERT INTO `acl_user_accessrights` (`user_id`, `accessright_id`, `created`) ".
                    "VALUES ('$user_id', '$access_right', now())";
            $mysqli->query($insert);
        }
        return $this;
    
    }

    protected function _password_hash($login, $password)
    {
        return sha1(strrev($password) . strtoupper($login) . $this->_salt);
    }

    protected function _fetch_by_login_password($login, $password)
    {
        if (empty($login) || empty($password)) {
            return false;
        }
        list($this->_login, $this->_password) = $this->_sanitize(array($login, $password));
        $password_hashed = $this->_password_hash($this->_login, $this->_password);

        $user = $this->_get_one(" `login` = '{$this->_login}' AND `password` = '{$password_hashed}' ");
        if (empty($user)) {
            return false;
        }
        $this->from_array($user);
        return true;
    }

    protected function _find_permissions($user_id = null)
    {

        $permissions = array();

        if (empty($user_id) && empty($this->_id)) {
            return $permissions;
        }

        // User permission
        $selects[] = "SELECT " .
            "r.`id`, " .
            "r.`name`, " .
            "r.`description` " .
            "FROM `acl_accessrights` AS ar " .
            "JOIN `acl_user_accessrights` AS uar ON uar.`accessright_id` = ar.`id` " .
            "JOIN `acl_resources` AS r ON r.`id` = ar.`resource_id` " .
            "WHERE uar.`user_id` = ?";

        // Role permission
        $selects[] = "SELECT " .
            "r.`id`, " .
            "r.`name`, " .
            "r.`description` " .
            "FROM `acl_accessrights` AS role " .
            "JOIN `acl_user_accessrights` AS uar ON uar.`accessright_id` = role.`id` " .
            "JOIN `acl_accessrights` AS permission ON permission.`role_id` = role.`id` " .
            "JOIN `acl_resources` AS r ON permission.`resource_id` = r.`id` " .
            "WHERE uar.`user_id` = ?";

        if (empty($user_id)) {
            $user_id = $this->_id;
        }
        $mysqli = $this->_get_db_connection();
        foreach ($selects as $select) {
            if (!$stmt = $mysqli->prepare($select)) {
                throw new Exception("Prepare statement failed.\nQuery: $select \n{$mysqli->error}\n\n");
            }
            $stmt->bind_param('i', $user_id);
            if (false === ($result = $stmt->execute())) {
                throw new Exception("Execute statement failed.\nQuery: $select \n{$mysqli->error}\n\n");
            }
            $stmt->store_result();
            if ($stmt->num_rows == 0) {
                continue;
            }
            $permission_id = $permission_name = $permission_desc = null;
            $stmt->bind_result($permission_id, $permission_name, $permission_desc);
            while ($stmt->fetch()) {
                $permission = array();
                $permission['id'] = $permission_id;
                $permission['name'] = strtolower($permission_name);
                $permission['description'] = $permission_desc;
                $permissions[strtolower($permission_name)] = $permission;
            }
        }
        $mysqli->close();
        return $permissions;
    }

    protected function _find_roles($user_id = null)
    {

        $roles = array();

        if (empty($user_id) && empty($this->_id)) {
            return $roles;
        }

        $select = "SELECT " .
            "role.`id`, " .
            "role.`role_name` " .
            "FROM `acl_accessrights` AS role " .
            "JOIN `acl_user_accessrights` AS uar ON uar.`accessright_id` = role.`id` " .
            "WHERE uar.`user_id` = ?";

        if (empty($user_id)) {
            $user_id = $this->_id;
        }

        $mysqli = $this->_get_db_connection();
        if (!$stmt = $mysqli->prepare($select)) {
            throw new Exception("Prepare statement failed.\nQuery: $select \n{$mysqli->error}\n\n");
        }
        $stmt->bind_param('i', $user_id);
        if (false === ($result = $stmt->execute())) {
            throw new Exception("Execute statement failed.\nQuery: $select \n{$mysqli->error}\n\n");
        }
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            return;
        }
        $role_id = $role_name = null;
        $stmt->bind_result($role_id, $role_name);
        while ($stmt->fetch()) {
            $role = array();
            $role['id'] = $role_id;
            $role['role_name'] = strtolower($role_name);

            $roles[$role_id] = $role;
        }
        $mysqli->close();
        return $roles;
    }

    protected function _insert()
    {

        $insert = "INSERT INTO `acl_users` (`login`,`password`,`email`,`display_name`,`activation_key`,`status`,`last_login_time`,`last_login_ip`,`created_date`,`created_by`) " .
            "VALUES (?,?,?,?,?,?,?,?,NOW(),?)";
        $mysqli = $this->_get_db_connection();
        if (!$stmt = $mysqli->prepare($insert)) {
            throw new Exception("Prepare statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        $id = $this->_id;
        $login = $this->_login;
        $password = $this->_password;
        $email = $this->_email;
        $display_name = $this->_display_name;
        $activation_key = $this->_activation_key;
        $status = $this->_status;
        $last_login_time = $this->_last_login_time;
        $last_login_ip = $this->_last_login_ip;
        $created_date = $this->_created_date;
        $modified_date = $this->_modified_date;
        $created_by = $this->_created_by;
        $modified_by = $this->_modified_by;

        $db_result = $stmt->bind_param('sssssissi', $login, $password, $email, $display_name, $activation_key, $status, $last_login_time, $last_login_ip, $created_by);
        if (false === $db_result) {
            throw new Exception("Bind statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        if (false === ($result = $stmt->execute())) {
            throw new Exception("Execute statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        $this->_id = $mysqli->insert_id;
        return $result;
    }

    public function from_array($user)
    {
        $this->_id = isset($user['id']) ? $user['id'] : null;
        $this->_login = isset($user['login']) ? $user['login'] : null;
        $this->_password = isset($user['password']) ? $user['password'] : null;
        $this->_email = isset($user['email']) ? $user['email'] : null;
        $this->_display_name = isset($user['display_name']) ? $user['display_name'] : null;
        $this->_activation_key = isset($user['activation_key']) ? $user['activation_key'] : null;
        $this->_status = isset($user['status']) ? $user['status'] : '0';
        $this->_last_login_time = isset($user['last_login_time']) ? $user['last_login_time'] : null;
        $this->_last_login_ip = isset($user['last_login_ip']) ? $user['last_login_ip'] : null;
        $this->_created_date = isset($user['created_date']) ? $user['created_date'] : null;
        $this->_modified_date = isset($user['modified_date']) ? $user['modified_date'] : date("Y-m-d H:i:s");
        $this->_created_by = isset($user['created_by']) ? $user['created_by'] : null;
        $this->_modified_by = isset($user['modified_by']) ? $user['modified_by'] : null;

        return $this;
    }

    public function read($id)
    {

        $select = $this->_select_sql(true);
        $select .= " WHERE `id` = ?";

        $mysqli = $this->_get_db_connection();
        if (!$stmt = $mysqli->prepare($select)) {
            throw new Exception("Prepare statement failed.\nQuery: $select \n{$mysqli->error}\n\n");
        }
        $stmt->bind_param('i', $id);
        if (false === ($result = $stmt->execute())) {
            throw new Exception("Execute statement failed.\nQuery: $select \n{$mysqli->error}\n\n");
        }
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            return false;
        }
        $user = array('id' => null, 'login' => null, 'password' => null, 'email' => null, 'display_name' => null, 'activation_key' => null, 'status' => null, 'last_login_time' => null, 'last_login_ip' => null, 'created_date' => null, 'modified_date' => null, 'created_by' => null, 'modified_by' => null);
        $stmt->bind_result($user['id'], $user['login'], $user['password'], $user['email'], $user['display_name'], $user['activation_key'], $user['status'], $user['last_login_time'], $user['last_login_ip'], $user['created_date'], $user['modified_date'], $user['created_by'], $user['modified_by']);
        $stmt->fetch();
        $stmt->free_result();
        $stmt->close();
        $mysqli->close();
        $this->_id = $user['id'];
        $this->_login = $user['login'];
        $this->_password = $user['password'];
        $this->_email = $user['email'];
        $this->_display_name = $user['display_name'];
        $this->_activation_key = $user['activation_key'];
        $this->_status = $user['status'];
        $this->_last_login_time = $user['last_login_time'];
        $this->_last_login_ip = $user['last_login_ip'];
        $this->_created_date = $user['created_date'];
        $this->_modified_date = $user['modified_date'];
        $this->_created_by = $user['created_by'];
        $this->_modified_by = $user['modified_by'];

        return true;
    }

    protected function _select_sql($single = false, $cnt_only = false)
    {

        $select = "SELECT ";
        if ($single) {
            $select .= "user.`id`, " .
                "user.`login`, " .
                "user.`password`, " .
                "user.`email`, " .
                "user.`display_name`, " .
                "user.`activation_key`, " .
                "user.`status`, " .
                "user.`last_login_time`, " .
                "user.`last_login_ip`, " .
                "user.`created_date`, " .
                "user.`modified_date`, " .
                "user.`created_by`, " .
                "user.`modified_by`" .

                "FROM acl_users AS user ";
            return $select;
        }
        if ($cnt_only) {
            $select .= " count(*) as cnt ";
        } else {
            $select .= $this->_getSelectComplete();
        }
        return $select;
    }

    protected function _getSelectComplete()
    {
        return
            "user.`id`, " .
            "user.`login`, " .
            "user.`password`, " .
            "user.`email`, " .
            "user.`display_name`, " .
            "user.`activation_key`, " .
            "user.`status`, " .
            "user.`last_login_time`, " .
            "user.`last_login_ip`, " .
            "user.`created_date`, " .
            "user.`modified_date`, " .
            "user.`created_by`, " .
            "user.`modified_by`" .
            "FROM acl_users AS user ";
    }

    protected function _update()
    {
        $update = "UPDATE `acl_users` SET " .
            "`login` = ?,`password` = ?,`email` = ?,`display_name` = ?,`activation_key` = ?,`status` = ?,`last_login_time` = ?,`last_login_ip` = ?,`modified_date` = NOW(),`modified_by` = ?" .
            " WHERE `id` = ?";

        $mysqli = $this->_get_db_connection();
        if (!$stmt = $mysqli->prepare($update)) {
            throw new Exception("Prepare statement failed.\nQuery: $update \n{$mysqli->error}\n\n");
        }
        $id = $this->_id;
        $login = $this->_login;
        $password = $this->_password;
        $email = $this->_email;
        $display_name = $this->_display_name;
        $activation_key = $this->_activation_key;
        $status = $this->_status;
        $last_login_time = $this->_last_login_time;
        $last_login_ip = $this->_last_login_ip;
        $created_date = $this->_created_date;
        $modified_date = $this->_modified_date;
        $created_by = $this->_created_by;
        $modified_by = $this->_modified_by;

        $stmt->bind_param('sssssissii', $login, $password, $email, $display_name, $activation_key, $status, $last_login_time, $last_login_ip, $modified_by, $id);
        if (false === ($result = $stmt->execute())) {
            throw new Exception("Execute statement failed.\nQuery: $update
{$mysqli->error}\n\n");
        }
        return $result;
    }

}
