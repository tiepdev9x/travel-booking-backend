<?php

namespace Modules\Flight\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Backend\BackendBaseController;

class FlightsController extends BackendBaseController
{
    use Authorizable;

    public function __construct()
    {
        // Page Title
        $this->module_title = 'Flights';

        // module name
        $this->module_name = 'flights';

        // directory path of the module
        $this->module_path = 'flight::backend';

        // module icon
        $this->module_icon = 'fa-regular fa-sun';

        // module model name, path
        $this->module_model = "Modules\Flight\Models\Flight";
    }

}
