<?php

namespace PHPAnt\Core;

class UserAuthenticator implements UserAuthenticator {

    const PBKDF2_HASH_ALGORITHM = "sha256";
    const PBKDF2_ITERATIONS = 1000;
    const PBKDF2_SALT_BYTE_SIZE = 24;
    const PBKDF2_HASH_BYTE_SIZE = 24;

    const HASH_SECTIONS = 4;
    const HASH_ALGORITHM_INDEX =  0;
    const HASH_ITERATION_INDEX = 1;
    const HASH_SALT_INDEX = 2;
    const HASH_PBKDF2_INDEX = 3;
    
    /**
    * Creates a salted hash from a given password.
    * @param string $password The password we are generating the hash from.
    * @return void
    **/
    
    public function createHash($password) {
            // format: algorithm:iterations:salt:hash
            $salt = base64_encode(mcrypt_create_iv(self::PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));
            return self::PBKDF2_HASH_ALGORITHM . ":" . self::PBKDF2_ITERATIONS . ":" .  $salt . ":" . 
        base64_encode($this->pbkdf2(
                self::PBKDF2_HASH_ALGORITHM,
                $password,
                $salt,
                self::PBKDF2_ITERATIONS,
                self::PBKDF2_HASH_BYTE_SIZE,
                true
        ));
    }

    /**
    * Validates a given password against the hash in the database
    *
    * @param string $password The password we are submitting for verification.
    * @param string $correct_hash The hash we should get if the password is correct.
    **/ 
    
    private function validatePassword($password, $correct_hash) {
        $params = explode(":", $correct_hash);
        if(count($params) < self::HASH_SECTIONS) {
             return false; 
        }

        $pbkdf2 = base64_decode($params[self::HASH_PBKDF2_INDEX]);
        
        return $this->slowEquals(
            $pbkdf2,
            $this->pbkdf2(
                $params[self::HASH_ALGORITHM_INDEX],
                $password,
                $params[self::HASH_SALT_INDEX],
                (int)$params[self::HASH_ITERATION_INDEX],
                strlen($pbkdf2),
                true
                )
            );
    }

    /**
    * Compares two strings $a and $b in length-constant time.
    * @param string @a First argument to compare.
    * @param string @b Second argument to compare.
    * @return boolean 
    **/
    
    private function slowEquals($a, $b) {
        $diff = strlen($a) ^ strlen($b);
        for($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $diff === 0; 
    }

    /**
     * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
     *
     * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt. This implementation of PBKDF2 was originally created by https://defuse.ca. With improvements by http://www.variations-of-shadow.com
     * 
     * @param const $algorithm The hash algorithm to use. Recommended: SHA256
     * @param string $password The password.
     * @param string $salt A salt that is unique to the password.
     * @param integer $count Iteration count. Higher is better, but slower. Recommended: At least 1000.
     * @param double $key_length The length of the derived key in bytes.
     * @param boolean $raw_output If true, the key is returned in raw binary format. Hex encoded otherwise.
     *
     * @return string A $key_length-byte key derived from the password and salt.
     **/

    private function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false) {
        $algorithm = strtolower($algorithm);
    
        if(!in_array($algorithm, hash_algos(), true)) {
            die('PBKDF2 ERROR: Invalid hash algorithm.');
        }
    
        if($count <= 0 || $key_length <= 0) {
            die('PBKDF2 ERROR: Invalid parameters.');
        }

        $hash_length = strlen(hash($algorithm, "", true));
        $block_count = ceil($key_length / $hash_length);

        $output = "";
        for($i = 1; $i <= $block_count; $i++) {
            // $i encoded as 4 bytes, big endian.
            $last = $salt . pack("N", $i);
            // first iteration
            $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
            // perform the other $count - 1 iterations
            for ($j = 1; $j < $count; $j++) {
                $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
            }
            $output .= $xorsum;
        }

        if($raw_output) {
            return substr($output, 0, $key_length);
        } else {
            return bin2hex(substr($output, 0, $key_length));
        }
    }	
}