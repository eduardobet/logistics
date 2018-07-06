<?php

namespace Logistics\Validator;

use Logistics\DB\User;

class CustomValidator extends \Illuminate\Validation\Validator
{
    /**
     * Validates password
     *
     * @param  array $attribute
     * @param  string $value
     * @param  array $parameters
     * @param  array $message
     * @return boolean
     */
    public function validateAlphaNumPwd($attribute, $value, $parameters, $message)
    {
        return preg_match('/^(?=.*[a-zA-Z])(?=.*[0-9])[a-zA-Z0-9]{6,}+$/i', $value);
    }

    /**
     * Validates panama one phone or multiples numbers separated by comma
     *
     * @param  array $attribute
     * @param  string $value
     * @param  array $parameters
     * @param  array $message
     * @return boolean
     */
    public function validateMassPhone($attribute, $value, $parameters, $message)
    {
        $value = trim($value);
        if ($value == '') {
            return true;
        }

        $value = explode(',', $value);

        if (array_has_dupes($value)) {
            return false;
        }

        // I don't want to validate a bunch of countries
        return true;

        foreach ($value as $val) {
            if (!is_valid_phone($val)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates one or multiple emails separated by comma
     *
     * @param  array $attribute
     * @param  string $value
     * @param  array $parameters
     * @param  array $message
     * @return boolean
     */
    public function validateMassEmail($attribute, $value, $parameters, $message)
    {
        $value = explode(',', $value);
        if (count($value) > 2) {
            return false;
        }
        if (array_has_dupes($value)) {
            return false;
        }
        foreach ($value as $key => $val) {
            if (!filter_var(trim($val), FILTER_VALIDATE_EMAIL)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate user status changing from locked to active
     *
     * @param  array $attribute
     * @param  string $value
     * @param  array $parameters
     * @param  array $message
     * @return boolean
     */
    public function validateUserStatus($attribute, $value, $parameters, $message)
    {
        $user = User::whereId(array_get($this->data, $parameters[0]))
            ->select(['status'])
            ->first();

        if (empty($user) || ($user->status == 'L' && $value != 'L')) {
            return false;
        }

        return true;
    }

    /**
     * Validates keys must not be present
     *
     * @param  array $attribute
     * @param  string $value
     * @param  array $parameters
     * @param  array $message
     * @return boolean
     */
    public function validateNotPresent($attribute, $value, $parameters, $message)
    {
        return !array_key_exists($attribute, $this->data);
    }

    public function validatePassCheck($attribute, $value, $parameters)
    {
        $dbPass = User::whereId(auth()->id())->firstOrFail(['password']);

        if (\Hash::check($value, $dbPass->password)) {
            return true;
        }
        
        return false;
    }
}
