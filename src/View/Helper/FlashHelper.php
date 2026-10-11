<?php

namespace Flash\View\Helper;

use BadMethodCallException;
use Cake\Core\Configure;
use Cake\Http\Exception\InternalErrorException;
use Cake\View\Helper;
use Cake\View\View;
use InvalidArgumentException;

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
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
		'key' => 'flash',
		'element' => 'default',
		'params' => [],
		'order' => ['error', 'warning', 'success', 'info'],
		'limit' => 10, // Max message limit per key - first in, first out
	];

	/**
	 * @param \Cake\View\View $view View instance.
	 * @param array<string, mixed> $config Helper options override shared Flash configuration.
	 */
	public function __construct(View $view, array $config = []) {
		$defaults = (array)Configure::read('Flash');
		$options = $config + $defaults;
		parent::__construct($view, $options);
		if (array_key_exists('order', $options)) {
			$this->setConfig('order', $options['order'], false);
		}
	}

	public function initialize(array $config): void {
		if ((int)$this->getConfig('limit') < 1) {
			throw new InvalidArgumentException('Flash message limit must be a positive integer.');
		}
	}

	/**
	 * Displays flash messages.
	 *
	 * Options:
	 * - types: Types to render out, defaults to all
	 *
	 * @param string|null $key The [Flash.]key you are rendering in the view.
	 * @param array<string, mixed> $options Additional options to use for the creation of this flash message.
	 *    Supports the 'params', and 'element' keys that are used in the helper.
	 * @throws \UnexpectedValueException If value for flash settings key is not an array.
	 * @return string|null Rendered flash message or null if flash key does not exist
	 *   in session.
	 */
	public function render(?string $key = null, array $options = []): ?string {
		$key ??= $this->getConfig('key');
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
			if ($options['types'] && !in_array($this->messageType($message), $options['types'], true)) {
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
				if (!in_array($this->messageType($message), $options['types'], true)) {
					continue;
				}
				$this->_View->getRequest()->getSession()->delete('Flash.' . $key . '.' . $index);
			}
			foreach ($transientMessages as $index => $message) {
				if (!in_array($this->messageType($message), $options['types'], true)) {
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
	 * @param array<array<string, mixed>> $messages
	 * @return array<array<string, mixed>>
	 */
	protected function _order(array $messages): array {
		$order = $this->getConfig('order');
		if (!$order) {
			return $messages;
		}

		$result = [];
		foreach ($order as $type) {
			foreach ($messages as $k => $message) {
				$messageType = $this->messageType($message);
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
	 * @param array<string, mixed> $message
	 * @return string
	 */
	protected function messageType(array $message): string {
		if (isset($message['type'])) {
			return (string)$message['type'];
		}
		$element = (string)$message['element'];
		$pos = strrpos($element, '/');

		return $pos === false ? $element : substr($element, $pos + 1);
	}

	/**
	 * Outputs a single flash message directly.
	 * Note that this does not use the Session.
	 *
	 * @param string $message String to output.
	 * @param array<string, mixed>|string|null $messageOptions Message options
	 * @param array<string, mixed> $options Element options
	 *
	 * @return string HTML
	 */
	public function message(string $message, array|string|null $messageOptions = null, array $options = []): string {
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
	 * @param array<string, mixed>|string|null $options
	 * @return void
	 */
	public function transientMessage(string $message, array|string|null $options = null): void {
		$options = $this->_mergeOptions($options);
		$options['message'] = $message;

		$messages = (array)Configure::read('TransientFlash.' . $options['key']);
		$limit = (int)$this->getConfig('limit');
		if ($limit < 1) {
			throw new InvalidArgumentException('Flash message limit must be a positive integer.');
		}
		$messages = array_slice($messages, -($limit - 1), $limit - 1);
		$messages[] = $options;
		Configure::write('TransientFlash.' . $options['key'], $messages);
	}

	/**
	 * Add a message on the fly
	 *
	 * @param string $name name
	 * @param array<int, mixed> $args method arguments
	 * @throws \BadMethodCallException
	 * @throws \Cake\Http\Exception\InternalErrorException
	 * @return void
	 */
	public function __call(string $name, array $args): void {
		if ($name === 'transient' || !str_starts_with($name, 'transient')) {
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
		$options += (array)($args[1] ?? []);
		$this->transientMessage($args[0], $options);
	}

	/**
	 * @param array<string, mixed>|string|null $options
	 *
	 * @return array<string, mixed>
	 */
	protected function _mergeOptions(array|string|null $options): array {
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
		if (!empty($options['plugin']) && !str_contains($options['element'], '.')) {
			$options['element'] = $options['plugin'] . '.' . $options['element'];
		}
		unset($options['plugin']);
		if (isset($options['escape']) && !isset($options['params']['escape'])) {
			$options['params']['escape'] = $options['escape'];
		}

		[$plugin, $element] = pluginSplit($options['element']);

		// Accept both short element names and an existing flash/ prefix.
		if (str_starts_with((string)$element, 'flash/')) {
			$element = substr((string)$element, 6);
		}

		$options['element'] = $plugin ? $plugin . '.flash/' . $element : 'flash/' . $element;

		return $options;
	}

}
