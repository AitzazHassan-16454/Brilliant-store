<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = Str::lower(trim($value));
    }
}
