<?php

namespace Modules\Country\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Backend\BackendBaseController;

class CountriesController extends BackendBaseController
{
    use Authorizable;

    public function __construct()
    {
        // Page Title
        $this->module_title = 'Countries';

        // module name
        $this->module_name = 'countries';

        // directory path of the module
        $this->module_path = 'country::backend';

        // module icon
        $this->module_icon = 'fa-regular fa-sun';

        // module model name, path
        $this->module_model = "Modules\Country\Models\Country";
    }

}
