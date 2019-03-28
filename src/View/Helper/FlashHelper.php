<?php

namespace Flash\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;

/**
 * Flash helper
 *
 * @author Mark Scherer
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
		'limit' => 10 // Max message limit per type - first in, first out
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
	public function addTransientMessage($message, $options = null) {
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
				'type' => $type
			];
		}

		$options += ['type' => 'info'];
		$options += ['element' => $options['type']];

		$options += $this->getConfig();

		list($plugin, $element) = pluginSplit($options['element']);

		if ($plugin) {
			$options['element'] = $plugin . '.Flash/' . $element;
		} else {
			$options['element'] = 'Flash/' . $element;
		}

		return $options;
	}

}
