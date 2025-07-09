<?php

namespace Modules\Country\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'countries';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\Country\database\factories\CountryFactory::new();
    }
    public function cities()
    {
        return $this->hasMany('Modules\City\Models\City', 'country_id', 'id');
    }
}
