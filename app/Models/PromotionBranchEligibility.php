<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionBranchEligibility extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_campaign_id',
        'branch_id',
    ];

    public function campaign()
    {
        return $this->belongsTo(PromotionCampaign::class, 'promotion_campaign_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
