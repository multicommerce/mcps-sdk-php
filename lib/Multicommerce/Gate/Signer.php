<?php

namespace Multicommerce\Gate;

class Signer {

    private $_hash_algo;
    private $_key;

    const HA_SHA1 = 'sha1';
    const HA_SHA256 = 'sha256';
    const HA_SHA512 = 'sha512';

    static $avail_hash_algos = [
	self::HA_SHA1,
	self::HA_SHA256,
	self::HA_SHA512,
    ];

    public function __construct($hash_algo, $key) {
	if (!in_array($hash_algo, self::$avail_hash_algos)) {
	    throw new \InvalidArgumentException('Unsuported hash algorithm. Available: ', join(', ', self::$avail_hash_algos));
	}
	$this->_hash_algo = $hash_algo;
	$this->_key = $key;
    }

    public function makeSign($str) {
	return hash($this->_hash_algo, $this->_key . $str . $this->_key);
    }

}
