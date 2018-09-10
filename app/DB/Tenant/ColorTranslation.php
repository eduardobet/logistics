<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class ColorTranslation extends Model
{
    protected $table = 'colors_translation';

    public function color()
    {
        return $this->belongsTo(Color::class);
    }
}
