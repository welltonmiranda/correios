<?php

namespace WelltonMiranda\Correios\Contracts;

interface CepInterface {
	
	/**
	 * Encontrar endereço por CEP.
	 *
	 * @param  string $cep
	 *
	 * @return array
	 */

	public function find($cep);

}