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

		////if ($this->hasErrorMessage()) {
		//	return $this->fetchErrorMessage();
		///}

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

		$codigo = preg_replace('/[^0-9]/', null, $this->codigo);
		$this->body = [
			'Objetos' => $codigo,
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
				'cache-control' => 'no-cache',
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

		$this->rastreamento = [];

		$c = 0;

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

		if (!count($this->rastreamento)) {
			return false;
		}

		return $this;
	}

	/**
	 * Verifica se existe alguma mensagem de
	 * erro no XML retornado da requisição.
	 *
	 * @return bool
	 */
	protected function hasErrorMessage() {
		return array_key_exists('Fault', $this->parsedXML);
	}

	/**
	 * Recupera mensagem de erro do XML formatada.
	 *
	 * @return array
	 */
	protected function fetchErrorMessage() {
		return [
			'error' => $this->messages($this->parsedXML['Fault']['faultstring']),
		];
	}

	/**
	 * Mensagens de erro mais legíveis.
	 *
	 * @param  string $faultString
	 *
	 * @return string
	 */
	protected function messages($faultString) {
		return [
			'CEP INVÁLIDO' => 'CEP não encontrado',
		][$faultString];
	}

	/**
	 * Recupera endereço do XML de resposta.
	 *
	 * @return array
	 */
	protected function fetchRastrear() {

		$rastreios = $this->rastreamento;

		$resultado = [];

		foreach ($rastreios as $key => $rastreio):

			$resultado[] = [

				//'data' => preg_replace('/(\v|\s)+/', ' ', stripslashes(strip_tags($rastreio['data']))),
				///'local' => preg_replace('/(\v|\s)+/', ' ', stripslashes(strip_tags($rastreio['local']))),
				//'status' => preg_replace('/(\v|\s)+/', ' ', stripslashes(strip_tags($rastreio['status']))),
				//'encaminhado' => (isset($rastreio['encaminhado']) ? preg_replace('/(\v|\s)+/', ' ', stripslashes(strip_tags($rastreio['encaminhado']))) : null),

				'data' => cleanString($rastreio['data']),
				'local' => cleanString($rastreio['local']),
				'status' => cleanString($rastreio['status']),
				'encaminhado' => (isset($rastreio['encaminhado']) ? cleanString($rastreio['encaminhado']) : null),

			];
		endforeach;

	}
}