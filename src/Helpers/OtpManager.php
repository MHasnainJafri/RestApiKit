<?php

namespace Mhasnainjafri\RestApiKit\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class OtpManager
{
    private $otpTtl;

/**
 * OtpManager constructor.
 *
 * Initializes the OtpManager by setting the OTP time-to-live (TTL) value
 * from the configuration.
 */

    public function __construct()
    {
        $this->otpTtl = config('restify.auth.otp.ttl');
    }

    /**
     * Returns the number of OTP tries for the given email address.
     *
     * The value is stored in cache with the key 'Otp__tries_'.$email and has
     * a default value of 0.
     *
     * @param string $email The email address to retrieve the OTP tries for.
     * @return int The number of OTP tries.
     */
    public function getOtpTries($email)
    {
        return Cache::get('Otp__tries_'.$email, 0);
    }

    /**
     * Increments the number of OTP tries for the given email address.
     *
     * The incrementing is done atomically and the new value is returned.
     *
     * @param string $email The email address to increment the OTP tries for.
     * @return int The new number of OTP tries.
     */
    public function incrementOtpTries($email)
    {
        $tries = $this->getOtpTries($email);
        Cache::put('Otp__tries_'.$email, $tries + 1, now()->addMinutes($this->otpTtl));

        return $tries + 1;
    }

    /**
     * Retrieves the OTP value stored in cache for the given email address.
     *
     * The OTP value is stored in cache with the key 'otp_'.$email.
     *
     * @param string $email The email address to retrieve the OTP value for.
     * @return string The OTP value, or null if no OTP value is found.
     */
    public function getOtp($email)
    {
        return Cache::get('otp_'.$email);
    }

    /**
     * Stores the OTP for the given email address in cache.
     *
     * The OTP is stored with a key in the format 'otp_' followed by the email.
     * The OTP has a time-to-live (TTL) duration specified by the configuration.
     *
     * @param string $email The email address to associate with the OTP.
     * @param string $otp The OTP value to be stored.
     */

    /**
     * Stores the OTP for the given email address in cache.
     *
     * The OTP is stored with a key in the format 'otp_' followed by the email.
     * The OTP has a time-to-live (TTL) duration specified by the configuration.
     *
     * @param string $email The email address to associate with the OTP.
     * @param string $otp The OTP value to be stored.
     */
    public function storeOtp($email, $otp)
    {
        Cache::put('otp_'.$email, $otp, now()->addMinutes($this->otpTtl));
    }

    /**
     * Clears the OTP and OTP tries for the given email address from cache.
     *
     * This method is used to reset the OTP and OTP tries after a successful
     * password reset or change password request.
     *
     * @param string $email The email address to clear the OTP and OTP tries for.
     */
    public function clearOtp($email)
    {
        Cache::forget('otp_'.$email);
        Cache::forget('Otp__tries_'.$email);
    }

    /**
     * Generates a one-time password (OTP) of the configured length.
     *
     * The type of OTP is determined by the 'restify.auth.otp.type' configuration
     * value. If the type is 'integer', a random integer with the configured
     * length is generated. If the type is 'string', a random string of the
     * configured length is generated.
     *
     * @return string|int The generated OTP.
     */
    public function generateOtp(): string|int
    {
        $otpLength = config('restify.auth.otp.length');
        $otpType = config('restify.auth.otp.type');

        if ($otpType == 'integer') {
            $min = pow(10, $otpLength - 1);
            $max = pow(10, $otpLength) - 1;
            return random_int($min, $max);
        } else {
            return Str::random($otpLength);
        }
    }
}
