<?php

namespace app\components;

class GCMService {
	// 
	public static function sendNotification($registration_ids=array(), $subject='', $message='')
	{
		$gcm_url = \Yii::$app->params['gcmUrl'];
		$api_key = \Yii::$app->params['gcmApiKey'];
		
		$headers = array(
			'Content-Type:application/json',
			"Authorization:key=$api_key"
		);

		$notification = array(
			'registration_ids'=>$registration_ids,
			'data'=> array(
				'subject'=>$subject,
				'message'=>$message,
			)
		);
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_URL, $gcm_url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($notification));
		$response = curl_exec($curl);
		curl_close($curl);
	}
}

?>