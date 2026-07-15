<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BondDeliverable extends Model
{
    protected $fillable = ['code', 'deliverable'];

    public function bondOutputIndicators()
    {
        return $this->belongsToMany(BondOutputIndicator::class, 'bond_deliverable_bond_output_indicator');
    }
}
