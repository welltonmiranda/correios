<?php

namespace WelltonMiranda\Correios;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider {

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
