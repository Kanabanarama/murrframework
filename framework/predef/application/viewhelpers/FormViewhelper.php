<?php

/**
 * FormsViewhelper
 * Murrmurr framework
 *
 * builds some form tags
 *
 * @author René Lantzsch <kana@bookpile.net>
 * @since 24.06.2014
 * @version 1.0
 */

class FormViewhelper extends BaseViewhelper {

	public function render($strText, $aAttributes, $mData) {
		if(!isset($aAttributes['type'])) {
			throw new Exception('Form viewhelper has no type defined', 55);
		} else {
			$methodName = $aAttributes['type'];
			unset($aAttributes['type']);
			//$result = call_user_func('$this->'.$methodName, array($strText, $aAttributes));
			if(method_exists($this, $methodName)) {
				$content = $this->{$methodName}($strText, $aAttributes, $mData);
			} else {
				throw new Exception('Form viewhelper method '.$methodName.' is missing.', 55);
			}
		}

		return $content;
	}

	private function renderSelect($options, $preselect, $name) {
		$optionTags = '';
		foreach($options as $optionValue => $optionText) {
			$selected = '';
			if($optionValue == $preselect) {
				$selected = ' selected="selected"';
			}
			$optionTags .= '<option value="'.$optionValue.'"'.$selected.'>'.$optionText.'</option>';

		}
		$selectTag = '<select name="'.$name.'" class="selectbox select-'.$name.'">'.$optionTags.'</select>';

		return $selectTag;
	}

	private function genderSelect($preSelect) {
		$genders = array(
			0 => '-',
			1 => '♀',
			2 => '♂'
		);
		$genderSelect = $this->renderSelect($genders, $preSelect, 'gender');

		return $genderSelect;
	}

	private function countrySelect($preSelect) {
		$countries = array(
			'' => '-',
			'de' => 'Germany',
			'ch' => 'Switzerland',
			'at' => 'Austria',
			'uk' => 'United Kingdom',
			'us' => 'USA'
		);
		$countrySelect = $this->renderSelect($countries, $preSelect, 'country');

		return $countrySelect;
	}

	private function languageSelect($preSelect) {
		// TODO: get from registry later or something
		$languages = array(
			'en' => 'english',
			'de' => 'german',
			'fr' => 'french',
			//'it' => 'italian',
			//'es' => 'spanish',
			//'ru' => 'russian',
			'jp' => 'japanese'
		);
		$countrySelect = $this->renderSelect($languages, $preSelect, 'language');

		return $countrySelect;
	}

	private function translationSelect($preSelect) {
		// TODO: get from registry later or something
		$languages = array(
			'en' => 'english',
			'de' => 'german'
		);
		$countrySelect = $this->renderSelect($languages, $preSelect, 'pagelanguage');

		return $countrySelect;
	}

	private function birthdaySelect($birthday) {
		if($birthday === '0000-00-00') {
			$birthday = '1980-01-01';
		}
		$content = '<input name="birthday" class="input-birthday datepicker" value="'.$birthday.'" />';

		return $content;
	}

	private function dataSelect($content, $attributes, $data) {
		$uidList = array();
		if(isset($attributes['empty'])) {
			$uidList[0] = $attributes['empty'];
		}
		foreach($data as $entry) {
			$uidList[$entry->uid] = $entry->title;
		}
		$countrySelect = $this->renderSelect($uidList, null, 'data');

		return $countrySelect;
	}

}

?>