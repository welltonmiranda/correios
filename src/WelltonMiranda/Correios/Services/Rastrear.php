<?php

namespace WelltonMiranda\Correios\Services;

use GuzzleHttp\ClientInterface;
use PhpQuery\PhpQuery as phpQuery;
use WelltonMiranda\Correios\WebService;

class Rastrear implements RastrearInterface {
	/**
	 * Cliente HTTP
	 *
	 * @var \GuzzleHttp\ClientInterface
	 */
	protected $http;

	/**
	 * CEP
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
	 * XML de resposta formatado.
	 *
	 * @var array
	 */
	protected $parsedXML;

	/**
	 * Cria uma nova instância da classe Cep.
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
		$this->setCodigo($cep)
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
		$codigo = preg_replace('/[^0-9]/', null, $this->codigo);
		$this->body = ['Objetos' => $codigo];

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
			'body' => $this->body,
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

		$rastreamento = [];

		$c = 0;

		foreach (phpQuery::pq('tr') as $tr):

			$c++;

			if (count(phpQuery::pq($tr)->find('td')) == 2):

				list($data, $hora, $local) = explode("<br>", phpQuery::pq($tr)->find('td:eq(0)')->html());

				list($status, $encaminhado) = explode("<br>", phpQuery::pq($tr)->find('td:eq(1)')->html());

				$rastreamento[] = ['data' => trim($data) . " " . trim($hora), 'local' => trim($local), 'status' => trim(strip_tags($status))];

				if (trim($encaminhado)):

					$rastreamento[count($rastreamento) - 1]['encaminhado'] = trim($encaminhado);

				endif;

			endif;

		endforeach;

		if (!count($rastreamento)) {
			return false;
		}

		return $rastreamento;
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
	 * Retorna complemento de um endereço.
	 *
	 * @param  array  $address
	 * @return array
	 */
	protected function getComplement(array $address) {
		$complement = [];

		if (array_key_exists('complemento', $address)) {
			$complement[] = $address['complemento'];
		}

		if (array_key_exists('complemento2', $address)) {
			$complement[] = $address['complemento2'];
		}

		return $complement;
	}

	/**
	 * Recupera endereço do XML de resposta.
	 *
	 * @return array
	 */
	protected function fetchRastrear() {

		$address = $this->parsedXML['consultaCEPResponse']['return'];
		$cep = preg_replace('/^([0-9]{5})([0-9]{3})$/', '${1}-${2}', $address['cep']);
		$complement = $this->getComplement($address);

		return [
			'data' => $this,
			'rua' => $address['end'],
			'complemento' => $complement,
			'bairro' => $address['bairro'],
			'cidade' => $address['cidade'],
			'uf' => $address['uf'],
		];
	}
}