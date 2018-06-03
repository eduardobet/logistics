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
     * Validates panama phone numbers
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

        foreach ($value as $val) {
            if (!is_valid_phone($val)) {
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
        $user = User::whereEmail(array_get($this->data, $parameters[0]))
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
}
