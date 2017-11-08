<?php

namespace Statamic\Addons\MJML;

use Statamic\Extend\Controller;

class MJMLController extends Controller
{
    /**
     * Maps to your route definition in routes.yaml
     *
     * @return mixed
     */
    public function index()
    {
        return $this->view('index');
    }
}
