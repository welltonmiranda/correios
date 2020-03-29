<?php

namespace WelltonMiranda\Correios;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {

	/**
	 * Register any application services.
	 *
	 * @return void
	 */

	public function register() {

		$this->app->bind('client', function () {

			return new \WelltonMiranda\Correios\Client;

		});

	}
}