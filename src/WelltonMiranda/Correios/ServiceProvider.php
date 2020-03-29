<?php

namespace WelltonMiranda\Correios;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {

	/**
	 * Register any application services.
	 *
	 * @return void
	 */

	public function register() {

		$this->app->singleton(Client::class, function () {

			return Client::class;

		});

	}

}
