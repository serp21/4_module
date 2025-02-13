<?php

class  MainController extends Controller
{

    public function index()
    {

        $view = new View;
        $view->view('index');
    }
}
