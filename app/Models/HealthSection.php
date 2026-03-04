<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthSection extends Model
{
    protected $table = 'health_section';

    protected $fillable = ['main_image', 'overlapping_image'];
}
