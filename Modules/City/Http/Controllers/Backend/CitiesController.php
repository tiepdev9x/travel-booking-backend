<?php

namespace Modules\City\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Backend\BackendBaseController;

class CitiesController extends BackendBaseController
{
    use Authorizable;

    public function __construct()
    {
        // Page Title
        $this->module_title = 'Cities';

        // module name
        $this->module_name = 'cities';

        // directory path of the module
        $this->module_path = 'city::backend';

        // module icon
        $this->module_icon = 'fa-regular fa-sun';

        // module model name, path
        $this->module_model = "Modules\City\Models\City";
    }

}
