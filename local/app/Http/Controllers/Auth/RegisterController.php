<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use App\WishareUser;

class RegisterController extends Controller
{

    protected $wishareUser;

    public function __construct()
    {
        $this->wishareUser = new WishareUser();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userDetails = array(
                'lastname'=>$request['lastname'],
                'firstname'=>$request['firstname'],
                'username'=>$request['username'],
                'password'=>$request['password'],
                'email'=>$request['email'],
                'type'=>'1',
                'status'=>'1',
            );

        $validation = Validator::make($userDetails, [
            'lastname'=>'required|min:2|max:50|regex:/^([a-zA-Z]+\s)*[a-zA-Z]+$/',
            'firstname'=>'required|min:2|max:50|regex:/^([a-zA-Z]+\s)*[a-zA-Z]+$/',
            'username'=>'required|min:3|max:15|unique:wishare_users|alpha_num',
            'password'=>'required|min:3|max:30|alpha_num',
            'email'=>'required|unique:wishare_users|max:255|email',
            ]);

        if($validation->fails())
        {
            $failedRules = $validation->failed();
            $field = '';

            if(isset($failedRules['lastname']['Required'])){
                $message = 'Lastname is required.';
                $field = 'lastname';
            }
            else if(isset($failedRules['lastname']['Regex'])){
                $message = 'Lastname must be letters only.';
                $field = 'lastname';
            }
            else if(isset($failedRules['lastname']['Max'])){
                $message = 'Lastname must not exceed 50 letters.';
                $field = 'lastname';
            }
            else if(isset($failedRules['lastname']['Min'])){
                $message = 'Lastname must be atleast 2 letters.';
                $field = 'lastname';
            }
            else if(isset($failedRules['firstname']['Required'])){
                $message = 'Firstname is required.';
                $field = 'firstname';
            }
            else if(isset($failedRules['firstname']['Regex'])){
                $message = 'Firstname must be letters only.';
                $field = 'firstname';
            }
            else if(isset($failedRules['firstname']['Max'])){
                $message = 'Firstname must not exceed 50 letters.';
                $field = 'firstname';
            }
            else if(isset($failedRules['firstname']['Min'])){
                $message = 'Firstname must be atleast 2 letters.';
                $field = 'firstname';
            }   
            else if(isset($failedRules['username']['Required'])){
                $message = 'Username is required.';
                $field = 'username';
            }
            else if(isset($failedRules['username']['AlphaNum'])){
                $message = 'Username can only contain letters and numbers.';
                $field = 'username';
            }
            else if(isset($failedRules['username']['Min'])){
                $message = 'Username must be atleast 3 characters.';
                $field = 'username';
            }
            else if(isset($failedRules['username']['Max'])){
                $message = 'Username must not exceed 30 characters.';
                $field = 'username';
            }
            else if(isset($failedRules['password']['Required'])){
                $message = 'Password is required.';
                $field = 'password';
            }
            else if(isset($failedRules['password']['AlphaNum'])){
                $message = 'Password can only contain letters and numbers.';
                $field = 'password';
            }
            else if(isset($failedRules['password']['Min'])){
                $message = 'Password must be atleast 3 characters.';
                $field = 'password';
            }
            else if(isset($failedRules['password']['Max'])){
                $message = 'Password must not exceed 15 characters.';
                $field = 'password';
            }
            else if(isset($failedRules['email']['Required'])){
                $message = 'Email is required.';
                $field = 'email';
            }
            else if(isset($failedRules['username']['Unique'])){
                $message = 'Username is already taken.';
                $field = 'username';
            }
            else if(isset($failedRules['email']['Email'])){
                $message = 'Please enter a valid email.';
                $field = 'email';
            }
            else if(isset($failedRules['email']['Unique'])){
                $message = 'Email is already taken.';
                $field = 'email';
            }

            return response()->json(['status'=>'400', 'field'=>$field,'message'=>$message]);
        }
        else
        {
            $user['id'] = $this->wishareUser->registerUser($userDetails); 
            return response()->json(['status'=>'201','message'=>'Successfully registered.','user'=>$user]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
