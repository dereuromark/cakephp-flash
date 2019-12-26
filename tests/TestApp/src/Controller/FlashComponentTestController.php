<?php

namespace TestApp\Controller;

use Cake\Controller\Controller;

/**
 * Use Controller instead of AppController to avoid conflicts
 *
 * @property \Flash\Controller\Component\FlashComponent $Flash
 */
class FlashComponentTestController extends Controller {

	/**
	 * @return void
	 */
	public function initialize(): void {
		$this->loadComponent('Flash.Flash');
	}

}
