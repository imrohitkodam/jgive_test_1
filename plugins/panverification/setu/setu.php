<?php
/**
 * @copyright	Copyright (c) 2025 PlgSetu. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Response\JsonResponse;

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * panverification - setu Plugin
 *
 */
class PlgPanverificationSetu extends CMSPlugin {

	/**
	 * Constructor.
	 *
	 * @param 	$subject
	 * @param	array $config
	 */
	function __construct(&$subject, $config = array()) {
		// call parent constructor
		parent::__construct($subject, $config);
	}
	

	protected $app;

    public function onAfterInitialise()
    {
        // Only process in the checkout page
        $input = $this->app->input;
        $task = $input->getCmd('task');

        if ($task === 'verifyPan') {
            $this->verifyPan();
        }
    }

    public function onVerifyPan($panNumber)
    {
        // Validate PAN number format (basic validation)
        if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $panNumber)) {
            return ['status' => 'error', 'message' => 'Invalid PAN number. Please enter valid PAN number'];
        }

        // API Details from plugin params
        $isSandboxMode = $this->params->get('sandbox');

        $setuApiUrl = $this->params->get('prod-base-url');
        $setuClientId = $this->params->get('prod-x-client-id');
        $setuClientSecret = $this->params->get('prod-x-client-secret');
        $setuProdInstanceId = $this->params->get('prod-x-product-instance-id');

        if ($isSandboxMode) {
            $setuApiUrl = $this->params->get('sandbox-base-url');
            $setuClientId = $this->params->get('sandbox-x-client-id');
            $setuClientSecret = $this->params->get('sandbox-x-client-secret');
            $setuProdInstanceId = $this->params->get('sandbox-x-product-instance-id');
            // echo "setuApiUrl: ".$setuApiUrl;exit;
        }

        // Make API Request
        $response = $this->callSetuApi($setuApiUrl, $setuClientId, $setuClientSecret, $setuProdInstanceId, $panNumber);

        return $response;
    }

    private function callSetuApi($url, $setuClientId, $setuClientSecret, $setuProdInstanceId, $panNumber)
    {
        $http = new Http();
		$headers  = [
			'Content-Type' => 'application/json',
			'x-client-id' => $setuClientId,
			'x-client-secret' => $setuClientSecret,
			'x-product-instance-id' => $setuProdInstanceId
		];
		$payload = ['pan' => $panNumber, "consent"=> "Y", "reason" => "Reason for verifying PAN set by the developer"];
        try {
            $response = $http->post($url, json_encode($payload), $headers);

            $data = json_decode($response->body, true);
			
            if (isset($data['verification']) && $data['verification'] === "SUCCESS") {
                return ['status' => 'success', 'message' => 'PAN verified successfully', "data"=>$data];
            } else {
                return ['status' => 'error', 'message' => 'Invalid PAN number. Please enter valid PAN number', 'details' => $data];
            }
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error during PAN verification: ' . $e->getMessage()];
        }
    }
}