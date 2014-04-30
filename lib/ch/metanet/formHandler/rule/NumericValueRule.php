<?php


namespace ch\metanet\formHandler\rule;
use ch\metanet\formHandler\field\FormField;


/**
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2014, METANET AG
 * @version 1.0.0
 */
class NumericValueRule extends Rule {
	/**
	 * @param FormField $field The field instance to check against
	 * @return bool
	 */
	public function validate(FormField $field) {
		return is_numeric($field->getValue());
	}
}

/* EOF */ 