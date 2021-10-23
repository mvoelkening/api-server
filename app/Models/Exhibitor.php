<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class Exhibitor extends Authenticatable implements JWTSubject
{

	protected $table = 'exhibitors';
	public $timestamps = false;

	use HasFactory, Notifiable;
	/**
	 * @return int
	 */
	public function getJWTIdentifier()
	{
		return $this->getKey();
	}

	/**
	 * @return array
	 */
	public function getJWTCustomClaims()
	{
		return [];
	}
}
