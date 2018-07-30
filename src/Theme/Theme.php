<?php

namespace Creatyon\Core\Theme;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{

    protected $table = 'themes';

    protected $fillable = [
        'name', 'slug', 'theme', 'parent','admin', 'active'
    ];

    public function setNameAttribute($name)
    {
        $this->attributes['name'] = $name;
        $this->attributes['slug'] = str_slug($name);
    }
}
