<?php

namespace WelltonMiranda\Correios\Services;

use GuzzleHttp\ClientInterface;
use WelltonMiranda\Correios\Contracts\CepInterface;
use WelltonMiranda\Correios\WebService;

class Cep implements CepInterface {
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
	protected $cep;

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
	 * Encontrar endereço por CEP.
	 *
	 * @param  string $cep
	 *
	 * @return array
	 */
	public function find($cep) {
		$this->setCep($cep)
			->buildXMLBody()
			->sendWebServiceRequest()
			->parseXMLFromResponse();

		if ($this->hasErrorMessage()) {
			return $this->fetchErrorMessage();
		}

		return $this->fetchCepAddress();
	}

	/**
	 * Seta o CEP informado.
	 *
	 * @param string $cep
	 *
	 * @return self
	 */
	protected function setCep($cep) {
		$this->cep = $cep;

		return $this;
	}

	/**
	 * Monta o corpo da requisição em XML.
	 *
	 * @return self
	 */
	protected function buildXMLBody() {
		$cep = preg_replace('/[^0-9]/', null, $this->cep);
		$this->body = trim('
            <?xml version="1.0"?>
            <soapenv:Envelope
                xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                xmlns:cli="http://cliente.bean.master.sigep.bsb.correios.com.br/">
                <soapenv:Header/>
                <soapenv:Body>
                    <cli:consultaCEP>
                        <cep>' . $cep . '</cep>
                    </cli:consultaCEP>
                </soapenv:Body>
            </soapenv:Envelope>
        ');

		return $this;
	}

	/**
	 * Envia uma requisição para o webservice dos Correios
	 * e salva a resposta para uso posterior.
	 *
	 * @return self
	 */
	protected function sendWebServiceRequest() {
		$this->response = $this->http->post(WebService::SIGEP, [
			'http_errors' => false,
			'body' => $this->body,
			'headers' => [
				'Content-Type' => 'application/xml; charset=utf-8',
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
	protected function parseXMLFromResponse() {
		$xml = $this->response->getBody()->getContents();
		$parse = simplexml_load_string(str_replace([
			'soap:', 'ns2:',
		], null, $xml));

		$this->parsedXML = json_decode(json_encode($parse->Body), true);

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
	protected function fetchCepAddress() {
		$address = $this->parsedXML['consultaCEPResponse']['return'];
		$cep = preg_replace('/^([0-9]{5})([0-9]{3})$/', '${1}-${2}', $address['cep']);
		$complement = $this->getComplement($address);

		return [
			'cep' => $cep,
			'rua' => $address['end'],
			'complemento' => $complement,
			'bairro' => $address['bairro'],
			'cidade' => $address['cidade'],
			'uf' => $address['uf'],
		];
	}
}