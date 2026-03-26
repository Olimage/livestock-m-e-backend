<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BondDeliverable extends Model
{
    protected $fillable = ['code', 'deliverable'];

    public function indicators()
    {
        return $this->belongsToMany(Indicator::class, 'bond_deliverable_indicator');
    }
}
