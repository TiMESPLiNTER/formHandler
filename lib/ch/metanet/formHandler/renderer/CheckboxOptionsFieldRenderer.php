<?php

namespace ch\metanet\formHandler\renderer;

use ch\metanet\formHandler\field\OptionsField;

/**
 * @author Pascal Muenst <entwicklung@metanet.ch>
 * @copyright Copyright (c) 2014, METANET AG
 */
class CheckboxOptionsFieldRenderer extends OptionsFieldRenderer
{
	/**
	 * @param OptionsField $field
	 * @return string
	 */
	public function render(OptionsField $field)
	{
		$required = ($field->hasRule('ch\metanet\formHandler\rule\RequiredRule') === true) ? ' aria-required="true"' : null;
		$optionsCount = count($field->getOptions());
		$fieldValue = (array)$field->getValue();
		
		$html = '<ul' . $this->getAttributesAsHtml() . '>';
		$fieldName = ($optionsCount === 1)?$field->getName():$field->getName() . '[]';

		foreach($field->getOptions() as $key => $val) {
			$attrId = ' id="' . $field->getName() . (($optionsCount !== 1) ?  '-' . $key : null) . '"';
			
			$checked = in_array($key, $fieldValue) ? ' checked' : null;
			$html .= '<li><label><input type="checkbox" name="' . $fieldName . '" value="' . $key . '"' . $attrId . $checked . $required . '> ' . $val . '</label></li>';
		}

		$html .= '</ul>';

		return $html;
	}
}

/* EOF */