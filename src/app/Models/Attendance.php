<?php

namespace App\Models;

use App\Models\CorrectionRequest;
use App\Models\WorkTime;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
	protected $fillable = [
		'work_time_id',
		'work_day',
		'total_break_time',
		'actual_working_hours',
	];

	public function workTime() {
		return $this->belongsTo(WorkTime::class);
	}

	public function correctionRequest() {
		return $this->hasOne(CorrectionRequest::class);
	}

}
