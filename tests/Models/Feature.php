<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $table = 'subscription_feature';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
   
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subscription_id', 'name', 'dtype', 'value', 'usage'
    ];
}