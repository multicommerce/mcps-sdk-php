<?php

namespace Multicommerce\Gate;

class Response {

    const E_UNKNOWN_CODE = 0;
    const S_UNKNOWN = 0;
    const S_SUCCESS = 1;
    const S_ERROR = 2;
    const S_TRANSPORT_FAIL = 3;
    const S_BAD_REQ = 4;

    private $_status;
    private $_code;
    private $_message;
    private $_sign_valid = false;
    private $_data = [];
    
    private function __construct($status, $data) {
	
    }

    /**
     *
     * @param type $status
     * @param type $str_resp
     * @return Multicommerce\Gate\Response
     */
    static function handle($str_resp, Signer $signer, $status = 0) {
	 
        $inst = new self();
        if ($status === self::S_TRANSPORT_FAIL) {
            $inst->_status = $status;
            $inst->_message = $str_resp;
        } else {
            $data = json_decode($str_resp, true);
	    
            if (isset($data['data']) && isset($data['sign'])) {
                $inst->_sign_valid = ($data['sign'] == $signer->makeSign($data['data']));

            }
            if(isset($data['data'])) {
                $data = json_decode(base64_decode($data['data']), true);
            } else {
                $data = false;
            }
	   
            if ($data === false) {
                $inst->_status = self::S_TRANSPORT_FAIL;
                $inst->_message = 'Parse response fail';
                $inst->_code = self::E_UNKNOWN_CODE;
            } else {
                $inst->_status = isset($data['success']) ? ($data['success'] ? self::S_SUCCESS : self::S_ERROR) : self::S_ERROR;
                $inst->_message = isset($data['message']) ? $data['message'] : '';
                $inst->_code = isset($data['code']) ? $data['code'] : self::E_UNKNOWN_CODE;
                if (isset($data['data'])) {
                    $inst->_data = $data['data'];
                }
            }
        }

        return $inst;
    }


    public function getValues() {
        return $this->_data;
    }

    public function getValue($name) {
        return isset($this->_data[$name]) ? $this->_data[$name] : null;
    }

    public function isSuccess() {
        return $this->_status === self::S_SUCCESS;
    }

    public function getStatus() {
        return $this->_status;
    }

    public function getMessage() {
        return $this->_message;
    }
    
    public function getCode() {
        return $this->_code;
    }

}
