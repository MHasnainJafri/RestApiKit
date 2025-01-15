<?php

namespace Mhasnainjafri\RestApiKit\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Mhasnainjafri\RestApiKit\Notifications\SendOtpNotification; // Ensure correct User model namespace

class AuthController extends Controller
{
    private $otpTtl = 10; // OTP TTL in minutes

    private $maxOtpTries = 10;

    /**
     * Register a new user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if (config('restify.auth.provider') == 'sanctum') {
            $token = $user->createToken('API Token')->plainTextToken;
        } else {
            $token = $user->createToken('API Token')->accessToken;
        }

return response()->json([
            'message' => 'User Registered Successfully',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = null;
            if (config('restify.auth.provider') == 'sanctum') {
                $token = $user->createToken('API Token')->plainTextToken;
            } else {
                $token = $user->createToken('API Token')->accessToken;
            }

            return response()->json(['token' => $token, 'user' => $user]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Password::sendResetLink($request->only('email'));

        return response()->json(['message' => 'Password reset link sent.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'token'),
            function ($user, $password) {
                $user->password = bcrypt($password);
                $user->save();
            }
        );

        return $status == Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset successfully.'])
            : response()->json(['message' => 'Password reset failed.'], 400);
    }

    public function verifyEmail($id, $emailHash)
    {
        $user = User::find($id);

        if (! $user || ! hash_equals(sha1($user->email), $emailHash)) {
            return response()->json(['message' => 'Invalid verification link.'], 400);
        }

        $user->markEmailAsVerified();

        return response()->json(['message' => 'Email verified successfully.']);
    }

    // Cache helpers for OTP retries and OTP itself
    private function getOtpTries($email)
    {
        return Cache::get('Otp__tries_'.$email, 0);
    }

    private function incrementOtpTries($email)
    {
        $tries = $this->getOtpTries($email);
        Cache::put('Otp__tries_'.$email, $tries + 1, now()->addMinutes($this->otpTtl));

        return $tries + 1;
    }

    private function getOtp($email)
    {
        return Cache::get('otp_'.$email);
    }

    private function storeOtp($email, $otp)
    {
        Cache::put('otp_'.$email, $otp, now()->addMinutes($this->otpTtl));
    }

    private function clearOtp($email)
    {
        Cache::forget('otp_'.$email);
        Cache::forget('Otp__tries_'.$email);
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->email;

        $tries = $this->getOtpTries($email);
        if ($tries >= $this->maxOtpTries) {
            return response()->json(['message' => 'Too many OTP tries. Please try again later.'], 400);
        }

        // Generate OTP securely
        $otp = Str::random(6);  // You can replace this with any more secure OTP generation logic

        // Store OTP in cache
        $this->storeOtp($email, $otp);

        try {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->notify(new SendOtpNotification($otp));  // Sending the OTP notification
            }

            return response()->json(['message' => 'OTP sent successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP: '.$e->getMessage());

            return response()->json(['message' => 'Failed to send OTP. Please try again later.'], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|integer',
        ]);

        $tries = $this->incrementOtpTries($request->email);

        if ($tries > $this->maxOtpTries) {
            return response()->json(['message' => 'Too many OTP tries. Please try again later.'], 400);
        }

        $cachedOtp = $this->getOtp($request->email);

        if (! $cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        return response()->json(['status' => 'success', 'message' => 'OTP verified successfully']);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
            'otp' => 'required',
        ]);

        // Increment OTP tries
        $tries = $this->incrementOtpTries($request->email);

        if ($tries > $this->maxOtpTries) {
            return response()->json(['message' => 'Too many OTP tries. Please try again later.'], 400);
        }

        $cachedOtp = $this->getOtp($request->email);

        if (! $cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        $this->clearOtp($request->email);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->password = bcrypt($request->password);
            $user->save();

            return response()->json(['status' => 'success', 'message' => 'Password changed successfully']);
        }

        return response()->json(['message' => 'User not found'], 404);
    }
}
