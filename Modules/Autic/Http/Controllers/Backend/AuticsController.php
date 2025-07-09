<?php

namespace Modules\Autic\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Backend\BackendBaseController;

class AuticsController extends BackendBaseController
{
    use Authorizable;

    public function __construct()
    {
        // Page Title
        $this->module_title = 'Autics';

        // module name
        $this->module_name = 'autics';

        // directory path of the module
        $this->module_path = 'autic::backend';

        // module icon
        $this->module_icon = 'fa-regular fa-sun';

        // module model name, path
        $this->module_model = "Modules\Autic\Models\Autic";
    }

}
