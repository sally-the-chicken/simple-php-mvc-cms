<?php

class Model_Resources extends Base_Model
{

    protected $_id;
    protected $_name;
    protected $_description;
    protected $_created_date;
    protected $_modified_date;
    protected $_created_by;
    protected $_modified_by;

    protected function _insert()
    {

        $insert = "INSERT INTO `acl_resources` (`name`,`description`,`created_date`,`created_by`) " .
            "VALUES (?,?,NOW(),?)";
        $mysqli = $this->_get_db_connection();
        if (!$stmt = $mysqli->prepare($insert)) {
            throw new Exception("Prepare statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        $id = $this->_id;
        $name = $this->_name;
        $description = $this->_description;
        $created_date = $this->_created_date;
        $modified_date = $this->_modified_date;
        $created_by = $this->_created_by;
        $modified_by = $this->_modified_by;

        $db_result = $stmt->bind_param('ssi', $name, $description, $created_by);
        if (false === $db_result) {
            throw new Exception("Bind statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        if (false === ($result = $stmt->execute())) {
            throw new Exception("Execute statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        $this->_id = $mysqli->insert_id;
        return $result;
    }

    public function from_array($resource)
    {
        $this->_id = isset($resource['id']) ? $resource['id'] : null;
        $this->_name = isset($resource['name']) ? $resource['name'] : null;
        $this->_description = isset($resource['description']) ? $resource['description'] : null;
        $this->_created_date = isset($resource['created_date']) ? $resource['created_date'] : null;
        $this->_modified_date = isset($resource['modified_date']) ? $resource['modified_date'] : null;
        $this->_created_by = isset($resource['created_by']) ? $resource['created_by'] : null;
        $this->_modified_by = isset($resource['modified_by']) ? $resource['modified_by'] : null;

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
        $resource = array('id' => null, 'name' => null, 'description' => null, 'created_date' => null, 'modified_date' => null, 'created_by' => null, 'modified_by' => null);
        $stmt->bind_result($resource['id'], $resource['name'], $resource['description'], $resource['created_date'], $resource['modified_date'], $resource['created_by'], $resource['modified_by']);
        $stmt->fetch();
        $stmt->free_result();
        $stmt->close();
        $mysqli->close();
        $this->_id = $resource['id'];
        $this->_name = $resource['name'];
        $this->_description = $resource['description'];
        $this->_created_date = $resource['created_date'];
        $this->_modified_date = $resource['modified_date'];
        $this->_created_by = $resource['created_by'];
        $this->_modified_by = $resource['modified_by'];

        return true;
    }

    protected function _select_sql($single = false, $cnt_only = false)
    {

        $select = "SELECT ";
        if ($single) {
            $select .= "resource.`id`, " .
                "resource.`name`, " .
                "resource.`description`, " .
                "resource.`created_date`, " .
                "resource.`modified_date`, " .
                "resource.`created_by`, " .
                "resource.`modified_by`" .

                "FROM acl_resources AS resource ";
            return $select;
        }
        if ($cnt_only) {
            $select .= " count(*) as cnt ";
        } else {
            $select .= $this->_getSelectComplete();
        }
        return $select;
    }

    protected function _getSelectComplete() {
        return "resource.`id`, " .
        "resource.`name`, " .
        "resource.`description`, " .
        "resource.`created_date`, " .
        "resource.`modified_date`, " .
        "resource.`created_by`, " .
        "resource.`modified_by`" .

        "FROM acl_resources AS resource ";
    }
    protected function _update()
    {

        $update = "UPDATE `acl_resources` SET " .
            "`name` = ?,`description` = ?,`modified_date` = NOW(),`modified_by` = ?" .
            " WHERE `id` = ?";

        $mysqli = $this->_get_db_connection();
        if (!$stmt = $mysqli->prepare($update)) {
            throw new Exception("Prepare statement failed.\nQuery: $update \n{$mysqli->error}\n\n");
        }
        $id = $this->_id;
        $name = $this->_name;
        $description = $this->_description;
        $created_date = $this->_created_date;
        $modified_date = $this->_modified_date;
        $created_by = $this->_created_by;
        $modified_by = $this->_modified_by;

        $stmt->bind_param('ssii', $name, $description, $modified_by, $id);
        if (false === ($result = $stmt->execute())) {
            throw new Exception("Execute statement failed.\nQuery: $update
{$mysqli->error}\n\n");
        }
        return $result;
    }

}
