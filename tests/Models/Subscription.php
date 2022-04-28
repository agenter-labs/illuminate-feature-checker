<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use AgenterLab\FeatureChecker\SubscriptionContract;

class Subscription extends Model implements SubscriptionContract
{
    protected $table = 'subscription';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
   
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'end_date', 'is_deleted'
    ];

    public function getEndDate(): int
    {
        return $this->end_date;
    }

    public function features() {

        return $this->hasMany(Feature::class, 'subscription_id', 'id');
    }
}