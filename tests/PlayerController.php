<?php

use SimpleMVC\BaseController;

require_once 'PlayerEntity.php';

class PlayerController extends BaseController
{
    protected function defaultAction()
    {
        echo "Default Action.";
    }

    protected function setMaps()
    {
        $this->GET_Map = array('show' => function () {
            $player = new PlayerEntity($_GET['id']);
            print_r($player->getName());
        });
    }
}
