<?php

namespace WelltonMiranda\CorreiosFerramentas;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider {

	/**
	 * Register any application services.
	 *
	 * @return void
	 */

	public function register() {

		$this->app->singleton('correios_ferramentas', function () {

			return new \WelltonMiranda\CorreiosFerramentas\CorreiosFerramentas;

		});

	}

}
