<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ShortUrl extends Model
{
    /** @use HasFactory<\Database\Factories\ShortUrlFactory> */
    use HasFactory;

    protected $hidden = [
        'id',
    ];
}
