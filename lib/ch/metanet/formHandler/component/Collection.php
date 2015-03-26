<?php

namespace ch\metanet\formHandler\component;

use ch\metanet\formHandler\mappable\Mappable;
use ch\metanet\formHandler\exception\FormHandlerException;
use ch\metanet\formHandler\field\Field;
use ch\metanet\formHandler\mappable\MappableHelper;
use ch\metanet\formHandler\renderer\CollectionComponentRenderer;
use ch\metanet\formHandler\renderer\DefaultCollectionComponentRenderer;

/**
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2014, METANET AG
 */
class Collection extends Component implements Mappable
{
	/** @var Component[] */
	protected $components;
	
	/** @var CollectionComponentRenderer */
	protected $collectionComponentRenderer;
	
	protected $mappedReference;

	public function __construct($name)
	{
		parent::__construct($name);
		
		$this->collectionComponentRenderer = new DefaultCollectionComponentRenderer();
		$this->inputData = array();
		$this->components = array();
	}
	
	/**
	 * Connects an undefined amount of form fields with this form handler instance preserving the already connected
	 * fields of this instance
	 * @param Component[] $components The array with form fields to be connected
	 */
	public function addComponents(array $components)
	{
		foreach($components as $fld) {
			$this->addComponent($fld);
		}
	}

	/**
	 * Connects an undefined amount of form fields with this form handler instance and overrides all the already
	 * connected fields of this instance
	 * @param Component[] $components The array with form fields to be connected
	 */
	public function setComponents(array $components)
	{
		$this->components = array();
		$this->addComponents($components);
	}

	/**
	 * Connects a component with the form handler instance
	 * @param Component $component The form component to add
	 */
	public function addComponent(Component $component)
	{
		$component->setParentComponent($this);
		$component->setFormComponent($this->formComponent);
		
		$this->components[$component->getName()] = $component;
		
		
		// Set value from attached object
		if($this->isMapped() && $component instanceof Mappable) {
			/** @var Component|Mappable $component */
			if(is_object($component->getMapped()) === true) {
				$value = $component->getMapped();
			} elseif(property_exists($this->mappedReference, $component->getMapped())) {
				$value = MappableHelper::getPropertyValue($this->mappedReference, $component->getMapped());
			} else {
				$value = null;
			}
			
			$component->setMappedData($value);
		}

		// Set value if there is one
		$component->setInputData(is_array($this->inputData) && array_key_exists($component->getName(), $this->inputData) ? $this->inputData[ $component->getName() ] : null);

	}

	/**
	 * Returns all connected components for this form handler instance
	 * @return Component[] All connected components of this form handler instance
	 */
	public function getComponents()
	{
		return $this->components;
	}

	public function setParentComponent(Component $parentComponent)
	{
		parent::setParentComponent($parentComponent);
		
		if(is_string($this->mappedReference) === true && property_exists($this->parentComponent->getMapped(), $this->mappedReference) === true) {
			$this->mappedReference = MappableHelper::getPropertyValue($this->parentComponent->getMapped(), $this->mappedReference);
		}
	}

	/**
	 * Returns a connected component of this form handler instance selected by its name
	 * @param string $name The name of the component
	 * @throws \OutOfBoundsException If the component does not exist
	 * @return Component The component
	 */
	public function getComponent($name)
	{
		if(isset($this->components[$name]) === false)
			throw new \OutOfBoundsException('The component with name "' . $name . '" does not exist');

		return $this->components[$name];
	}
	
	public function render()
	{
		return $this->collectionComponentRenderer->render($this);
	}

	/**
	 * @param CollectionComponentRenderer $collectionComponentRenderer
	 */
	public function setCollectionComponentRenderer(CollectionComponentRenderer $collectionComponentRenderer)
	{
		$this->collectionComponentRenderer = $collectionComponentRenderer;
	}

	public function validate()
	{
		$validation = true;
		
		foreach($this->components as $component) {
			if($component->validate() === true) {
				
				if($this->isMapped() === false || $component instanceof Mappable === false)
					continue;

				/** @var Component|Mappable $component */
				if(is_string($component->getMapped()) === false || property_exists($this->mappedReference, $component->getMapped()) === false)
					continue;
					
				MappableHelper::setPropertyValue($this->mappedReference, $component->getMapped(), $component->getMappedData());
								
				continue;
			}
			
			$validation = false;
			$this->errors = array_merge($this->errors, $component->getErrors());
		}
		
		return $validation;
	}

	public function isValueEmpty()
	{
		$validation = true;

		foreach($this->components as $component) {
			/** @var Field $component */
			if($component->isValueEmpty() === false)
				$validation = false;
		}

		return $validation;
	}

	public function setInputData($data)
	{
		$this->inputData = (array)$data;
		
		// Delegate values
		foreach($this->components as $component) {
			/** @var Field $component */
			$component->setInputData(array_key_exists($component->getName(), $this->inputData) ? $this->inputData[$component->getName()] : null);
		}
	}

	/**
	 * @return bool
	 */
	public function hasErrors()
	{
		foreach($this->components as $component) {
			/** @var Field $component */
			if($component->hasErrors() === true)
				return true;
		}
		
		return false;
	}

	/**
	 * @return array
	 */
	public function getErrors()
	{
		$errors = array();

		foreach($this->components as $component) {
			/** @var Field $component */
			$errors = array_merge($errors, $component->getErrors());
		}
		
		return $errors;
	}

	/**
	 * @param array|object $reference
	 *
	 * @throws FormHandlerException
	 */
	public function map($reference)
	{
		$this->mappedReference = $reference;
	}

	/**
	 * @return bool
	 */
	public function isMapped()
	{
		return ($this->mappedReference !== null);
	}

	/**
	 * @return array|object $reference
	 */
	public function getMapped()
	{
		return $this->mappedReference;
	}

	/**
	 * @param object $data
	 *
	 * @throws FormHandlerException
	 */
	public function setMappedData($data)
	{
		if($data === null)
			return;
		elseif(is_object($data) === false)
			throw new FormHandlerException('The value to attach has to be an object');
		
		$this->mappedReference = $data;
	}

	/**
	 * @return object
	 */
	public function getMappedData()
	{
		return $this->mappedReference;
	}
}

/* EOF */ 