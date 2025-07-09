<?php

namespace Modules\Flight\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flight extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'flights';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\Flight\database\factories\FlightFactory::new();
    }
}
