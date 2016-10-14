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
	 * @var array
	 */
	public $components = ['Flash.Flash'];

}
