<?php

namespace App\Models;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
	protected $fillable = [
		'attendance_id',
		'user_id',
		'admin_id',
		'application_date',
		'status',
		'note',
	];

	public function user() {
		return $this->belongsTo(User::class);
	}

	public function admins() {
		return $this->belongsToMany(Admin::class);
	}

	public function attendance() {
		return $this->belongsTo(Attendance::class);
	}
}

