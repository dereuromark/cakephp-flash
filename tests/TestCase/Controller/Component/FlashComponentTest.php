<?php

namespace Flash\Test\TestCase\Controller\Component;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use TestApp\Controller\FlashComponentTestController;

class FlashComponentTest extends TestCase {

	/**
	 * @var \TestApp\Controller\FlashComponentTestController
	 */
	protected $Controller;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->Controller = new FlashComponentTestController(new ServerRequest());
		$this->Controller->startupProcess();

		$this->Controller->getRequest()->getSession()->delete('Flash');
		Configure::delete('TransientFlash');
	}

	/**
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();

		unset($this->Controller);
	}

	/**
	 * @return void
	 */
	public function testTransientMessage() {
		$this->Controller->Flash->transientMessage('xyz', 'success');

		$res = Configure::read('TransientFlash.flash');
		$this->assertTrue(!empty($res));
		$this->assertSame('success', $res[0]['type']);
		$this->assertSame('Flash/success', $res[0]['element']);
	}

	/**
	 * @return void
	 */
	public function testMessage() {
		$this->Controller->Flash->message('efg');

		$res = $this->Controller->getRequest()->getSession()->read('Flash.flash');
		$this->assertTrue(!empty($res));

		$this->assertSame('efg', $res[0]['message']);
		$this->assertSame('info', $res[0]['type']);
		$this->assertSame('Flash/info', $res[0]['element']);
	}

	/**
	 * @return void
	 */
	public function testMagic() {
		$this->Controller->Flash->error('Some Error Message');

		$res = $this->Controller->getRequest()->getSession()->read('Flash.flash');
		$this->assertTrue(!empty($res));

		$this->assertSame('Some Error Message', $res[0]['message']);
		$this->assertSame('error', $res[0]['type']);
		$this->assertSame('Flash/error', $res[0]['element']);
	}

	/**
	 * @return void
	 */
	public function testCoreHook() {
		$this->Controller->Flash->set('Some Message');

		$res = $this->Controller->getRequest()->getSession()->read('Flash.flash');
		$this->assertTrue(!empty($res));
		$this->assertSame('info', $res[0]['type']);
		$this->assertSame('Some Message', $res[0]['message']);
	}

	/**
	 * @return void
	 */
	public function testAjax() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller->Flash->success('yeah');
		$this->Controller->getRequest()->getSession()->write('Foo', 'bar');
		$this->Controller->Flash->transientMessage('xyz', 'warning');

		$event = new Event('Controller.startup', $this->Controller);
		$this->Controller->Flash->beforeRender($event);

		$result = $this->Controller->getResponse()->getHeaders();
		$expected = [
			'Content-Type' => ['text/html'],
			'X-Flash' => ['{"flash":[{"message":"yeah","type":"success","params":[]},{"message":"xyz","type":"warning","params":[]}]}'],
		];
		$this->assertSame($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testCrudFlash() {
		$this->Controller->Flash->set('efg', [
			'key' => 'flash',
			'element' => 'default',
			'params' => [
				'class' => 'message error',
				'original' => 'efg',
				'escape' => false,
			],
		]);

		$res = $this->Controller->getRequest()->getSession()->read('Flash.flash');
		$this->assertTrue(!empty($res));

		$this->assertSame('efg', $res[0]['message']);
		$this->assertSame('error', $res[0]['type']);
		$this->assertSame('Flash/error', $res[0]['element']);
		$this->assertFalse($res[0]['params']['escape']);
	}

}
