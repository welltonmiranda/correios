<?php

namespace WelltonMiranda\Correios\Services;

use GuzzleHttp\ClientInterface;
use PhpQuery\PhpQuery as phpQuery;
use WelltonMiranda\Correios\Contracts\RastrearInterface;
use WelltonMiranda\Correios\WebService;

class Rastrear implements RastrearInterface {

	/**
	 * Cliente HTTP
	 *
	 * @var \GuzzleHttp\ClientInterface
	 */
	protected $http;

	/**
	 * Código
	 *
	 * @var string
	 */
	protected $codigo;

	/**
	 * XML da requisição.
	 *
	 * @var string
	 */
	protected $body;

	/**
	 * Resposta da requisição.
	 *
	 * @var \GuzzleHttp\Psr7\Response
	 */
	protected $response;

	/**
	 * Array de resposta formatado.
	 *
	 * @var array
	 */
	protected $rastreamento;

	/**
	 * Array de resposta formatado.
	 *
	 * @var array
	 */
	protected $errors;

	/**
	 * Cria uma nova instância da classe Rastrear.
	 *
	 * @param ClientInterface $http
	 */
	public function __construct(ClientInterface $http) {
		$this->http = $http;
	}

	/**
	 * Rastrear objeto por Código.
	 *
	 * @param  string $codigo
	 *
	 * @return array
	 */
	public function find($codigo) {
		$this->setCodigo($codigo)
			->buildFormBody()
			->sendWebServiceRequest()
			->parseHTMLFromResponse();

		if ($this->hasErrorMessage()) {
			return $this->fetchErrorMessage();
		}

		return $this->fetchRastrear();
	}

	/**
	 * Seta o Código informado.
	 *
	 * @param string $codigo
	 *
	 * @return self
	 */
	protected function setCodigo($codigo) {
		$this->codigo = $codigo;

		return $this;
	}

	/**
	 * Monta o corpo da requisição em XML.
	 *
	 * @return self
	 */
	protected function buildFormBody() {

		$codigo = $this->codigo;
		$this->body = [
			'objetos' => $codigo,
		];

		return $this;
	}

	/**
	 * Envia uma requisição para o webservice dos Correios
	 * e salva a resposta para uso posterior.
	 *
	 * @return self
	 */
	protected function sendWebServiceRequest() {

		$this->response = $this->http->post(WebService::RASTREAR, [
			'http_errors' => false,
			'form_params' => $this->body,
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Cache-Control' => 'no-cache',
			],
		]);

		return $this;
	}

	/**
	 * Formata o XML do corpo da resposta.
	 *
	 * @return self
	 */
	protected function parseHTMLFromResponse() {

		$html = $this->response->getBody()->getContents();

		phpQuery::newDocumentHTML($html, $charset = 'utf-8');

		$c = 0;

		$this->rastreamento = [];

		foreach (phpQuery::pq('tr') as $tr):

			$c++;

			if (count(phpQuery::pq($tr)->find('td')) == 2):

				list($data, $hora, $local) = explode("<br>", phpQuery::pq($tr)->find('td:eq(0)')->html());

				list($status, $encaminhado) = explode("<br>", phpQuery::pq($tr)->find('td:eq(1)')->html());

				$this->rastreamento[] = ['data' => $data . " " . $hora, 'local' => $local, 'status' => $status];

				if ($encaminhado):

					$this->rastreamento[count($this->rastreamento) - 1]['encaminhado'] = $encaminhado;

				endif;

			endif;

		endforeach;

		if (!isset($this->rastreamento)) {
			return null;
		}

		return $this;
	}

	protected function hasErrorMessage() {

		$statusCode = $this->response->getStatusCode();

		if ($statusCode = !200):

			return true;

		else:

			return false;

		endif;

	}

	protected function fetchErrorMessage() {

		return [
			'error' => $this->messages($this->response->getStatusCode()),
		];

	}

	protected function messages($statusCode) {

		$codigo[503] = 'Serviço indisponível no momento, por favor, tente novamente mais tarde.';

		return $codigo[$statusCode];
	}

	/**
	 * Recupera endereço do XML de resposta.
	 *
	 * @return array
	 */
	protected function fetchRastrear() {

		$resultado = [];

		foreach ($this->rastreamento as $key => $rastreio):

			$resultado[] = [

				'data' => cleanString($rastreio['data']),
				'local' => cleanString($rastreio['local']),
				'status' => cleanString($rastreio['status']),
				'encaminhado' => (isset($rastreio['encaminhado']) ? cleanString($rastreio['encaminhado']) : null),

			];
		endforeach;

		return $resultado;

	}
}