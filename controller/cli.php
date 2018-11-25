<?php

class Controller_Cli
{

    public function action_default($params = array())
    {
        echo "hello";
        var_dump($params);
    }

}
