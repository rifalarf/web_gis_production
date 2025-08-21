<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Clickbar\Magellan\Data\Geometries\MultiLineString;

class Segment extends Model
{
    protected $table = 'segments';

    protected $fillable = [
        'ruas_code','sta','jens_perm','kondisi','geometry',
    ];

    protected $casts = [
        // Magellan’s MultiLineString
        'geometry' => MultiLineString::class,
    ];

    public function ruas()
    {
        return $this->belongsTo(Ruas::class, 'ruas_code', 'code');
    }
}
