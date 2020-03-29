<?php

namespace WelltonMiranda\Correios;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use WelltonMiranda\Correios\Contracts\CepInterface;
use WelltonMiranda\Correios\Contracts\FreteInterface;
use WelltonMiranda\Correios\Services\Cep;
use WelltonMiranda\Correios\Services\Frete;
use WelltonMiranda\Correios\Services\Rastrear;

class Client {
	
	/**
	 * Serviço de frete.
	 *
	 * @var \WelltonMiranda\Correios\Contracts\FreteInterface
	 */
	protected $frete;

	/**
	 * Serviço de CEP.
	 *
	 * @var \WelltonMiranda\Correios\Contracts\CepInterface
	 */
	protected $cep;

	/**
	 * Serviço de Rastreio.
	 *
	 * @var \WelltonMiranda\Correios\Contracts\RastrearInterface
	 */
	protected $rastrear;

	/**
	 * Cria uma nova instância da classe Client.
	 *
	 * @param \GuzzleHttp\ClientInterface|null  $http
	 * @param \WelltonMiranda\Correios\Contracts\FreteInterface|null $frete
	 * @param \WelltonMiranda\Correios\Contracts\CepInterface|null $cep
	 */
	public function __construct(
		ClientInterface $http = null,
		FreteInterface $frete = null,
		CepInterface $cep = null,
		RastrearInterface $rastrear = null
	) {
		$this->http = $http ?: new HttpClient;
		$this->frete = $frete ?: new Frete($this->http);
		$this->cep = $cep ?: new Cep($this->http);
		$this->rastrear = $rastrear ?: new Rastrear($this->http);
	}

	/**
	 * Serviço de frete dos Correios.
	 *
	 * @return \WelltonMiranda\Correios\Contracts\FreteInterface
	 */
	public function frete() {
		return $this->frete;
	}

	/**
	 * Serviço de CEP dos Correios.
	 *
	 * @return \WelltonMiranda\Correios\Contracts\CepInterface
	 */
	public function cep() {
		return $this->cep;
	}

	/**
	 * Serviço de Rastreio dos Correios.
	 *
	 * @return \WelltonMiranda\Correios\Contracts\CepInterface
	 */
	public function rastrear() {
		return $this->rastrear;
	}
}