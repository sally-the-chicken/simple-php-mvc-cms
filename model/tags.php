<?php

class Model_Tags extends Base_Model
{
    protected $_id;
    protected $_friendly_url;
    protected $_name;
    protected $_created_date;
    protected $_modified_date;
    protected $_created_by;
    protected $_modified_by;

    public function get_by_article_id($id) {
        $id = $this->_sanitize($id);
        $select = $this->_select_sql();
        $select .= " JOIN `article_tags` as article_tag ON tag.`id` = article_tag.`tag_id` ";
        return $this->_get_all(" article_tag.`article_id` = '$id'", false, null, null, null, $select);
    }

    protected function _insert()
    {

        $insert = "INSERT INTO `tags` (`friendly_url`,`name`,`created_date`,`created_by`) " .
            "VALUES (?,?,NOW(),?)";
        $mysqli = $this->_get_db_connection();
        if (!$stmt = $mysqli->prepare($insert)) {
            throw new Exception("Prepare statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        $id = $this->_id;
        $friendly_url = $this->_friendly_url;
        $name = $this->_name;
        $created_date = $this->_created_date;
        $modified_date = $this->_modified_date;
        $created_by = $this->_created_by;
        $modified_by = $this->_modified_by;

        $db_result = $stmt->bind_param('ssi', $friendly_url, $name, $created_by);
        if (false === $db_result) {
            throw new Exception("Bind statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        if (false === ($result = $stmt->execute())) {
            throw new Exception("Execute statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        $this->_id = $mysqli->insert_id;
        return $result;
    }

    public function from_array($tag)
    {
        $this->_id = isset($tag['id']) ? $tag['id'] : null;
        $this->_friendly_url = isset($tag['friendly_url']) ? $tag['friendly_url'] : null;
        $this->_name = isset($tag['name']) ? $tag['name'] : null;
        $this->_created_date = isset($tag['created_date']) ? $tag['created_date'] : null;
        $this->_modified_date = isset($tag['modified_date']) ? $tag['modified_date'] : date("Y-m-d H:i:s");
        $this->_created_by = isset($tag['created_by']) ? $tag['created_by'] : null;
        $this->_modified_by = isset($tag['modified_by']) ? $tag['modified_by'] : null;

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
        $tag = array('id' => null, 'friendly_url' => null, 'name' => null, 'created_date' => null, 'modified_date' => null, 'created_by' => null, 'modified_by' => null);
        $stmt->bind_result($tag['id'], $tag['friendly_url'], $tag['name'], $tag['created_date'], $tag['modified_date'], $tag['created_by'], $tag['modified_by']);
        $stmt->fetch();
        $stmt->free_result();
        $stmt->close();
        $mysqli->close();
        $this->_id = $tag['id'];
        $this->_friendly_url = $tag['friendly_url'];
        $this->_name = $tag['name'];
        $this->_created_date = $tag['created_date'];
        $this->_modified_date = $tag['modified_date'];
        $this->_created_by = $tag['created_by'];
        $this->_modified_by = $tag['modified_by'];

        return true;
    }
    protected function _select_sql($single = false, $cnt_only = false)
    {

        $select = "SELECT ";
        if ($single) {
            $select .= "tag.`id`, " .
                "tag.`friendly_url`, " .
                "tag.`name`, " .
                "tag.`created_date`, " .
                "tag.`modified_date`, " .
                "tag.`created_by`, " .
                "tag.`modified_by`" .
                "FROM tags AS tag ";
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
        return "tag.`id`, " .
            "tag.`friendly_url`, " .
            "tag.`name`, " .
            "tag.`created_date`, " .
            "tag.`modified_date`, " .
            "tag.`created_by`, " .
            "tag.`modified_by`" .
            "FROM tags AS tag ";
    }

    protected function _update()
    {

        $update = "UPDATE `tags` SET " .
            "`friendly_url` = ?,`name` = ?,`modified_date` = NOW(),`modified_by` = ?" .
            " WHERE `id` = ?";

        $mysqli = $this->_get_db_connection();
        if (!$stmt = $mysqli->prepare($update)) {
            throw new Exception("Prepare statement failed.\nQuery: $update \n{$mysqli->error}\n\n");
        }
        $id = $this->_id;
        $friendly_url = $this->_friendly_url;
        $name = $this->_name;
        $created_date = $this->_created_date;
        $modified_date = $this->_modified_date;
        $created_by = $this->_created_by;
        $modified_by = $this->_modified_by;

        $stmt->bind_param('ssii', $friendly_url, $name, $modified_by, $id);
        if (false === ($result = $stmt->execute())) {
            throw new Exception("Execute statement failed.\nQuery: $update
{$mysqli->error}\n\n");
        }
        return $result;
    }
}
