<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Notifications\CommonNotification;
use App\Traits\ResetsPasswords;
use App\Traits\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    use SendsPasswordResetEmails, ResetsPasswords;
    public function __construct()
    {
        $this->broker = 'users';
    }

    /**
     * Handles Registration Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'username' => 'required|min:3|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'first_login' => 1,
            'user_type_id' => 2 // normal users
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'active'   => 0,
            'verify_account' => Str::random(10),
        ]);

        // $token = $user->createToken('LinkMe')->accessToken;

        $this->sendVerifyEmail($user);
        // return response()->json(['token' => $token], 200);
        return response()->json(['message' => 'Activate your Account using activation link.'], 200);
    }

    /**
     * Handles Login Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password) && isset($user->active) && !empty($user->active)) {
                $token = $user->createToken(env('APP_NAME'))->accessToken;
                $first_login = (isset($user->first_login))? $user->first_login : 0;
                $categories = Category::get();
                $response = ['token' => $token, 'first_login' => $first_login, 'categories' => $categories];
                return response($response, 200);
            } else if(!isset($user->active) || empty($user->active)){
                $response = ["message" => "Your Account is Not Active Yet."];
                return response($response, 422);
            } else {
                $response = ["message" => "Password mismatch"];
                return response($response, 422);
            }
        } else {
            $response = ["message" =>'User does not exist'];
            return response($response, 422);
        }
    }

    /**
     * Returns Authenticated User Details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details()
    {
        return response()->json(['user' => auth()->user()], 200);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->token()->revoke();
            return response()->json(['success' =>'logout_success'],200);
        }else{
            return response()->json(['error' =>'api.something_went_wrong'], 500);
        }
    }

    public function changePassword(Request $request){
        $userid = auth()->user()->id;
        $input = $request->input();
        $rules = array(
            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $arr = array("status" => 400, "message" => $validator->errors()->first(), "data" => array());
        } else {
            if ((Hash::check(request('old_password'), Auth::user()->password)) == false) {
                $arr = array("status" => 400, "message" => "Check your old password.", "data" => array());
            } else if ((Hash::check(request('new_password'), Auth::user()->password)) == true) {
                $arr = array("status" => 400, "message" => "Please enter a password which is not similar then current password.", "data" => array());
            } else {
                User::where('_id', $userid)->update(['password' => Hash::make($input['new_password'])]);
                $arr = array("status" => 200, "message" => "Password updated successfully.", "data" => array());
            }
        }
        return $arr;
    }

    public function verifyAccount(Request $request){
        $user = User::where('email', $request->email)->first();
        if(!empty($user)){
            if($user->verify_account == $request->verify_account){
                $user->unset('verify_account');
                $user->active = 1;
                $user->save();
                return response()->json(['message' =>'Account Activated Successfully.', 'activation_status'=> $user->active],200);
            }else{
                return response()->json(['message' =>'Code mismatch please try again.', 'activation_status'=> $user->active],200);
            }
        }else{
            return response()->json(['message' =>'No user found.'],200);
        }
    }

    public function resendVerifyEmail(Request $request){
        $user = User::where('email', $request->email)->where('active', 0)->first();
        if(!empty($user)){
            $this->sendVerifyEmail($user);
            return response()->json(['message' =>'Mail has been sent.'],200);
        }else{
            return response()->json(['message' =>'No user found.'],200);
        }
    }

    public function sendVerifyEmail(object $user){
        $notify = [
            'email_subject'     => 'Account Activation',
            'introduction_line' => "Thank you for registering on ". env('APP_NAME') .'.',
            'email_text'        => 'You need to activate your account in order to use our service. please click link we sent on your email address.',
            'action_text'       => 'Activate Your Account',
            'action_url'        => env('FRONT_END_LINK', 'http://localhost:3100/') . 'user/verify_account?email=' . $user->email . '&verify_token=' . $user->verify_account,
            'email_blade'       => 'mail.default'
        ];
        Notification::send($user, new CommonNotification($notify));
        return true;
    }

    public function setupUser(Request $request){
        $input = $request->input();
        $rules = array(
            'name' => 'required|min:3',
        );
        $update = User::find(auth()->user()->id);
        $arr =[];
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $arr = array("status" => 400, "message" => $validator->errors()->first());
        }else{
            $update->name = $request->name;
            $update->categories = $request->categories;
            $update->first_login = 0;
            $update->save();
            $arr = array("status" => 200, "message" => 'information Saved');
        }
        return json_encode($arr);
    }
}
