<?php

namespace WelltonMiranda\Correios\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider {

	/**
	 * Register any application services.
	 *
	 * @return void
	 */

	public function register() {

		$this->app->singleton('correios', function () {

			return new \WelltonMiranda\Correios\Client;

		});

	}

}
