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
		$this->assertSame('flash/success', $res[0]['element']);
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
		$this->assertSame('flash/info', $res[0]['element']);
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
		$this->assertSame('flash/error', $res[0]['element']);
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
		$this->Controller->getRequest()->getSession()->write('Foo', 'bar');

		$this->Controller->Flash->success('my-yes');
		$this->Controller->Flash->transientMessage('my-warning', 'warning');
		$event = new Event('Controller.startup', $this->Controller);
		$this->Controller->Flash->beforeRender($event);

		$result = $this->Controller->getResponse()->getHeaders();
		$expected = [
			'Content-Type' => ['text/html; charset=UTF-8'],
			'X-Flash' => ['[{"message":"my-yes","type":"success","params":[]},{"message":"my-warning","type":"warning","params":[]}]'],
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
		$this->assertSame('flash/error', $res[0]['element']);
		$this->assertFalse($res[0]['params']['escape']);
	}

	/**
	 * @return void
	 */
	public function testBeforeRedirect() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$this->Controller->Flash->setConfig('headerOnRedirect', true);
		$this->Controller->Flash->success('redirect-success');
		$this->Controller->Flash->transientMessage('redirect-warning', 'warning');

		$event = new Event('Controller.beforeRedirect', $this->Controller, [
			'url' => '/some/path',
			'response' => $this->Controller->getResponse(),
		]);
		$this->Controller->Flash->beforeRedirect($event, '/some/path', $this->Controller->getResponse());

		$result = $this->Controller->getResponse()->getHeaders();
		$expected = [
			'Content-Type' => ['text/html; charset=UTF-8'],
			'X-Flash' => ['[{"message":"redirect-success","type":"success","params":[]},{"message":"redirect-warning","type":"warning","params":[]}]'],
		];
		$this->assertSame($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testBeforeRedirectNonAjax() {
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);

		$this->Controller->Flash->setConfig('headerOnRedirect', true);
		$this->Controller->Flash->success('redirect-success');

		$event = new Event('Controller.beforeRedirect', $this->Controller, [
			'url' => '/some/path',
			'response' => $this->Controller->getResponse(),
		]);
		$this->Controller->Flash->beforeRedirect($event, '/some/path', $this->Controller->getResponse());

		$result = $this->Controller->getResponse()->getHeaders();
		$expected = [
			'Content-Type' => ['text/html; charset=UTF-8'],
		];
		$this->assertSame($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testBeforeRedirectDisabledByDefault() {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		// headerOnRedirect is false by default
		$this->Controller->Flash->success('redirect-success');
		$this->Controller->Flash->transientMessage('redirect-warning', 'warning');

		$event = new Event('Controller.beforeRedirect', $this->Controller, [
			'url' => '/some/path',
			'response' => $this->Controller->getResponse(),
		]);
		$this->Controller->Flash->beforeRedirect($event, '/some/path', $this->Controller->getResponse());

		$result = $this->Controller->getResponse()->getHeaders();
		$expected = [
			'Content-Type' => ['text/html; charset=UTF-8'],
		];
		$this->assertSame($expected, $result);
	}

}
