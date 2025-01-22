<?php
namespace Mhasnainjafri\RestApiKit\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Mhasnainjafri\RestApiKit\Helpers\OtpManager;
use Mhasnainjafri\RestApiKit\Notifications\SendOtpNotification;

class AuthController extends Controller 
{
    private $otpTtl;
    private $maxOtpTries;

    /**
     * AuthController constructor.
     *
     * Initialize the controller, injecting the OtpManager instance and
     * setting the middleware to prevent authenticated users from accessing
     * the login and register routes.
     */
    public function __construct(private OtpManager $otpManager)
    {
        // Fetch OTP TTL and max tries from config
        $this->otpTtl = config('restify.auth.otp.ttl');
        $this->maxOtpTries = config('restify.auth.otp.max_tries');
    }

    /**
     * Handle a registration request for the application.
     */
    public function register(Request $request)
    {
        // Define validation rules dynamically based on the required fields for different scenarios
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ];
    
        // Dynamically add additional validation rules if provided in the config or elsewhere
        if (config('restify.auth.custom_registration_fields') && is_array(config('restify.auth.custom_registration_fields'))) {
            foreach (config('restify.auth.custom_registration_fields') as $field => $rules) {
                $validationRules[$field] = $rules;
            }
        }
    
        // Validate incoming request
        $validatedData = $request->validate($validationRules);
    
        // Create user with validated data
       
    
        $user = User::create($validatedData);
    
        // Determine the correct token provider based on config
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
    

    /**
     * Handle a login request for the application.
     */
    public function login(Request $request)
    {
        // Define dynamic validation rules based on the input type
        $validationRules = [
            'password' => 'required|string', // Password is always required
            'remember' => ['sometimes', 'boolean'], // Remember me is optional
        ];
    
        // Check if phone number or email is provided for login
        if (config('restify.auth.login_with') == 'email') {
            $validationRules['email'] = 'required|email';
        }else{
            $validationRules[config('restify.auth.login_with')] = 'required';
        }
    
      
    
        // Validate the incoming data
        $credentials = $request->validate($validationRules);
    
        // Attempt login based on provided credentials
        if ($request->has('email')) {
            $credentials = [
                'email' => $request->email,
                'password' => $request->password,
            ];
        } elseif ($request->has('phone_number')) {
            // Attempt login using phone number and password if provided
            $credentials = [
                'phone_number' => $request->phone_number,
                'password' => $request->password,
            ];
        }
    
        // Check the login credentials
        if (Auth::attempt($credentials, $request->remember)) {
            $user = Auth::user();
            $token = null;
    
            // Generate token based on config provider
            if (config('restify.auth.provider') == 'sanctum') {
                $token = $user->createToken('API Token')->plainTextToken;
            } else {
                $token = $user->createToken('API Token')->accessToken;
            }
    
            return response()->json(['token' => $token, 'user' => $user]);
        }
    
        return response()->json(['message' => 'Invalid credentials'], 401);
    }
    

    /**
     * Revoke the authenticated user's token and log them out.
     */
    public function logout()
    {
        if (config('restify.auth.provider') == 'sanctum') {
            auth()->user()->tokens()->delete();
        } else {
            auth()->logout();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Handle a forgot password request.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Password::sendResetLink($request->only('email'));

        return response()->json(['message' => 'Password reset link sent.']);
    }

    /**
     * Handle a password reset request.
     */
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

    /**
     * Handle an email verification request.
     */
    public function verifyEmail($id, $emailHash)
    {
        $user = User::find($id);

        if (! $user || ! hash_equals(sha1($user->email), $emailHash)) {
            return response()->json(['message' => 'Invalid verification link.'], 400);
        }

        $user->markEmailAsVerified();

        return response()->json(['message' => 'Email verified successfully.']);
    }

    /**
     * Send OTP to the user.
     */
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->email;
        $tries = $this->otpManager->getOtpTries($email);

        if ($tries >= $this->maxOtpTries) {
            return response()->json(['message' => 'Too many OTP tries. Please try again later.'], 400);
        }

        $otp = $this->otpManager->generateOtp();


        // Store OTP in cache
        $this->otpManager->storeOtp($email, $otp);

        try {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->notify(new SendOtpNotification($otp));
            }

            $response = [
                'message' => 'OTP sent successfully',
            ];

            // Include OTP in response if APP_DEBUG is true
            if (config('restify.APP_DEBUG') == true) {
                $response['otp'] = $otp;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP: '.$e->getMessage());
            return response()->json(['message' => 'Failed to send OTP. Please try again later.'], 500);
        }
    }

    /**
     * Verify the OTP entered by the user.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
        ]);

        $tries = $this->otpManager->incrementOtpTries($request->email);

        if ($tries > $this->maxOtpTries) {
            return response()->json(['message' => 'Too many OTP tries. Please try again later.'], 400);
        }

        $cachedOtp = $this->otpManager->getOtp($request->email);

        if (! $cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        return response()->json(['status' => 'success', 'message' => 'OTP verified successfully']);
    }

    /**
     * Change the user's password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
            'otp' => 'required',
        ]);

        // Increment OTP tries
        $tries = $this->otpManager->incrementOtpTries($request->email);

        if ($tries > $this->maxOtpTries) {
            return response()->json(['message' => 'Too many OTP tries. Please try again later.'], 400);
        }

        $cachedOtp = $this->otpManager->getOtp($request->email);

        if (! $cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        $this->otpManager->clearOtp($request->email);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->password = bcrypt($request->password);
            $user->save();

            return response()->json(['status' => 'success', 'message' => 'Password changed successfully']);
        }

        return response()->json(['message' => 'User not found'], 404);
    }
}
