<?php

namespace WelltonMiranda\CorreiosFerramentas;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Facade extends BaseFacade {
	protected static function getFacadeAccessor() {
		return 'correios_ferramentas';
	}
}
