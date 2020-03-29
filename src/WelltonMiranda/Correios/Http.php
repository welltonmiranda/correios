<?php

namespace WelltonMiranda\Correios;

use GuzzleHttp\Exception\RequestException;

class Http {

	function get($url, $query = [], $headers = []) {

		try {

			$client = new \GuzzleHttp\Client();

			$response = $client->request('GET', $url, [

				'headers' => $headers,

				'query' => $query,

			]);

			$body = $response->getBody();

		} catch (RequestException $e) {

			//report($e);

			//\Log::critical(Psr7\str($e->getRequest()));

			//if ($e->hasResponse()) {
			//	\Log::critical(Psr7\str($e->getResponse()));
			//}

			$response = $e->getResponse();

			$body = $response->getBody();

		}

		return $body;

	}


}