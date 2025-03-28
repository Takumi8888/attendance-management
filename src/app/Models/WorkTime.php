<?php

namespace App\Models;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WorkTime extends Model
{
	protected $fillable = [
		'user_id',
		'clock_in_time',
		'clock_out_time',
		'work_time',
	];

	public function user() {
		return $this->belongsTo(User::class);
	}

	public function breakTimes() {
		return $this->hasMany(BreakTime::class);
	}

	public function attendance() {
		return $this->hasOne(Attendance::class);
	}
}
