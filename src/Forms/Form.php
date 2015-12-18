<?php

namespace Forms;

require_once 'autoload.php';

use Forms\Elements\ElementInterface;

class Form implements \IteratorAggregate, \Countable {

	use Traits\Attributes;

	protected $elements;

	public function __construct(array $attributes = []) {
		$this->setAttributes(array_merge(['accept-charset' => 'utf-8'], $attributes));
		$this->elements = new \SplObjectStorage;
	}

	public function setValues(array $values) {
		foreach($this->elements as $element) {
			$name = $element->getName();

			if(array_key_exists($name, $values)) {
				$element->setValue($values[$name]);
			}
		}
	}

	public function withValues(array $values) {
		$this->setValues($values);

		return $this;
	}

	public function addElement(ElementInterface $element) {
		$this->elements->attach($element);
	}

	public function removeElement($name) {
		$element = $this->getElement($name);

		$this->elements->detach($element);
	}

	public function getElement($name) {
		foreach($this->elements as $element) {
			if($element->getName() === $name) {
				return $element;
			}
		}

		throw new \InvalidArgumentException(sprintf('Form element not found "%s"', $name));
	}

	public function getElements(array $names) {
		$filtered = clone $this->elements;

		foreach($this->elements as $element) {
			if(false === in_array($element->getName(), $names, true)) {
				$filtered->detach($element);
			}
		}

		return $filtered;
	}

	public function getElementsExcluding(array $names) {
		$filtered = clone $this->elements;

		foreach($this->elements as $element) {
			if(true === in_array($element->getName(), $names, true)) {
				$filtered->detach($element);
			}
		}

		return $filtered;
	}

	public function setElements(array $elements) {
		$this->elements = new \SplObjectStorage;

		foreach($elements as $element) {
			$this->addElement($element);
		}

		unset($elements);
	}

	public function withElements(array $elements) {
		foreach($elements as $element) {
			$this->addElement($element);
		}

		unset($elements);

		return $this;
	}

	public function getIterator() {
		return $this->elements;
	}

	public function count() {
		return $this->elements->count();
	}

	public function open(array $extra = []) {
		return sprintf('<form %s>', $this->withAttributes($extra)->getAttributesAsString());
	}

	public function withFiles() {
		return $this->withAttribute('enctype', 'multipart/form-data');
	}

	public function close() {
		return '</form>';
	}

	public function build() {
		$html = $this->open() . "\r\n";

		foreach($this->elements as $element) {
			$html .= "\t";
			if($element->hasLabel()) {
				$html .= "<label for='" . $element->getName() . "'>" . $element->getLabel() . "</label>\r\n\t";
			}
			$html .= $element->getHtml() . "\r\n";
		}

		$html .= $this->close() . "\r\n";

		return $html;
	}

	public function buildGrouped() {
		$html = $this->open() . "\r\n";

		foreach($this->elements as $element) {
		    if( "hidden" != $element->getType() )
    			$html .= "\t<div class='forms-group forms-" . $element->getName() . "'>\r\n\t\t";

			if($element->hasLabel()) {
				$html .= "<label for='" . $element->getName() . "'>" . $element->getLabel() . "</label>\r\n\t";
			}
			$html .= $element->getHtml();

			if( "hidden" != $element->getType() )
			    $html .= "\r\n\t</div>\r\n";
		}

		$html .= $this->close() . "\r\n";

		return $html;
	}

}
