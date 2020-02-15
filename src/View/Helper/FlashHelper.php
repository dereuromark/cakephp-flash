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
 * @method void addTransientSuccess(string $message, array $options = []) Set a message using "success" element.
 *                                                                      These flash messages that are not saved (only available for current view)
 * @method void addTransientError(string $message, array $options = []) Set a message using "error" element.
 *                                                                      These flash messages that are not saved (only available for current view)
 * @method void addTransientWarning(string $message, array $options = []) Set a message using "warning" element
 *                                                                      These flash messages that are not saved (only available for current view)
 * @method void addTransientInfo(string $message, array $options = []) Set a message using "info" element
 *                                                                      These flash messages that are not saved (only available for current view)
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
	public function render($key = 'flash', array $options = []) {
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
			if ($options['types'] && !in_array($message['type'], $options['types'])) {
				continue;
			}

			$message = $options + $message;
			$html .= $this->_View->element($message['element'], $message);
		}

		if ($options['types']) {
			$messages = (array)$this->_View->getRequest()->getSession()->read('Flash.' . $key);
			foreach ($messages as $index => $message) {
				if (!in_array($message['type'], $options['types'])) {
					continue;
				}
				$this->_View->getRequest()->getSession()->delete('Flash.' . $key . '.' . $index);
			}
			foreach ($transientMessages as $index => $message) {
				if (!in_array($message['type'], $options['types'])) {
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
	protected function _order($messages) {
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
	 * @param array|string|null $options Options
	 *
	 * @return string HTML
	 */
	public function message($message, $options = null) {
		$options = $this->_mergeOptions($options);
		$options['message'] = $message;

		return $this->_View->element($options['element'], $options);
	}

	/**
	 * Add a message on the fly
	 *
	 * @param string $message
	 * @param array|string|null $options
	 * @return void
	 */
	public function addTransientMessage($message, $options = null): void {
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
	 * @return void
	 */
	public function __call(string $name, array $args): void {
		if ($name === 'addTransient') {
			throw new BadMethodCallException('Method does not exist. Select a type e.g. addTransientInfo.');
		}

		if (strpos($name, 'addTransient') !== 0) {
			throw new BadMethodCallException('Method does not exist.');
		}

		$name = substr($name, 12); // remove addTransient
		$type = lcfirst($name);

		if (count($args) < 1) {
			throw new InternalErrorException('Flash message missing.');
		}

		$options['type'] = $type;

		$this->addTransientMessage($args[0], $options);
	}

	/**
	 * @param array|string|null $options
	 *
	 * @return array
	 */
	protected function _mergeOptions($options) {
		if (!is_array($options)) {
			$type = $options;
			if (!$type) {
				$type = 'info';
			}
			$options = [
				'type' => $type,
			];
		}

		$options += ['type' => 'info'];
		$options += ['element' => $options['type']];

		$options += $this->getConfig();

		[$plugin, $element] = pluginSplit($options['element']);

		if ($plugin) {
			$options['element'] = $plugin . '.Flash/' . $element;
		} else {
			$options['element'] = 'Flash/' . $element;
		}

		return $options;
	}

}
