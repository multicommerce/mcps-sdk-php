<?php

namespace Multicommerce\Gate;
use Exception;

class Client {

    private $_uuid;
    private $_api_url;
    private $_verifySsl;
    private $_executor = null;

    /**
     *
     * @var Multicommerce\Gate\Signer
     */
    private $_signer = null;

    /**
     *
     * @var  Multicommerce\Gate\Client
     */
    private static $_instance = null;

    /**
     *
     * @param array $params
     * @return \Multicommerce\Gate\Client
     * @throws \InvalidArgumentException
     */
    static function initInstance(array $params) {
	if (!isset($params['point_uuid']) || !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89aAbB][a-f0-9]{3}-[a-f0-9]{12}$/', $params['point_uuid'])) {
	    throw new \InvalidArgumentException('Bad format for uuid field', 100);
	}
	if (!isset($params['key']) || strlen($params['key']) < 8) {
	    throw new \InvalidArgumentException('Bad format for key field', 200);
	}
	if (!isset($params['api_url']) || !preg_match('#^https?://.+$#', $params['api_url'])) {
	    throw new \InvalidArgumentException('Bad format api url', 300);
	}
	$hash_algo = '';
	if (isset($params['hash_algo'])) {
	    $hash_algo = $params['hash_algo'];
	    if (!in_array($hash_algo, Signer::$avail_hash_algos)) {
		throw new \InvalidArgumentException('Unknown hash algoritm. Avail: ' . join(', ', Signer::$avail_hash_algos), 300);
	    }
	} else {
	    $hash_algo = Signer::HA_SHA256;
	}


	self::$_instance = new self;
	self::$_instance->_uuid = $params['point_uuid'];

	self::$_instance->_signer = new Signer($hash_algo, $params['key']);

	self::$_instance->_api_url = $params['api_url'];

	self::$_instance->_verifySsl = isset($params['verify_ssl']) ? $params['verify_ssl'] : true;

	return self::$_instance;
    }

    /**
     *
     * @return Client
     * @throws Exception
     */
    public static function getInstance() {
	if (is_null(self::$_instance)) {
	    throw new Exception('Instance not init');
	}
	return self::$_instance;
    }

    /**
     *
     * @return Executor
     */
    private function getExecutor() {
	if (is_null($this->_executor)) {
	    $this->_executor = new Executor($this->_api_url, $this->_verifySsl);
	}
	return $this->_executor;
    }

    private function __construct() {
	//die($this->_api_url);
//	$this->_executor =  new Executor($this->_api_url, $this->_verifySsl);
    }

    private function buildPacket($params) {
	$data = $this->buildData($params);
	$sign = $this->_signer->makeSign($data);
	return ['data' => $data, 'sign' => $sign];
    }

    private function buildData($data) {
	$data['point_uuid'] = $this->_uuid;
	return base64_encode(json_encode($data));
    }

    /**
     * 
     * @param array $data
     * @return \Multicommerce\Gate\Response
     */
    public function init(array $data) {
	return $this->getExecutor()->execute('init', $this->buildPacket($data), $this->_signer);
    }

    /**
     * 
     * @param string $payment_uuid
     * @return \Multicommerce\Gate\Response
     */
    public function statusByPaymentUUID($payment_uuid) {
	return $this->getExecutor()->execute(
		'statusByPaymentUUID', $this->buildPacket(['payment_uuid' => $payment_uuid]), $this->_signer
	);
    }

    /**
     * 
     * @param string $id
     * @return \Multicommerce\Gate\Response
     */
    public function statusByOrderId($id) {
	return $this->getExecutor()->execute(
		'statusByOrderId', $this->buildPacket(['id' => $id]), $this->_signer
	);
    }

    /**
     * 
     * @param string $data
     * @return \Multicommerce\Gate\Response
     */
    public function parseIncomingData($data) {
	return \Multicommerce\Gate\Response::handle($data, $this->_signer);
    }

}
