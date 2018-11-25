<?php

class Controller_Home extends Controller_App
{
    public function init()
    {
        parent::init();
        $this->_home_location = APP_DIR . "login/";
    }

    public function action_index()
    {
        $this->_view
            ->set_tag_body_contents('class="home"')
            ->set_var('msg', $this->_msg)
            ->set_var('errmsg', $this->_errmsg);
    }
}

