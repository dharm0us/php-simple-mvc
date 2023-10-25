<?php

use SimpleMVC\BaseController;

class GenericWebViewController extends BaseController
{
    protected function defaultAction()
    {
        echo "Default Action.";
    }

    protected function setMaps()
    {
        $this->GET_Map = array('get_action' => function () {
            echo $_GET['param1'];
        });
        $this->POST_Map = array('post_action' => function () {
            echo $_POST['sampleParam'] . "123";
        });
    }
}
