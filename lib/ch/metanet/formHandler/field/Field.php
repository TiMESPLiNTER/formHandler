<?php

namespace ch\metanet\formHandler\field;

use ch\metanet\formHandler\component\Component;
use ch\metanet\formHandler\component\Form;
use ch\metanet\formHandler\decorator\FieldValueDecorator;
use ch\metanet\formHandler\listener\FormFieldListener;
use ch\metanet\formHandler\renderer\DefaultFieldComponentRenderer;
use ch\metanet\formHandler\renderer\FieldComponentRenderer;
use ch\metanet\formHandler\rule\Rule;

/**
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2014, METANET AG
 */
abstract class Field extends Component
{
	protected $label;
	protected $id;
	protected $ruleSet;
	protected $decorators;
	protected $validated;
	
	protected $value;
	protected $linkedLabel;

	protected $errors;

	protected $fieldComponentRenderer;

	/**
	 * @param string $name The name of the field in the HTTP request
	 * @param string $label The label of the field
	 * @param array $ruleSet
	 */
	public function __construct($name, $label, array $ruleSet = array())
	{
		parent::__construct($name);		
		
		$this->id = $name;
		$this->label = $label;
		$this->ruleSet = $ruleSet;

		$this->errors = array();
		$this->validated = false;
		$this->value = null;
		$this->linkedLabel = true;
		
		$this->fieldComponentRenderer = new DefaultFieldComponentRenderer();
	}

	public function validate()
	{
		foreach($this->ruleSet as $r) {
			/** @var Rule $r */
			if($r->validate($this) === true)
				continue;

			$this->errors[] = $r->getErrorMessage();
		}

		$this->validated = true;

		$isValueEmpty = $this->isValueEmpty();

		foreach($this->listeners as $l) {
			/** @var FormFieldListener $l */
			if($isValueEmpty === true) {
				$l->onEmptyValueAfterValidation($this->formComponent, $this);
			} else {
				$l->onNotEmptyValueAfterValidation($this->formComponent, $this);
			}
		}

		return !$this->hasErrors();
	}

	/**
	 * @return bool
	 * @throws \UnexpectedValueException
	 */
	public function isValueEmpty()
	{
		if($this->value === null)
			return true;

		if(is_scalar($this->value)) {
			return (strlen($this->value) <= 0);
		} elseif(is_array($this->value)) {
			return (count(array_filter($this->value)) <= 0);
		} elseif($this->value instanceof \ArrayObject) {
			return (count(array_filter((array)$this->value)) <= 0);
		} else {
			throw new \UnexpectedValueException('Could not check value against emptiness');
		}
	}

	/**
	 * @param string $ruleClassName
	 * @return bool
	 */
	public function hasRule($ruleClassName)
	{
		foreach($this->ruleSet as $r) {
			if(get_class($r) === $ruleClassName)
				return true;
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @return array
	 */
	public function getRuleSet()
	{
		return $this->ruleSet;
	}

	/**
	 * @param Rule $rule
	 */
	public function addRule(Rule $rule)
	{
		$this->ruleSet[] = $rule;
	}

	/**
	 * @return array
	 */
	public function getDecorators()
	{
		return $this->decorators;
	}

	/**
	 * @param array $ruleSet
	 * @param bool $override Override current rules for this field
	 */
	public function setRuleSet(array $ruleSet, $override = false)
	{
		if($override === true)
			$this->ruleSet = array();

		$this->ruleSet = array_merge($this->ruleSet, $ruleSet);
	}

	public function resetChecked()
	{
		$this->validated = false;
		$this->errors = array();
	}

	public function hasErrors()
	{
		return (count($this->errors) > 0);
	}


	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * @param string $errorMessage
	 */
	public function addError($errorMessage)
	{
		$this->errors[] = $errorMessage;
	}

	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @return boolean
	 */
	public function getValidated()
	{
		return $this->validated;
	}

	/**
	 * @param mixed $linkedLabel
	 */
	public function setLinkedLabel($linkedLabel)
	{
		$this->linkedLabel = $linkedLabel;
	}

	/**
	 * @return mixed
	 */
	public function getLinkedLabel()
	{
		return $this->linkedLabel;
	}

	/**
	 * @param FieldComponentRenderer $fieldComponentRenderer
	 */
	public function setFieldComponentRenderer(FieldComponentRenderer $fieldComponentRenderer)
	{
		$this->fieldComponentRenderer = $fieldComponentRenderer;
	}

	/**
	 * @return FieldComponentRenderer
	 */
	public function getFieldComponentRenderer()
	{
		return $this->fieldComponentRenderer;
	}

	public function setRequestData($data)
	{
		$this->inputData = $data;
		
		if($this->formComponent->isSent() === true)
			$this->value = $this->inputData;
	}

	public function getRequestData()
	{
		return $this->value;
	}

	/**
	 * @param string $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}
}

/* EOF */ 