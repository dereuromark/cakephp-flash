<?php

namespace Flash\Controller\Component;

use BadMethodCallException;
use Cake\Controller\Component\FlashComponent as CakeFlashComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\InternalErrorException;
use Cake\Utility\Inflector;
use Exception;

/**
 * A flash component to enhance flash message support with stackable messages, both
 * persistent and transient.
 *
 * @author Mark Scherer
 * @license MIT
 *
 * @method void success(string $message, array $options = []) Set a message using "success" element
 * @method void error(string $message, array $options = []) Set a message using "error" element
 * @method void warning(string $message, array $options = []) Set a message using "warning" element
 * @method void info(string $message, array $options = []) Set a message using "info" element
 * @method void transientSuccess(string $message, array $options = []) Set a message using "success" element.
 *   These flash messages are not persisted across requests (only available for current view)
 * @method void transientError(string $message, array $options = []) Set a message using "error" element.
 *   These flash messages are not persisted across requests (only available for current view)
 * @method void transientWarning(string $message, array $options = []) Set a message using "warning" element
 *   These flash messages are not persisted across requests (only available for current view)
 * @method void transientInfo(string $message, array $options = []) Set a message using "info" element
 *   These flash messages are not persisted across requests (only available for current view)
 */
class FlashComponent extends CakeFlashComponent {

	/**
	 * @var array
	 */
	protected $_defaultConfigExt = [
		'limit' => 10, // Max message limit per key - first in, first out
		'headerKey' => 'X-Flash', // Set to empty string to disable headers for AJAX requests.
		'noSessionOnAjax' => true, // Set to false to disable auto-writing flash calls from normal flash() usage into transient collection on AJAX requests
	];

	/**
	 * @param \Cake\Controller\ComponentRegistry $registry A ComponentRegistry for this component
	 * @param array $config Array of config.
	 */
	public function __construct(ComponentRegistry $registry, array $config = []) {
		$this->_defaultConfig += $this->_defaultConfigExt;
		parent::__construct($registry, $config);
	}

	/**
	 * Called after the Controller::beforeRender(), after the view class is loaded, and before the
	 * Controller::render()
	 *
	 * @param \Cake\Event\EventInterface $event
	 * @return \Cake\Http\Response|null
	 */
	public function beforeRender(EventInterface $event) {
		/** @var \Cake\Controller\Controller $controller */
		$controller = $event->getSubject();

		if (!$this->getConfig('headerKey') || !$controller->getRequest()->is('ajax')) {
			return null;
		}

		$key = $this->getConfig('key');
		$flashMessages = $this->getFlashMessages($key);
		if (!$flashMessages) {
			return null;
		}

		$array = [];
		foreach ($flashMessages as $flashMessage) {
			$array[] = [
				'message' => $flashMessage['message'] ?? null,
				'type' => $flashMessage['type'] ?? null,
				'params' => $flashMessage['params'] ?? null,
			];
		}

		// The header can be read with JavaScript and the flash messages can be displayed
		$this->getController()->setResponse($controller->getResponse()->withHeader($this->getConfig('headerKey'), json_encode($array)));

		return null;
	}

	/**
	 * @param string $key
	 *
	 * @return array
	 */
	protected function getFlashMessages(string $key): array {
		$flashMessages = [];
		$transientFlash = (array)Configure::read('TransientFlash');

		if (!empty($transientFlash[$key])
			&& is_array($transientFlash[$key])
		) {
			$flashMessages = $transientFlash[$key];
		}

		return $flashMessages;
	}

	/**
	 * Adds a flash message.
	 * Updates "messages" session content (to enable multiple messages of one type).
	 *
	 * @param string|\Exception $message Message to output.
	 * @param array|string|null $options Options
	 * @return void
	 */
	public function message($message, $options = null): void {
		$options = $this->_mergeOptions($options);

		$this->set($message, $options);

		$this->_assertSessionStackSize($options);
	}

	/**
	 * @param string|\Exception $message
	 * @param array $options
	 *
	 * @return void
	 */
	public function set($message, array $options = []): void {
		$options = $this->_mergeOptions($options);
		$options += $this->getConfig();

		if ($message instanceof Exception) {
			if (!isset($options['params']['code'])) {
				$options['params']['code'] = $message->getCode();
			}
			$message = $message->getMessage();
		}

		if (isset($options['escape']) && !isset($options['params']['escape'])) {
			$options['params']['escape'] = $options['escape'];
		}

		[$plugin, $element] = pluginSplit($options['element']);

		if ($plugin) {
			$options['element'] = $plugin . '.flash/' . $element;
		} else {
			$options['element'] = 'flash/' . $element;
		}

		$messages = [];
		if ($options['clear'] === false) {
			$messages = (array)$this->getSession()->read('Flash.' . $options['key']);
		}

		$messages[] = [
			'type' => $options['type'],
			'message' => $message,
			'key' => $options['key'],
			'element' => $options['element'],
			'params' => $options['params'],
		];

		$this->getSession()->write('Flash.' . $options['key'], $messages);
	}

	/**
	 * @param array|string|null $options
	 *
	 * @return array
	 */
	protected function _mergeOptions($options): array {
		if (!is_array($options)) {
			$type = $options;
			if (!$type) {
				$type = 'info';
			}
			$options = [
				'type' => $type,
			];
		}

		$options = $this->_transformCrudOptions($options);

		if (isset($options['element']) && !isset($options['type'])) {
			$options['type'] = $options['element'];
		}

		$options += ['type' => 'info'];
		$options += ['element' => $options['type']];

		$options += (array)$this->getConfig();

		return $options;
	}

	/**
	 * @param array $options
	 *
	 * @return void
	 */
	protected function _assertSessionStackSize(array $options): void {
		$messages = (array)$this->getSession()->read('Flash.' . $options['key']);
		if ($messages && count($messages) > $this->getConfig('limit')) {
			array_shift($messages);
		}
		$this->getSession()->write('Flash.' . $options['key'], $messages);
	}

	/**
	 * Adds a transient flash message.
	 * These flash messages are not persisted across requests (only available for current view),
	 * will be merged into the session flash ones prior to output.
	 *
	 * @param string $message Message to output.
	 * @param array|string|null $options Options
	 * @return void
	 */
	public function transientMessage($message, $options = null) {
		$options = $this->_mergeOptions($options);

		[$plugin, $element] = pluginSplit($options['element']);

		if ($plugin) {
			$options['element'] = $plugin . '.flash/' . $element;
		} else {
			$options['element'] = 'flash/' . $element;
		}

		$messages = (array)Configure::read('TransientFlash.' . $options['key']);
		if ($messages && count($messages) > $this->getConfig('limit')) {
			array_shift($messages);
		}
		$messages[] = [
			'type' => $options['type'],
			'message' => $message,
			'key' => $options['key'],
			'element' => $options['element'],
			'params' => $options['params'],
		];
		Configure::write('TransientFlash.' . $options['key'], $messages);
	}

	/**
	 * Transforms Crud plugin flashs into Flash messages.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	protected function _transformCrudOptions(array $options) {
		if (isset($options['params']['class']) && !isset($options['type'])) {
			$class = $options['params']['class'];
			$pos = strrpos($class, ' ');
			if ($pos !== false) {
				$class = substr($class, $pos + 1);
			}
			$options['type'] = $class;
			$options['element'] = $class;
			unset($options['params']['class']);
			unset($options['params']['original']);
		}

		return $options;
	}

	/**
	 * @inheritDoc
	 * handles Flash->type (e.g. success, error) or ->transientType (e.g. transientSuccess, transientError)
	 */
	public function __call(string $name, array $args): void {
		if ($name === 'transient') {
			throw new BadMethodCallException('Method transient() does not exist. Select a type e.g. transientInfo().');
		}

		$transient = false;
		if (strpos($name, 'transient') === 0) {
			$transient = true;
			$name = substr($name, 9); // remove transient
			$type = lcfirst($name);

			if (count($args) < 1) {
				throw new InternalErrorException('Flash message missing.');
			}
		} else {
			$type = $name;
		}

		$element = Inflector::underscore($name);

		$options = ['element' => $element, 'type' => $type];

		if (!empty($args[1])) {
			if (!empty($args[1]['plugin'])) {
				$options = ['element' => $args[1]['plugin'] . '.' . $element];
				unset($args[1]['plugin']);
			}
			$options += (array)$args[1];
		}

		if ($transient || $this->ajaxHandling()) {
			$this->transientMessage($args[0], $options);
		} else {
			$this->set($args[0], $options);
		}
	}

	/**
	 * @return bool
	 */
	protected function ajaxHandling(): bool {
		if (!$this->getConfig('noSessionOnAjax')) {
			return false;
		}

		return $this->getController()->getRequest()->is('ajax');
	}

}
