<?php

class Model_AccessRights extends Base_Model
{

    protected $_id;
    protected $_role_id;
    protected $_resource_id;
    protected $_role_name;
    protected $_created_date;
    protected $_modified_date;
    protected $_created_by;
    protected $_modified_by;

    public function get_all_roles_with_permissions()
    {

        $all_roles = array();
        $accessrights = $this->use_id_as_key(false)->_get_all("accessright.`role_id` IS NULL");
        if (empty($accessrights)) {
            return $all_roles;
        }
        foreach ($accessrights as $k=>$accessright) {
            if (!isset($all_roles[$accessright['id']])) {
                $all_roles[$accessright['id']] = array(
                    'id' => $accessright['id'], 
                    'role_name' => $accessright['role_name'], 
                    'resources' => array());
            }
            $all_roles[$accessright['id']]['resources'][$accessright['resource_name']] = $accessright['resource_description'];
        }
        return $all_roles;
    }

    protected function _insert()
    {

        $insert = "INSERT INTO `acl_accessrights` (`role_id`,`resource_id`,`role_name`,`created_date`,`created_by`) " .
            "VALUES (?,?,?,NOW(),?)";
        $mysqli = $this->_get_db_connection();
        if (!$stmt = $mysqli->prepare($insert)) {
            throw new Exception("Prepare statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        $id = $this->_id;
        $role_id = $this->_role_id;
        $resource_id = $this->_resource_id;
        $role_name = $this->_role_name;
        $created_date = $this->_created_date;
        $modified_date = $this->_modified_date;
        $created_by = $this->_created_by;
        $modified_by = $this->_modified_by;

        $db_result = $stmt->bind_param('iisi', $role_id, $resource_id, $role_name, $created_by);
        if (false === $db_result) {
            throw new Exception("Bind statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        if (false === ($result = $stmt->execute())) {
            throw new Exception("Execute statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        $this->_id = $mysqli->insert_id;
        return $result;
    }

    public function from_array($accessright)
    {
        $this->_id = isset($accessright['id']) ? $accessright['id'] : null;
        $this->_role_id = isset($accessright['role_id']) ? $accessright['role_id'] : null;
        $this->_resource_id = isset($accessright['resource_id']) ? $accessright['resource_id'] : null;
        $this->_role_name = isset($accessright['role_name']) ? $accessright['role_name'] : null;
        $this->_created_date = isset($accessright['created_date']) ? $accessright['created_date'] : null;
        $this->_modified_date = isset($accessright['modified_date']) ? $accessright['modified_date'] : date("Y-m-d H:i:s");
        $this->_created_by = isset($accessright['created_by']) ? $accessright['created_by'] : null;
        $this->_modified_by = isset($accessright['modified_by']) ? $accessright['modified_by'] : null;

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
        $accessright = array('id' => null, 'role_id' => null, 'resource_id' => null, 'role_name' => null, 'created_date' => null, 'modified_date' => null, 'created_by' => null, 'modified_by' => null);
        $stmt->bind_result($accessright['id'], $accessright['role_id'], $accessright['resource_id'], $accessright['role_name'], $accessright['created_date'], $accessright['modified_date'], $accessright['created_by'], $accessright['modified_by']);
        $stmt->fetch();
        $stmt->free_result();
        $stmt->close();
        $mysqli->close();
        $this->_id = $accessright['id'];
        $this->_role_id = $accessright['role_id'];
        $this->_resource_id = $accessright['resource_id'];
        $this->_role_name = $accessright['role_name'];
        $this->_created_date = $accessright['created_date'];
        $this->_modified_date = $accessright['modified_date'];
        $this->_created_by = $accessright['created_by'];
        $this->_modified_by = $accessright['modified_by'];

        return true;
    }

    protected function _select_sql($single = false, $cnt_only = false)
    {

        $select = "SELECT ";
        if ($single) {
            $select .= "accessright.`id`, " .
                "accessright.`role_id`, " .
                "accessright.`resource_id`, " .
                "accessright.`role_name`, " .
                "accessright.`created_date`, " .
                "accessright.`modified_date`, " .
                "accessright.`created_by`, " .
                "accessright.`modified_by`" .
                "FROM acl_accessrights AS accessright ";
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
            "accessright.`id`, " .
            "accessright.`role_id`, " .
            "accessright.`resource_id`, " .
            "accessright.`role_name`, " .
            "accessright.`created_date`, " .
            "accessright.`modified_date`, " .
            "accessright.`created_by`, " .
            "accessright.`modified_by`, " .
            "permission.`id` as `permission_id`, " .
            "permission.`role_id` as `permission_role_id`, " .
            "permission.`resource_id` as `permission_resource_id`, " .
            "permission.`role_name` as `permission_role_name`, " .
            "permission.`created_date` as `permission_created_date`, " .
            "permission.`modified_date` as `permission_modified_date`, " .
            "permission.`created_by` as `permission_created_by`, " .
            "permission.`modified_by` as `permission_modified_by`, " .
            "resource.`id` as `resource_id`, " .
            "resource.`name` as `resource_name`, " .
            "resource.`description` as `resource_description`, " .
            "resource.`created_date` as `resource_created_date`, " .
            "resource.`modified_date` as `resource_modified_date`, " .
            "resource.`created_by` as `resource_created_by`, " .
            "resource.`modified_by` as `resource_modified_by` " .
            "FROM acl_accessrights AS accessright " .
            "JOIN acl_accessrights AS permission ON permission.`role_id` = accessright.`id` " .
            "JOIN acl_resources AS resource ON resource.`id` = permission.`resource_id` ";

    }

    protected function _update()
    {

        $update = "UPDATE `acl_accessrights` SET " .
            "`role_id` = ?,`resource_id` = ?,`role_name` = ?,`modified_date` = NOW(),`modified_by` = ?" .
            " WHERE `id` = ?";

        $mysqli = $this->_get_db_connection();
        if (!$stmt = $mysqli->prepare($update)) {
            throw new Exception("Prepare statement failed.\nQuery: $update \n{$mysqli->error}\n\n");
        }
        $id = $this->_id;
        $role_id = $this->_role_id;
        $resource_id = $this->_resource_id;
        $role_name = $this->_role_name;
        $created_date = $this->_created_date;
        $modified_date = $this->_modified_date;
        $created_by = $this->_created_by;
        $modified_by = $this->_modified_by;

        $stmt->bind_param('iisii', $role_id, $resource_id, $role_name, $modified_by, $id);
        if (false === ($result = $stmt->execute())) {
            throw new Exception("Execute statement failed.\nQuery: $update
{$mysqli->error}\n\n");
        }
        return $result;
    }
}
