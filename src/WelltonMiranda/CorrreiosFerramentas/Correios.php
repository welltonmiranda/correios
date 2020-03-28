<?php

namespace WelltonMiranda\CorreiosFerramentas;

class Correios extends Http {

	function __construct() {
		$this->url = [
			'calcularPrecoPrazo' => 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo',
		];
	}

	/**
	 * Customize the outgoing response for the resource.
	 *
	 * @param  string  $cep
	 * @return array
	 */
	function calcularPrecoPrazo($cep) {

		return $this->get($this->url['calcularPrecoPrazo']);

		//return [
		//	'cep' => $cep,
		//];

	}

}