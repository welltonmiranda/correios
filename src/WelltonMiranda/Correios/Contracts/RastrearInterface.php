<?php

namespace WelltonMiranda\Correios\Contracts;

interface RastrearInterface {
	
	/**
	 * Encontrar endereço por CEP.
	 *
	 * @param  string $cep
	 *
	 * @return array
	 */

	public function find($codigo);

}