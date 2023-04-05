<?php

class AUTHORIZATION
{
    
    public static function validateTimestamp($token)
    {
        $CI =& get_instance();
        $token = self::validateToken($token);
        if ($token != false && ((time() - $token->timestamp) < ($CI->config->item('tokenTimeOut') * 60))) {
            return $token;
        }
        return false;
    }
    public static function validateToken($token)
    {
        $CI =& get_instance();
        return JWT::decode($token, $CI->config->item('jwtKey'));
       
    }
    public static function generateToken($data)
    {
        $CI =& get_instance();
        return JWT::encode($data, $CI->config->item('jwtKey'));
    }
}

