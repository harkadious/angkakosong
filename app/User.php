<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {
	use Authenticatable, CanResetPassword;

	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'email', 'password',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token',
	];

	public function posts(){
		return $this->hasMany('App\Posts', 'writer_id');
	}
	
	public function comments(){
		return $this->hasMany('App\Comments', 'from_user');
	}

	public function can_post(){
		$role = $this->role;
		if($role == 'writer' || $role =='superuser'){
			return true;
		}
		return false;
	}

	public function is_superuser(){
		$role = $this->role;
		if($role == 'superuser'){
			return true;
		}
		return false;
	}

}
