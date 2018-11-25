<?php

class Model_Articles extends Base_Model
{
    protected $_id;
    protected $_status;
    protected $_friendly_url;
    protected $_title;
    protected $_content;
    protected $_publish_date;
    protected $_created_date;
    protected $_modified_date;
    protected $_created_by;
    protected $_modified_by;

    public function get_by_friendly_url($friendly_url)
    {
        $friendly_url = $this->_sanitize($friendly_url);
        $conditions = " article.`friendly_url` = '$friendly_url'";
        return $this->_get_all($conditions);
    }

    public function get_all_with_assocs($conditions = null, $order = null, $limit, $offset, $search_word = null)
    {
        return $this->_get_all_with_assocs($conditions, false, $order, $limit, $offset, $search_word);
    }

    public function get_all_with_assocs_cnt($conditions = null, $search_word = null)
    {
        return $this->_get_all_with_assocs($conditions, true, null, null, null, $search_word);
    }

    protected function _get_all_with_assocs(
        $conditions = null,
        $cnt_only = false,
        $order = null,
        $limit = null,
        $offset = null,
        $search_word = null) {

        if (!empty($search_word)) {
            $mysqli = $this->_get_db_connection();
            $search_word = $mysqli->real_escape_string($search_word);
            $searchword_q = "'%$search_word%'";
            $where_clauses =
                "(article.`id` LIKE $searchword_q OR " .
                "article.`friendly_url` LIKE $searchword_q OR " .
                "article.`title` LIKE $searchword_q OR " .
                "article.`content`  LIKE $searchword_q OR " .
                "created_user.`login` LIKE $searchword_q OR " .
                "created_user.`display_name` LIKE $searchword_q OR " .
                "modified_user.`login` LIKE $searchword_q OR " .
                "modified_user.`display_name` LIKE $searchword_q ) ";

            if ($conditions === null) {
                $conditions = $where_clauses;
            } else {
                $conditions .= " AND " . $where_clauses;
            }
        }

        $articles = $this->_get_all(
            $conditions, $cnt_only, $order, $limit, $offset);

        return $articles;

    }

    protected function _insert()
    {

        $insert = "INSERT INTO `articles` (`status`,`friendly_url`,`title`,`content`,`publish_date`,`created_date`,`created_by`) " .
            "VALUES (?,?,?,?,?,NOW(),?)";
        $mysqli = $this->_get_db_connection();
        if (!$stmt = $mysqli->prepare($insert)) {
            throw new Exception("Prepare statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        $id = $this->_id;
        $status = $this->_status;
        $friendly_url = $this->_friendly_url;
        $title = $this->_title;
        $content = $this->_content;
        $publish_date = $this->_publish_date;
        $created_date = $this->_created_date;
        $modified_date = $this->_modified_date;
        $created_by = $this->_created_by;
        $modified_by = $this->_modified_by;

        $db_result = $stmt->bind_param('sssssi', $status, $friendly_url, $title, $content, $publish_date, $created_by);
        if (false === $db_result) {
            throw new Exception("Bind statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        if (false === ($result = $stmt->execute())) {
            throw new Exception("Execute statement failed.\nQuery: $insert \n{$mysqli->error}\n\n");
        }
        $this->_id = $mysqli->insert_id;
        return $result;
    }

    public function from_array($article)
    {
        $this->_id = isset($article['id']) ? $article['id'] : null;
        $this->_status = isset($article['status']) ? $article['status'] : null;
        $this->_friendly_url = isset($article['friendly_url']) ? $article['friendly_url'] : null;
        $this->_title = isset($article['title']) ? $article['title'] : null;
        $this->_content = isset($article['content']) ? $article['content'] : null;
        $this->_publish_date = isset($article['publish_date']) ? $article['publish_date'] : null;
        $this->_created_date = isset($article['created_date']) ? $article['created_date'] : null;
        $this->_modified_date = isset($article['modified_date']) ? $article['modified_date'] : date("Y-m-d H:i:s");
        $this->_created_by = isset($article['created_by']) ? $article['created_by'] : null;
        $this->_modified_by = isset($article['modified_by']) ? $article['modified_by'] : null;

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
        $article = array('id' => null, 'status' => null, 'friendly_url' => null, 'title' => null, 'content' => null, 'publish_date' => null, 'created_date' => null, 'modified_date' => null, 'created_by' => null, 'modified_by' => null);
        $stmt->bind_result($article['id'], $article['status'], $article['friendly_url'], $article['title'], $article['content'], $article['publish_date'], $article['created_date'], $article['modified_date'], $article['created_by'], $article['modified_by']);
        $stmt->fetch();
        $stmt->free_result();
        $stmt->close();
        $mysqli->close();
        $this->_id = $article['id'];
        $this->_status = $article['status'];
        $this->_friendly_url = $article['friendly_url'];
        $this->_title = $article['title'];
        $this->_content = $article['content'];
        $this->_publish_date = $article['publish_date'];
        $this->_created_date = $article['created_date'];
        $this->_modified_date = $article['modified_date'];
        $this->_created_by = $article['created_by'];
        $this->_modified_by = $article['modified_by'];

        return true;
    }

    protected function _select_sql($single = false, $cnt_only = false)
    {

        $select = "SELECT ";
        if ($single) {
            $select .= "article.`id`, " .
                "article.`status`, " .
                "article.`friendly_url`, " .
                "article.`title`, " .
                "article.`content`, " .
                "article.`publish_date`, " .
                "article.`created_date`, " .
                "article.`modified_date`, " .
                "article.`created_by`, " .
                "article.`modified_by`" .

                "FROM articles AS article ";
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
        return "article.`id`, " .
            "article.`status`, " .
            "article.`friendly_url`, " .
            "article.`title`, " .
            "article.`content`, " .
            "article.`publish_date`, " .
            "article.`created_date`, " .
            "article.`modified_date`, " .
            "article.`created_by`, " .
            "article.`modified_by`, " .
            "created_user.`id` as created_user_id," .
            "created_user.`login` as created_user_login," .
            "created_user.`email` as created_user_email," .
            "created_user.`display_name` as created_user_display_name," .
            "created_user.`status` as created_user_status," .
            "created_user.`last_login_time` as created_user_last_login_time," .
            "created_user.`last_login_ip` as created_user_last_login_ip," .
            "modified_user.`id` as modified_user_id," .
            "modified_user.`login` as modified_user_login," .
            "modified_user.`email` as modified_user_email," .
            "modified_user.`display_name` as modified_user_display_name," .
            "modified_user.`status` as modified_user_status," .
            "modified_user.`last_login_time` as modified_user_last_login_time," .
            "modified_user.`last_login_ip` as modified_user_last_login_ip," .
            "modified_user.`id` as modified_user_id " .
            "FROM `articles` as article " .
            "LEFT JOIN `acl_users` as created_user ON article.`created_by` = created_user.`id` " .
            "LEFT JOIN `acl_users` as modified_user ON article.`modified_by` = modified_user.`id` ";
    }

    protected function _update()
    {

        $update = "UPDATE `articles` SET " .
            "`status` = ?,`friendly_url` = ?,`title` = ?,`content` = ?,`publish_date` = ?,`modified_date` = NOW(),`modified_by` = ?" .
            " WHERE `id` = ?";

        $mysqli = $this->_get_db_connection();
        if (!$stmt = $mysqli->prepare($update)) {
            throw new Exception("Prepare statement failed.\nQuery: $update \n{$mysqli->error}\n\n");
        }
        $id = $this->_id;
        $status = $this->_status;
        $friendly_url = $this->_friendly_url;
        $title = $this->_title;
        $content = $this->_content;
        $publish_date = $this->_publish_date;
        $created_date = $this->_created_date;
        $modified_date = $this->_modified_date;
        $created_by = $this->_created_by;
        $modified_by = $this->_modified_by;

        $stmt->bind_param('sssssii', $status, $friendly_url, $title, $content, $publish_date, $modified_by, $id);
        if (false === ($result = $stmt->execute())) {
            throw new Exception("Execute statement failed.\nQuery: $update
{$mysqli->error}\n\n");
        }
        return $result;
    }
}
