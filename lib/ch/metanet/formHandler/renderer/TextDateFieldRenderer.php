<?php

namespace ch\metanet\formHandler\renderer;

use ch\metanet\formHandler\field\DateField;

/**
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2014, METANET AG
 * @version 1.0.0
 */
class TextDateFieldRenderer extends DateFieldRenderer {
	/**
	 * @param DateField $field The field instance to render
	 * @return string The rendered field
	 */
	public function render(DateField $field) {
		if(is_array($field->getValue()) === true) {
			$field->setValue(null);
		}

		return '<input type="text" value="' . $field->getValue() . '" name="' . $field->getName() . '" id="' . $field->getName() . '">';
	}
}

/* EOF */