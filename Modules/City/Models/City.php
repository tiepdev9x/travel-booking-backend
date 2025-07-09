<?php

namespace Modules\City\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cities';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\City\database\factories\CityFactory::new();
    }
    public function countries()
    {
        return $this->belongsTo('Modules\Country\Models\Country', 'country_id', 'id');
    }
}
