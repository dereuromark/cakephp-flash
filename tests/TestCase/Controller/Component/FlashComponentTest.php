<?php

namespace Flash\Test\TestCase\Controller\Component;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\TestSuite\TestCase;
use TestApp\Controller\FlashComponentTestController;

/**
 */
class FlashComponentTest extends TestCase {

	/**
	 * @var \TestApp\Controller\FlashComponentTestController
	 */
	public $Controller;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->Controller = new FlashComponentTestController(new Request());
		$this->Controller->startupProcess();

		$this->Controller->request->session()->delete('Flash');
		Configure::delete('TransientFlash');
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();

		unset($this->Controller->Flash);
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

		$res = $this->Controller->request->session()->read('Flash.flash');
		$this->assertTrue(!empty($res));
		$this->assertSame('efg', $res[0]['message']);
	}

	/**
	 * @return void
	 */
	public function testMagic() {
		$this->Controller->Flash->error('Some Error Message');

		$res = $this->Controller->request->session()->read('Flash.flash');
		$this->assertTrue(!empty($res));
		$this->assertSame('Some Error Message', $res[0]['message']);
	}

	/**
	 * @return void
	 */
	public function testCoreHook() {
		$this->Controller->Flash->set('Some Message');

		$res = $this->Controller->request->session()->read('Flash.flash');
		$this->assertTrue(!empty($res));
		$this->assertSame('info', $res[0]['type']);
		$this->assertSame('Some Message', $res[0]['message']);
	}

	/**
	 * @return void
	 */
	public function testAjax() {
		$session = $this->Controller->request->session();
		$this->Controller->request = $this->getMockBuilder(Request::class)->setMethods(['is'])->getMock();
		$this->Controller->Flash->request->session($session);

		$this->Controller->Flash->success('yeah');
		$this->Controller->request->session()->write('Foo', 'bar');
		$this->Controller->Flash->transientMessage('xyz', 'warning');

		$this->Controller->request->expects($this->once())
			->method('is')
			->with('ajax')
			->will($this->returnValue(true));

		$event = new Event('Controller.startup', $this->Controller);
		$this->Controller->Flash->beforeRender($event);

		$result = $this->Controller->response->header();
		$expected = ['X-Flash' => '{"flash":[{"message":"yeah","type":"success","params":[]},{"message":"xyz","type":"warning","params":[]}]}'];
		$this->assertSame($expected, $result);
	}

}
