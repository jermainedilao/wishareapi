<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use App\WishareUser;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if(Auth::attempt(['email'=>$request['email'], 'password'=>$request['password'], 'status'=>'1']))
        {
            $user['id'] = Auth::user()->id;
            return response()->json(['status'=>'200', 'message'=>'Successfully logged in.', 'user'=>$user]);
        }   
        else
        {
            return response()->json(['status'=>'400', 'message'=>'Invalid email or password.']);
        }
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(['status'=>'200','message'=>'Successfully logged out.']);
    }
}
