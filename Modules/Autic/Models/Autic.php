<?php

namespace Modules\Autic\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Autic extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'autics';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\Autic\database\factories\AuticFactory::new();
    }
}
