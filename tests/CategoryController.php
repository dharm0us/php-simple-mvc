<?php

use SimpleMVC\BaseController;

require_once 'CategoryEntity.php';

class CategoryController extends BaseController
{
    protected function setMaps()
    {
        $this->POST_Map = array('show' => function () {
            $category = new CategoryEntity($_POST['id']);
            print_r($category->getCat());
        });
    }
}
