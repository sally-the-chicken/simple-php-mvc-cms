<?php

abstract class Controller_App extends Base_Controller
{
    protected $_home_location; 

    public function init()
    {
        parent::init();
        $this->_view
            ->set_title('Simple PHP MVC CMS Sample')
            ->add_meta('<meta charset="utf-8">')
            ->add_meta('<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">')
            ->add_css(array(
                'href' => 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css',
                'integrity' => 'sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO',
                'crossorigin' => 'anonymous'))
            ->add_css(DIR_ASSETS_CSS . 'main.css')
            ->add_js('foot', array(
                'src' => 'https://code.jquery.com/jquery-3.3.1.min.js',
                'integrity' => 'sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=',
                'crossorigin' => 'anonymous'
            ))
            ->add_js('foot', array(
                'src' => 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js',
                'integrity' => 'sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy',
                'crossorigin' => 'anonymous'))
            ->add_js('foot', array(
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js',
                'integrity' => 'sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49',
                'crossorigin' => 'anonymous'));
    }

    protected function _check_access($resource)
    {
        // check access
        if (!$_SESSION[SESS_USER_VAR]->can($resource)) {
            $this->_errmsg = 'Unauthorized access.';
            $this->set_msgs();
            Util_Header::redirect(APP_DIR);
        }
    }
}
