<?php

namespace WelltonMiranda\Correios;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use Contracts\CepInterface;
use Contracts\FreteInterface;
use Services\Cep;
use Services\Frete;
use Services\Rastrear;

class Client {

	/**
	 * Serviço de frete.
	 *
	 * @var \Contracts\FreteInterface
	 */
	protected $frete;

	/**
	 * Serviço de CEP.
	 *
	 * @var \Contracts\CepInterface
	 */
	protected $cep;

	/**
	 * Serviço de Rastreio.
	 *
	 * @var \Contracts\RastrearInterface
	 */
	protected $rastrear;

	/**
	 * Cria uma nova instância da classe Client.
	 *
	 * @param \GuzzleHttp\ClientInterface|null  $http
	 * @param \Contracts\FreteInterface|null $frete
	 * @param \Contracts\CepInterface|null $cep
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
	 * @return \Contracts\FreteInterface
	 */
	public function frete() {
		return $this->frete;
	}

	/**
	 * Serviço de CEP dos Correios.
	 *
	 * @return \Contracts\CepInterface
	 */
	public function cep() {
		return $this->cep;
	}

	/**
	 * Serviço de Rastreio dos Correios.
	 *
	 * @return \Contracts\CepInterface
	 */
	public function rastrear() {
		return $this->rastrear;
	}
}