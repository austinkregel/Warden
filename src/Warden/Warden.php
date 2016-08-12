<?php

namespace Kregel\Warden;

class Warden
{
    /**
     * This will look through the input and remove any unset values.
     *
     * @param $input
     *
     * @return mixed
     */
    public static function clearInput($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                if (!isset($value) || $value === '') {
                    unset($input[$key]);
                }
            }

            return $input;
        }

        return $input;
    }

    /**
     * @return string
     */
    public static function generateUUID()
    {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
