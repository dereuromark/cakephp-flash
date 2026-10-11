<?php

namespace Flash\Test\TestCase\Controller\Component;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Error;
use InvalidArgumentException;
use Laminas\Diactoros\Uri;
use TestApp\Controller\FlashComponentTestController;

class FlashComponentTest extends TestCase {

	/**
	 * @var \TestApp\Controller\FlashComponentTestController|null
	 */
	protected ?FlashComponentTestController $Controller = null;

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

		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
		Configure::delete('TransientFlash');
		$this->Controller = null;
	}

	/**
	 * @return void
	 */
	public function testTransientMessage(): void {
		$this->Controller->Flash->transientMessage('xyz', 'success');

		$res = Configure::read('TransientFlash.flash');
		$this->assertTrue(!empty($res));
		$this->assertSame('success', $res[0]['type']);
		$this->assertSame('flash/success', $res[0]['element']);
	}

	/**
	 * @return void
	 */
	public function testMessage(): void {
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
	public function testMagic(): void {
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
	public function testCoreHook(): void {
		$this->Controller->Flash->set('Some Message');

		$res = $this->Controller->getRequest()->getSession()->read('Flash.flash');
		$this->assertTrue(!empty($res));
		$this->assertSame('info', $res[0]['type']);
		$this->assertSame('Some Message', $res[0]['message']);
	}

	/**
	 * @return void
	 */
	public function testAjax(): void {
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
	public function testCrudFlash(): void {
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
	public function testBeforeRedirect(): void {
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
	public function testBeforeRedirectNonAjax(): void {
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
	public function testBeforeRedirectDisabledByDefault(): void {
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

	public function testTypedMessagesRespectSessionLimit(): void {
		$this->Controller->Flash->setConfig('limit', 2);
		$this->Controller->Flash->success('first');
		$this->Controller->Flash->success('second');
		$this->Controller->Flash->success('third');

		$messages = $this->Controller->getRequest()->getSession()->read('Flash.flash');
		$this->assertSame(['second', 'third'], array_column($messages, 'message'));
	}

	public function testTransientLimitRemainsExact(): void {
		$this->Controller->Flash->setConfig('limit', 2);
		foreach (['first', 'second', 'third'] as $message) {
			$this->Controller->Flash->transientInfo($message);
		}

		$this->assertSame(['second', 'third'], array_column(Configure::read('TransientFlash.flash'), 'message'));
	}

	public function testDuplicateFalseAppliesToBothMessageStores(): void {
		$this->Controller->Flash->setConfig('duplicate', false);
		$this->Controller->Flash->success('same');
		$this->Controller->Flash->success('same');
		$this->Controller->Flash->transientInfo('same');
		$this->Controller->Flash->transientInfo('same');

		$this->assertCount(1, $this->Controller->getRequest()->getSession()->read('Flash.flash'));
		$this->assertCount(1, Configure::read('TransientFlash.flash'));
	}

	public function testExplicitSetUsesTransientAjaxStorage(): void {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->Controller->Flash->set('Ajax message');

		$this->assertNull($this->Controller->getRequest()->getSession()->read('Flash.flash'));
		$this->assertSame('Ajax message', Configure::read('TransientFlash.flash.0.message'));
	}

	public function testTransientThrowableContainsOnlyItsMessageAndCode(): void {
		$this->Controller->Flash->transientMessage(new Error('Failure', 12), ['escape' => false]);

		$this->assertSame('Failure', Configure::read('TransientFlash.flash.0.message'));
		$this->assertSame(['code' => 12, 'escape' => false], Configure::read('TransientFlash.flash.0.params'));
	}

	public function testPluginElementKeepsItsMessageType(): void {
		$this->Controller->Flash->success('Plugin message', ['plugin' => 'Custom']);

		$messages = $this->Controller->getRequest()->getSession()->read('Flash.flash');
		$this->assertSame('success', $messages[0]['type']);
		$this->assertSame('Custom.flash/success', $messages[0]['element']);
	}

	public function testRedirectAcceptsUriObjects(): void {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->Controller->Flash->setConfig('headerOnRedirect', true);
		$this->Controller->Flash->success('Redirected');

		$response = $this->Controller->redirect(new Uri('/next'));

		$this->assertSame(302, $response->getStatusCode());
		$this->assertStringContainsString('Redirected', $response->getHeaderLine('X-Flash'));
	}

	public function testDirectAjaxResponseGetsHeaderAtShutdown(): void {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->Controller->Flash->success('Saved');
		$this->Controller->setResponse($this->Controller->getResponse()->withType('json')->withStringBody('{"saved":true}'));

		$this->Controller->shutdownProcess();

		$this->assertStringContainsString('Saved', $this->Controller->getResponse()->getHeaderLine('X-Flash'));
		$this->assertSame('{"saved":true}', (string)$this->Controller->getResponse()->getBody());
	}

	public function testShutdownKeepsRedirectHeadersDisabledByDefault(): void {
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->Controller->Flash->success('Saved');
		$this->Controller->redirect('/next');
		$this->Controller->shutdownProcess();

		$this->assertFalse($this->Controller->getResponse()->hasHeader('X-Flash'));
	}

	public function testDirectPluginOptionSelectsPluginElement(): void {
		$this->Controller->Flash->message('Saved', ['type' => 'success', 'plugin' => 'Custom']);
		$this->Controller->Flash->transientMessage('Preview', ['type' => 'info', 'plugin' => 'Custom']);

		$this->assertSame('Custom.flash/success', $this->Controller->getRequest()->getSession()->read('Flash.flash.0.element'));
		$this->assertSame('Custom.flash/info', Configure::read('TransientFlash.flash.0.element'));
	}

	public function testInvalidLimitFailsWhenComponentLoads(): void {
		$this->Controller->components()->unload('Flash');
		$this->expectException(InvalidArgumentException::class);

		$this->Controller->loadComponent('Flash.Flash', ['limit' => 0]);
	}

}
