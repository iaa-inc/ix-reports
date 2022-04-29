<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortCount extends Model
{
    use HasFactory;
    use HasTimestamps;

    protected $fillable = [
        'ix',
        'site',
        'switch',
        'count_100',
        'count_1000',
        'count_10000',
        'count_100000',
        'count_40000',
        'count_400000',
        'total_cross_connects',
        'used_cross_connects',
        'free_cross_connects'
    ];


}
