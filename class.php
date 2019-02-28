<?php

class DeviceCodes {

    function __construct() {
        $this->base_api_url = "https://api.myspotlight.tv";
        $this->token = $this->api_token_check();
    }

    private function api_token_check() {
        $token = get_option("dspdl_dsp_api_token");
        $token_expiration = (int) get_option("dspdl_dsp_api_token_expiration");
        // Check to see if token expiration is too far in the future due to an earlier bug
        $future = time() + 3600*24*29;
        if (!empty($token_expiration) && is_numeric($token_expiration) && $token_expiration <= time() || ( empty($token_expiration) || empty($token) || !is_numeric($token_expiration) || $token_expiration > $future )) {
            $api_key = get_option("dspdl_dsp_api_key");
            if (empty($api_key)) return false;
            $result = dspdl_api_run_curl_command($this->base_api_url . "/token", "POST", "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"key\"\r\n\r\n$api_key\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
                array(
                    "Cache-Control: no-cache",
                    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
                )
            );
            if ($result->err) {
                $this->error .= " cURL Error: $result->err";
                return false;
            } else {
                $res = json_decode($result->response);
                if ($res->success) {
                    $token = $res->token;
                    update_option("dspdl_dsp_api_token", $token);
                    update_option("dspdl_dsp_api_token_expiration", time() + (3600 * 24 * 29));
                }
            }
        }
        return $token;
    }

	public function submit_device_code ($code) {

		$toReturn = new stdClass;
		$toReturn->success = false;
		$toReturn->message = "There was an error: ";
        $token = $this->token;
		// If we don't have a token, the API call will fail
        if(empty($token)){
        	$toReturn->message .= "No token present.";
            return $toReturn;
        }

        $customer_id = get_user_meta( get_current_user_id(), "dotstudiopro_customer_id", true);

        if (empty($customer_id)) {
        	$toReturn->message .= "No customer_id found for this user.";
            return $toReturn;
        }

        if (empty($code)) {
        	$toReturn->message .= "No code submitted to connect a device.";
            return $toReturn;
        }

        $result = dspdl_api_run_curl_command($this->base_api_url . "/device/codes/customer",
            "POST", "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"customer_id\"\r\n\r\n" . $customer_id . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"code\"\r\n\r\n" . $code . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
            array(
                "Cache-Control: no-cache",
                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                "x-access-token:".$token
            ));
        if ($result->err) {
            $this->error .= " cURL Error: $result->err";
            $toReturn->message .= $result->err;
            return $toReturn;
        } else {
        	$res = json_decode($result->response);
        	$toReturn->success = $res->success;
        	$toReturn->message = $res->error ?: $res->message;
        	return $toReturn;
        }
    }

    public function refresh_client_token($client_token) {
        $toReturn = new stdClass;
        $toReturn->success = false;
        $toReturn->message = "There was an error: ";
        $token = $this->token;
        // If we don't have a token, the API call will fail
        if(empty($token)){
            $toReturn->message .= "No token present.";
            return $toReturn;
        }

        $customer_id = get_user_meta( get_current_user_id(), "dotstudiopro_customer_id", true);

        if (empty($client_token)) {
            $toReturn->message .= "No client token sent.";
            return $toReturn;
        }

        $result = dspdl_api_run_curl_command($this->base_api_url . "/users/token/refresh",
            "POST", "",
            array(
                "Cache-Control: no-cache",
                "x-access-token:" . $token,
                "x-client-token:" . $client_token
            ));
        if ($result->err) {
            $this->error .= " cURL Error: $result->err";
            $toReturn->message .= $result->err;
            return $toReturn;
        } else {
            $res = json_decode($result->response);
            $toReturn->success = $res->success;
            if ($res->success) {
                $toReturn->client_token = $res->client_token;
            }
            return $toReturn;
        }
    }
}