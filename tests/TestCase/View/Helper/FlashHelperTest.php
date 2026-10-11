<?php

namespace Flash\Test\TestCase\View\Helper;

use BadMethodCallException;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Flash\View\Helper\FlashHelper;
use InvalidArgumentException;

class FlashHelperTest extends TestCase {

	/**
	 * @var array
	 */
	protected array $fixtures = ['core.Sessions'];

	/**
	 * @var \Flash\View\Helper\FlashHelper|null
	 */
	protected ?FlashHelper $Flash = null;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		Router::reload();
		$View = new View(null);
		$this->Flash = new FlashHelper($View);
	}

	/**
	 * TearDown method
	 *
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();

		Configure::delete('TransientFlash');
		Configure::delete('Flash');
		$this->Flash = null;
	}

	/**
	 * @return void
	 */
	public function testMessage(): void {
		$result = $this->Flash->message('Foo & bar', 'success');
		$expected = '<div class="alert alert-success">Foo &amp; bar</div>
';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testRender(): void {
		$this->Flash->transientMessage('Foo & bar', 'success');

		$result = $this->Flash->render();
		$expected = '<div class="alert alert-success">Foo &amp; bar</div>
';
		$this->assertEquals($expected, $result);

		$this->Flash->transientMessage('I am an error', 'error');
		$this->Flash->transientMessage('I am a warning', 'warning');
		$this->Flash->transientMessage('I am some info', 'info');
		$this->Flash->transientMessage('I am also some info');
		$this->Flash->transientMessage('I am sth custom', 'custom');

		$result = $this->Flash->render();

		$this->assertTextContains('alert alert-danger', $result);
		$this->assertTextContains('alert alert-warning', $result);
		$this->assertTextContains('alert alert-info', $result);
		$this->assertTextContains('custom-info', $result);
	}

	/**
	 * Test that you can define your own order or just output a subpart of
	 * the types.
	 *
	 * @return void
	 */
	public function testFlashWithTypes(): void {
		$this->Flash->transientError('I am an error');
		$this->Flash->transientWarning('I am a warning');
		$this->Flash->transientInfo('I am some info');
		$this->Flash->transientMessage('I am also some info');
		$this->Flash->transientMessage('I am sth custom', 'custom');
		$this->Flash->transientMessage('I am sth custom', ['type' => 'custom', 'params' => ['class' => 'foo']]);

		$result = $this->Flash->render('flash', ['types' => ['warning', 'error']]);
		$expected = '<div class="alert alert-danger">I am an error</div>
<div class="alert alert-warning">I am a warning</div>
';
		$this->assertEquals($expected, $result);

		$result = $this->Flash->render('flash', ['types' => ['info']]);
		$expected = '<div class="alert alert-info">I am some info</div>
<div class="alert alert-info">I am also some info</div>
';
		$this->assertEquals($expected, $result);

		$result = $this->Flash->render();
		$expected = '<div class="custom-info">I am sth custom</div>
<div class="custom-info foo">I am sth custom</div>
';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testTransientRequiresType(): void {
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Method transient() does not exist. Select a type, e.g. transientInfo().');

		$this->Flash->transient('Missing type');
	}

	public function testMagicTransientOptionsReachTheElement(): void {
		$this->Flash->transientSuccess('<b>Message</b>', ['escape' => false, 'key' => 'custom']);

		$this->assertFalse(Configure::read('TransientFlash.custom.0.params.escape'));
		$this->assertStringContainsString('<b>Message</b>', $this->Flash->render('custom'));
	}

	public function testConfiguredKeyIsUsedByDefault(): void {
		$this->Flash->setConfig('key', 'custom');
		$this->Flash->transientInfo('Custom message');

		$this->assertStringContainsString('Custom message', $this->Flash->render());
		$this->assertNull(Configure::read('TransientFlash.custom'));
	}

	public function testCoreMessageTypeCanBeFiltered(): void {
		$view = new View(null);
		$view->getRequest()->getSession()->write('Flash.flash', [
			[
				'message' => 'Core message',
				'element' => 'flash/error',
				'key' => 'flash',
				'params' => [],
			],
		]);
		$helper = new FlashHelper($view);

		$this->assertStringContainsString('Core message', $helper->render('flash', ['types' => ['error']]));
		$this->assertEmpty($view->getRequest()->getSession()->read('Flash.flash'));
	}

	public function testArrayOptionsWithoutTypeUseInfo(): void {
		$this->assertStringContainsString('<b>Direct</b>', $this->Flash->message('<b>Direct</b>', ['escape' => false]));
		$this->Flash->transientMessage('Queued', ['key' => 'custom']);

		$this->assertStringContainsString('Queued', $this->Flash->render('custom'));
	}

	public function testInvalidLimitFailsWhenHelperLoads(): void {
		$this->expectException(InvalidArgumentException::class);

		new FlashHelper(new View(), ['limit' => 0]);
	}

	public function testSharedConfigurationAppliesToHelperTransientMessages(): void {
		Configure::write('Flash', ['key' => 'shared', 'limit' => 1, 'order' => ['info', 'error']]);
		$helper = new FlashHelper(new View());
		$helper->transientInfo('first');
		$helper->transientInfo('second');

		$this->assertSame(['info', 'error'], $helper->getConfig('order'));
		$this->assertSame(['second'], array_column(Configure::read('TransientFlash.shared'), 'message'));
		$this->assertStringContainsString('second', $helper->render());
	}

}
