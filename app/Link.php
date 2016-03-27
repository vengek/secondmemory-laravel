<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    public $timestamps = false;

    protected $table = 'links';

    protected $primaryKey = '_id';

    protected $fillable = ['id', 'right', 'type_id', 'x', 'y'];

    protected $casts = [
	'id' => 'integer',
	'right' => 'integer',
	'type_id' => 'integer',
	'x' => 'integer', 
	'y' => 'integer',
    ];
}
