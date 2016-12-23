<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PramotionPromoBanner extends Model
{
    protected $table = 'pramotion_promo_banner';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['promotion_name','campaign_id','	type','type_id','banner'];

   
}