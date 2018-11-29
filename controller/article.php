<?php

class Controller_Article extends Controller_App
{

    public function init()
    {

        parent::init();
        $this->_home_location = APP_DIR . "article/";
        $this->_check_access('articles.view');

    }

    public function action_index()
    {

        $form_inputs = Util_Form::get_form_inputs('article');
        $inputs = $form_inputs['inputs'];

        $this->_view
            ->set_tag_body_contents('class="article"')
            ->set_var('table_cols', $this->_get_article_list_table_cols())
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
                'src' => DIR_ASSETS_JS . 'article_list.js'))
            ->add_css("https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css")
            ->add_css("https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css")
            ->add_css("https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css");

    }

    protected function _get_article_list_table_cols()
    {
        return array(
            0 => '',
            'article.`id`' => 'ID',
            'article.`title`' => 'Title',
            'tags' => 'Tags',
            '`created_user_display_name`' => 'Author',
            'article.`status`' => 'Status',
            'article.`publish_date`' => 'Publish Date',
        );
    }

    public function action_ajax_list()
    {

        $cols = $this->_get_article_list_table_cols();

        $start = isset($_GET['start']) && is_numeric($_GET['start']) ? $_GET['start'] : 0;
        $length = isset($_GET['length']) && is_numeric($_GET['length']) ? $_GET['length'] : 20;
        $order = isset($_GET['order']) && is_array($_GET['order']) ? $_GET['order'][0] : array('column' => 'article.`id`', 'dir' => 'DESC');
        $search = isset($_GET['search']) && is_array($_GET['search']) ? $_GET['search'] : array();

        if (isset($order['column']) && is_numeric($order['column']) && isset($order['dir'])) {
            $keyval = array_slice($cols, $order['column'], 1);
            $order['column'] = key($keyval);
            $order['dir'] = $order['dir'] == 'asc' ? 'ASC' : 'DESC';
        }
        $searchword = '';
        if (isset($search['value'])) {
            $searchword = trim($search['value']);
        }
        if (empty($order)) {
            $order['column'] = 'article.`id`';
            $order['dir'] = 'DESC';
        }

        $order = " {$order['column']} {$order['dir']} ";

        $conditions = null;
        if (!empty($condition_arr)) {
            $conditions = implode(' AND ', $condition_arr);
        }

        $model_article = new Model_Articles();
        $articles = $model_article->get_all_with_assocs($conditions, $order, $length, $start, $searchword);
        if (empty($articles)) {
            $articles = array();
        }
        $articles_cnt = $model_article->get_all_with_assocs_cnt();
        $articles_filtered_cnt = count($articles);

        $output = array(
            "get" => $_GET,
            "draw" => $_GET['draw'],
            "recordsTotal" => $articles_cnt,
            "recordsFiltered" => $articles_filtered_cnt,
            "data" => array(),
        );

        if (empty($articles)) {
            echo json_encode($output);
            exit;
        }

        $model_tags = new Model_Tags();
        foreach ($articles as $article) {
            $tags = $model_tags->get_by_article_id($article['id']);
            if (empty($tags)) {
                $tags = array();
            }
            $tag_names = array_map(function ($tag) {return $tag['name'];}, $tags);
            $row = array();
            $row[] = '<a class="btn btn-primary" href="' . APP_DIR . 'article/edit/id/' . $article['id'] . '"><i class="fa fa-edit"></i></a>';
            $row[] = $article['id'];
            $row[] = $article['title'];
            $row[] = empty($tags) ? '' : implode(',', $tag_names);
            $row[] = $article['created_user_display_name'];
            $row[] = $article['status'];
            $row[] = $article['publish_date'];
            $output['data'][] = $row;
        }
        echo json_encode($output);
        exit;
    }

    protected function _show_article($model_article)
    {

        $model_article->read_tags();
        $form_inputs = Util_Form::get_form_inputs('article');
        $inputs = $form_inputs['inputs'];

        // tags
        $model_tag = new Model_Tags();
        $tags = $model_tag->get_all();
        $tag_options = array();
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $tag_options[] = $tag['name'];
            }
        }

        $inputs['status']['value'] = $model_article->status;
        $inputs['title']['value'] = $model_article->title;
        $inputs['content']['value'] = $model_article->content;
        $inputs['friendly_url']['value'] = rawurldecode($model_article->friendly_url);

        if ($model_article->tags) {
            $inputs['tags']['value'] = implode(', ',
                array_map(function ($tag) {
                    return $tag['name'];
                }, $model_article->tags));
        }

        // set required input fields
        foreach (array('title', 'status') as $input_names) {
            $inputs[$input_names]['required'] = true;
        }

        $this->_view
            ->set_tag_body_contents('class="article"')
            ->set_var('tag_options', $tag_options)
            ->set_var('article', $model_article)
            ->set_var('inputs', $inputs)
            ->set_var('msg', $this->_msg)
            ->set_var('errmsg', $this->_errmsg)
            ->add_js('foot', "https://code.jquery.com/ui/1.12.1/jquery-ui.min.js")
            ->add_js('foot', "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js")
            ->add_js('foot', "https://cdn.jsdelivr.net/npm/tinymce@4.8.5/tinymce.min.js")
            ->add_js('foot', DIR_ASSETS_JS . 'article.js')
            ->add_css("https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css")
            ->add_css("https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css");

    }

    public function action_add()
    {

        $model_article = new Model_Articles();

        // post request
        if (!empty($_POST)) {
            $result = $this->_upsert_article($model_article);
            $location = APP_DIR . 'article/add/';
            if ($result['success']) {
                $this->_msg = 'Success!';
                $location = APP_DIR . 'article/edit/id/' . $model_article->id;
            } elseif (!empty($result['err_msg'])) {
                $this->_errmsg = $result['err_msg'];
            } else {
                $this->_errmsg = "Unknown error.";
            }
            $this->set_msgs();
            Util_Header::redirect($location);
        }
        $this->_show_article($model_article);

    }

    public function action_edit()
    {

        if (!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) {
            Util_Header::redirect($this->_home_location);
        }
        $model_article = new Model_Articles();
        $model_article->read(trim($_REQUEST['id']));
        if (!$model_article->id) {
            Util_Header::redirect($this->_home_location);
        }

        // post request
        if (isset($_POST['id'])) {
            $result = $this->_upsert_article($model_article);
            if ($result['success']) {
                $this->_msg = 'Success!';
            } elseif (!empty($result['err_msg'])) {
                $this->_errmsg = $result['err_msg'];
            } else {
                $this->_errmsg = "Unknown error.";
            }
            $this->set_msgs();
            Util_Header::redirect(APP_DIR . 'article/edit/id/' . $model_article->id);
        }

        $this->_show_article($model_article);

    }

    protected function _upsert_article($model_article)
    {

        $result = array('success' => false, 'err_msg' => null);

        // check inputs
        foreach ($_POST as &$param) {
            if (is_array($param)) {
                continue;
            }
            $param = trim($param);
        }

        // sanitize
        $form_inputs = Util_Form::get_form_inputs('article');
        $form_inputs = $form_inputs['inputs'];
        if (empty($_POST['status']) ||
            !array_key_exists($_POST['status'], $form_inputs['status']['options'])) {
            $result['err_msg'][] = "Value of status [{$_POST['status']}] is invalid.";
        }
        if (empty($_POST['title'])) {
            $result['err_msg'][] = "Title must not be empty.";
        }

        // check access
        if (!$_SESSION[SESS_USER_VAR]->can('articles.edit')) {
            $result['errmsgs'][] = 'You are not authorized to add/edit articles.';
        }

        if (!empty($result['err_msg'])) {
            return $result;
        }

        $article = array();
        $article['id'] = $model_article->id;

        foreach ($_POST as $key => $val) {
            $article[$key] = $val;
        }

        $article['modified_by'] = $_SESSION[SESS_USER_VAR]->id;
        $article['created_by'] = $model_article->id ?
        $model_article->created_by : $_SESSION[SESS_USER_VAR]->id;

        // set friendly url
        $title4url = Util_File::convert_url($article['title']);
        if (!empty($article['friendly_url'])) {
            $title4url = Util_File::convert_url($article['friendly_url']);
        }
        if (empty($title4url)) {
            $result['err_msg'][] = "Friendly URL cannot be generated.";
            return $result;
        }
        // if friendly url is duplicated, add something at the end of string to make it unique
        $duplicates = $model_article->get_by_friendly_url($title4url);
        if ($model_article->id) {
            unset($duplicates[$model_article->id]);
        }
        while (!empty($duplicates)) {
            $title4url = $title4url . '-';
            $duplicates = $model_article->get_by_friendly_url($title4url);
        }

        $article['friendly_url'] = $title4url;
        $article['publish_date'] = $model_article->publish_date;

        // if status = published and publish date is not set, set publish date to now
        if ((empty($article['publish_date']) || $article['publish_date'] == '0000-00-00 00:00:00') &&
            $article['status'] === 'published') {
            $article['publish_date'] = date('Y-m-d H:i:s');
        }

        if ($article['status'] === 'pending') {
            $article['publish_date'] = null;
        }

        // tags
        $tag_names = array();
        foreach (explode(',', $_POST['tags']) as $tag) {
            $tag_names[] = trim($tag);
        }

        try {
            $model_article->from_array($article)->save();
            $model_article->save_tags($tag_names);
            $result['success'] = true;
        } catch (Exception $e) {
            $result['err_msg'] = $e->getMessage();
        }

        return $result;

    }

}
