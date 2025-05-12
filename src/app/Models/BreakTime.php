<?php

namespace App\Models;

use App\Models\WorkTime;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
	protected $fillable = [
		'work_time_id',
		'start_time',
		'end_time',
		'break_time',
	];

	public function workTime() {
		return $this->belongsTo(WorkTime::class);
	}
}
