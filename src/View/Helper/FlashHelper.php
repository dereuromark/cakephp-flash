<?php

namespace Flash\View\Helper;

use BadMethodCallException;
use Cake\Core\Configure;
use Cake\Http\Exception\InternalErrorException;
use Cake\View\Helper;

/**
 * Flash helper
 *
 * @author Mark Scherer
 * @method void transientSuccess(string $message, array $options = []) Set a message using "success" element.
 *   These flash messages are not persisted across requests (only available for current view)
 * @method void transientError(string $message, array $options = []) Set a message using "error" element.
 *   These flash messages are not persisted across requests (only available for current view)
 * @method void transientWarning(string $message, array $options = []) Set a message using "warning" element
 *   These flash messages are not persisted across requests (only available for current view)
 * @method void transientInfo(string $message, array $options = []) Set a message using "info" element
 *   These flash messages are not persisted across requests (only available for current view)
 */
class FlashHelper extends Helper {

	/**
	 * Default configuration
	 *
	 * @var array
	 */
	protected $_defaultConfig = [
		'key' => 'flash',
		'element' => 'default',
		'params' => [],
		'order' => ['error', 'warning', 'success', 'info'],
		'limit' => 10, // Max message limit per type - first in, first out
	];

	/**
	 * Displays flash messages.
	 *
	 * Options:
	 * - types: Types to render out, defaults to all
	 *
	 * @param string $key The [Flash.]key you are rendering in the view.
	 * @param array $options Additional options to use for the creation of this flash message.
	 *    Supports the 'params', and 'element' keys that are used in the helper.
	 * @return string|null Rendered flash message or null if flash key does not exist
	 *   in session.
	 * @throws \UnexpectedValueException If value for flash settings key is not an array.
	 */
	public function render(string $key = 'flash', array $options = []): ?string {
		$options += ['types' => []];

		// Get the messages from the session
		$messages = (array)$this->_View->getRequest()->getSession()->read('Flash.' . $key);
		$transientMessages = (array)Configure::read('TransientFlash.' . $key);
		if ($transientMessages) {
			$messages = array_merge($messages, $transientMessages);
		}
		$messages = $this->_order($messages);

		$html = '';
		foreach ($messages as $message) {
			if ($options['types'] && !in_array($message['type'], $options['types'], true)) {
				continue;
			}

			$message = $options + $message;
			$options += [
				'plugin' => false,
			];

			$html .= $this->_View->element($message['element'], $message, $options);
		}

		if ($options['types']) {
			$messages = (array)$this->_View->getRequest()->getSession()->read('Flash.' . $key);
			foreach ($messages as $index => $message) {
				if (!in_array($message['type'], $options['types'], true)) {
					continue;
				}
				$this->_View->getRequest()->getSession()->delete('Flash.' . $key . '.' . $index);
			}
			foreach ($transientMessages as $index => $message) {
				if (!in_array($message['type'], $options['types'], true)) {
					continue;
				}
				Configure::delete('TransientFlash.' . $key . '.' . $index);
			}

		} else {
			$this->_View->getRequest()->getSession()->delete('Flash.' . $key);
			Configure::delete('TransientFlash.' . $key);
		}

		return $html;
	}

	/**
	 * @param array $messages
	 * @return array
	 */
	protected function _order(array $messages): array {
		$order = $this->getConfig('order');
		if (!$order) {
			return $messages;
		}

		$result = [];
		foreach ($order as $type) {
			foreach ($messages as $k => $message) {
				$messageType = isset($message['type']) ? $message['type'] : substr($message['element'], strrpos($message['element'], '/') + 1);
				if ($messageType !== $type) {
					continue;
				}

				$result[] = $message;
				unset($messages[$k]);
			}
		}

		foreach ($messages as $message) {
			$result[] = $message;
		}

		return $result;
	}

	/**
	 * Outputs a single flash message directly.
	 * Note that this does not use the Session.
	 *
	 * @param string $message String to output.
	 * @param array|string|null $messageOptions Message options
	 * @param array $options Element options
	 *
	 * @return string HTML
	 */
	public function message(string $message, $messageOptions = null, array $options = []): string {
		$messageOptions = $this->_mergeOptions($messageOptions);
		$messageOptions['message'] = $message;

		$options += [
			'plugin' => false,
		];

		return $this->_View->element($messageOptions['element'], $messageOptions, $options);
	}

	/**
	 * Add a message on the fly
	 *
	 * @param string $message
	 * @param array|string|null $options
	 * @return void
	 */
	public function transientMessage(string $message, $options = null): void {
		$options = $this->_mergeOptions($options);
		$options['message'] = $message;

		$messages = (array)Configure::read('TransientFlash.' . $options['key']);
		if ($messages && count($messages) > $this->getConfig('limit')) {
			array_shift($messages);
		}
		$messages[] = $options;
		Configure::write('TransientFlash.' . $options['key'], $messages);
	}

	/**
	 * Add a message on the fly
	 *
	 * @param string $name name
	 * @param array $args method arguments
	 * @throws \BadMethodCallException
	 * @throws \Cake\Http\Exception\InternalErrorException
	 * @return void
	 */
	public function __call(string $name, array $args): void {
		if (strpos($name, 'transient') !== 0) {
			throw new BadMethodCallException('Method ' . $name . '() does not exist. Select a type, e.g. transientInfo().');
		}

		$name = substr($name, 9); // remove "transient" prefix
		$type = lcfirst($name);

		if (count($args) < 1) {
			throw new InternalErrorException('Flash message missing.');
		}

		$options = [
			'type' => $type,
		];
		$this->transientMessage($args[0], $options);
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

		$options += [
			'type' => 'info',
			'element' => $options['type'],
		];
		$options += $this->getConfig();

		[$plugin, $element] = pluginSplit($options['element']);

		if ($plugin) {
			$options['element'] = $plugin . '.flash/' . $element;
		} else {
			$options['element'] = 'flash/' . $element;
		}

		return $options;
	}

}
