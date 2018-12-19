<?php

namespace Flash\Test\TestCase\View\Helper;

use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Flash\View\Helper\FlashHelper;

class FlashHelperTest extends TestCase {

	/**
	 * @var array
	 */
	public $fixtures = ['core.Sessions'];

	/**
	 * @var \Flash\View\Helper\FlashHelper
	 */
	public $Flash;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		Router::reload();
		$View = new View(null);
		$this->Flash = new FlashHelper($View);
	}

	/**
	 * @return void
	 */
	public function testMessage() {
		$result = $this->Flash->message('Foo & bar', 'success');
		$expected = '<div class="alert alert-success">Foo &amp; bar</div>
';
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testRender() {
		$this->Flash->addTransientMessage('Foo & bar', 'success');

		$result = $this->Flash->render();
		$expected = '<div class="alert alert-success">Foo &amp; bar</div>
';
		$this->assertEquals($expected, $result);

		$this->Flash->addTransientMessage('I am an error', 'error');
		$this->Flash->addTransientMessage('I am a warning', 'warning');
		$this->Flash->addTransientMessage('I am some info', 'info');
		$this->Flash->addTransientMessage('I am also some info');
		$this->Flash->addTransientMessage('I am sth custom', 'custom');

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
	public function testFlashWithTypes() {
		$this->Flash->addTransientMessage('I am an error', 'error');
		$this->Flash->addTransientMessage('I am a warning', 'warning');
		$this->Flash->addTransientMessage('I am some info', 'info');
		$this->Flash->addTransientMessage('I am also some info');
		$this->Flash->addTransientMessage('I am sth custom', 'custom');
		$this->Flash->addTransientMessage('I am sth custom', ['type' => 'custom', 'params' => ['class' => 'foo']]);

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
	 * TearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();

		unset($this->Flash);
	}

}
