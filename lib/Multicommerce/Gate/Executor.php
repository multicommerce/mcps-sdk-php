<?php

namespace Multicommerce\Gate;

class Executor {

    private $_base_url;
    private $_verifySsl;

    public function __construct($base_url, $verifySsl = true) {
	$this->_base_url = rtrim($base_url, '/') . '/';
	$this->_verifySsl = $verifySsl;
    }

    /**
     *
     * @param string $action
     * @param array $postFields
     * @return \Multicommerce\Gate\Response
     */
    public function execute($action, array $postData, Signer $signer) {

	$url = $this->_base_url . $action;
	$ch  = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_POST, 1);
	if ($this->_verifySsl) {
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	} else {
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	}

	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

	$curlResult = curl_exec($ch);

	$status = Response::S_UNKNOWN;
	if (!$curlResult) {
	    $status = Response::S_TRANSPORT_FAIL;
	    $curlResult = curl_error($ch);
	}
	curl_close($ch);

	return Response::handle($curlResult, $signer, $status);
    }   

}
