<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use DB;
use Hash;

class WishareUser extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
	use Authenticatable, Authorizable, CanResetPassword;

    protected $table = 'wishare_users';
    protected $fillable = [
    					'lastname',
    					'firstname',
    					'username',
    					'password',
    					'email',
                        'imageurl',
    					'status',
                        'birthdate',
                        'facebook',
                        'status',
                        'type',
    				];

    public function registerUser($userDetails)
    {
        $userDetails['password']=bcrypt($userDetails['password']);
        $userDetails['imageurl']='http://images.wishare.net/userimages/default.jpg';
        $userDetails['status'] = 1;
        $userDetails['type']=1;
    	$registerUser = WishareUser::create($userDetails);
    	
        if($registerUser)
        {
          $this->createDefaultWishlist($registerUser->id);
          return $registerUser->id;
        }
    }

    public function createDefaultWishlist($createdby_id)
    {
        $defaultwishlists = DefaultWishlist::where('status', '=', 1)
                            ->orderBy('created_at', 'desc')
                            ->get();

        foreach ($defaultwishlists as $dw) 
        {
          $wishlist = new Wishlist(array(
            'title' => $dw->title,
            'createdby_id' => $createdby_id,
            'privacy' => 0,
            'status' => 1,
          ));

          $wishlist->save();
        }

        $user = WishareUser::find($createdby_id);
        $user->defaultwishlist = 1;
        $user->save();

        return;
    }

    public function updateUser($userDetails, $id)
    {
        if(isset($userDetails['password']))
            $userDetails['password'] = bcrypt($userDetails['password']);
        $updateUser = WishareUser::where('id','=',$id)->update($userDetails);

        if($updateUser)
            return WishareUser::find($id);
    }

    public function getUser($id)
    {
        return WishareUser::find($id);
    }

    public function addImageUrl($id, $imageurl)
    {
        $user = WishareUser::find($id);
        $user->imageurl = $imageurl;
        $addImageUrl = $user->save();

        if($addImageUrl)
            return WishareUser::find($id);
    }

    public function searchUsers($username)
    {
        $users = WishareUser::where('status','=','1')
                 ->where('type','=','1')
                 ->where(function ($query) use ($username){
                        $query->where('username', 'like', '%'.$username.'%')
                              ->orWhere('lastname', 'like', '%'.$username.'%')
                              ->orWhere('firstname', 'like', '%'.$username.'%');
                    })
                 ->orderBy('username')
                 ->orderBy('firstname')
                 ->orderBy('lastname')
                 ->get();
        return $users;

        // ->where('username','LIKE','%'.$username.'%')
        //          ->orWhere('firstname','LIKE','%'.$username.'%')
        //          ->orWhere('lastname','LIKE','%'.$username.'%')
    }

    public function getUserUsername($userid)
    {
        $getUserUsername = WishareUser::find($userid);
        return $getUserUsername->username;
    }

    public function getUserImageurl($userid)
    {
        $getUserUsername = WishareUser::find($userid);
        return $getUserUsername->imageurl;
    }

    public function isOldPasswordCorrect($userid, $oldpassword)
    {
        $getUser = WishareUser::find($userid);

        if(Hash::check($oldpassword, $getUser->password))
            return true;
        else
            return false;
    }
}
