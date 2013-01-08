<?php
/**
 * @desc encrypt class
 * @author Andrew Lee<tinray1024@gmail.com>
 * @link 1024(at)w(dot)cn
 * @version 0.10
 * @since 16:07 3/18/2011
 */
defined('SYS_ROOT') || die('Access denied');

class encrypt {

    /**
     * @access private
     * @desc private vars
     */
    private

        /**
         * @var salt
         */
        $salt = 'o/<^>%$#wnxnLii_){]}i32+09-**&&^(01-.?<!@`;asdkc":L:ap;',

        /**
         * @var position
         * @desc key less than 20 as much as possible, sum of value must be ten
         */
        $position = array(
            1 => 3,
            5 => 1,
            8 => 2,
            12 => 1,
            13 => 2,
            20 => 1
        ),

        /**
         * @var expire
         * @desc default 3600 seconds
         */
        $expire = 0; //3600;


    /**
     * @method parse
     * @access public
     * @desc encrypt string or parse string
     * @param string $string
     * @param boolen $isEncrypt default TRUE
     * @return mixed
     */
    public function parse($string, $isEncrypt = TRUE) {

        $salt = md5($this->salt);

        ksort($this->position);

        /**
         * @desc encrypt
         */
        if (TRUE === $isEncrypt) {
            $time = $_SERVER['REQUEST_TIME'] + $this->expire;

            $sources = array('token' => NULL, 'time' => NULL);
            $cutLength = 0;
            $prevKey = 0;

            $string .= $salt;

            foreach ($this->position as $k=>$v) {
                $sources['token'][$k] = substr($string, $cutLength, $k-$prevKey);
                $cutLength += $k-$prevKey;
                $prevKey = $k;

                if (isset($sources['time'][$k-1]))
                    $sources['time'][$k] = str_replace($sources['time'][$k-1], '', substr($time, 0, $v));
                else
                    $sources['time'][$k] = substr($time, 0, $v);

                $time = substr($time, $v);
            }

            $newToken = '';

            foreach ($sources['token'] as $k=>$s) 
                $newToken .= $s. $sources['time'][$k];

            return $newToken. substr($string, $cutLength);
        }

        /**
         * @desc parse
         */
        else {
            $time = '';

            foreach ($this->position as $k=>$v) {
                $time .= substr($string, $k, $v);
                $string = substr($string, 0, $k). substr($string, $k+$v);
            }

            if (substr($string, -32) != $salt)
                return array('token' => NULL, 'time' => NULL);

            $string = substr($string, 0, strlen($string)-32);
        
            return array('token' => $string, 'time' => $time);
        }
    }


    /**
     * @method setSalt
     * @desc set salt code
     * @access public
     * @param string $salt
     * @return void
     */
    public function setSalt($salt) {
        $this->salt = $salt;
    }


    /**
     * @method setPosition
     * @desc set chars insert position
     * @access public
     * @param array $position
     * @return void
     */
    public function setPosition($position) {
        $this->position = $position;
    }


    /**
     * @method setExpire
     * @desc set token expire relative
     * @access public
     * @param integer $expire
     * @return void
     */
    public function setExpire($expire) {
        $this->expire = $expire;
    }
}
