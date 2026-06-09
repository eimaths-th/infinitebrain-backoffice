<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
    ];

    public function receipts()
    {
        return $this->hasMany(Receipt_School::class, 'promotion_id', 'id');
    }

    public function campaigns()
    {
        return $this->hasMany(PromotionCampaign::class);
    }
}
