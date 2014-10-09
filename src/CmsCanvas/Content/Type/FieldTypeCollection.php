<?php namespace CmsCanvas\Content\Type;

use CmsCanvas\Database\Eloquent\Collection as CmsCanvasCollection;
use CmsCanvas\Models\Language;

class FieldTypeCollection extends CmsCanvasCollection {

	/**
	 * Sets data to the fields types from an array
	 *
	 * @param array $array
	 * @return void
	 */
	public function fill(array $array)
	{
		foreach ($this->items as $item)
		{
			if (isset($array[$item->getKey()]))
			{
				$item->setData($array[$item->getKey()], true);
			}

			if (isset($array[$item->getMetadataKey()]))
			{
				$item->setMetadata($array[$item->getMetadataKey()], true);
			}
		}
	}

	/**
	 * Deletes field type's existing data from the database
	 * and saves the new data.
	 *
	 * @return void
	 */
	public function save()
	{
		$languages = Language::all();
		$localeIds = $languages->getKeyValueArray('locale', 'id');
		$entries = array();

		foreach ($this->items as $item)
		{
			if (!isset($entries[$item->entry->id])) 
			{
				$item->entry->allData()->delete();
				$entries[$item->entry->id] = $item->entry;
			}

			$data = $item->getSaveData();
			$metadata = $item->getSaveMetadata();

			// Only insert data if it is not an empty string and not null
			if (($data !== '' && $data !== null) || ($metadata !== '' && $metadata !== null))
			{
				$entryData = new \CmsCanvas\Models\Content\Entry\Data;
				$entryData->entry_id = $item->entry->id;
				$entryData->content_type_field_id = $item->field->id;
				$entryData->language_id = $localeIds[$item->locale];
				$entryData->data = ($data === '' || $data === null) ? null : $data;
				$entryData->metadata = ($metadata === '' || $metadata === null) ? null : $metadata;
				$entryData->save();
			}
		}
	}

	/**
	 * Returns an array of each field type's validation rules
	 *
	 * @return array
	 */
	public function getValidationRules()
	{
		$rules = array();

		foreach ($this->items as $item)
		{
			$itemRules = $item->getValidationRules();

			if ( ! empty($itemRules))
			{
				$rules = array_merge($rules, $itemRules);
			}
		}

		return $rules;
	}

	/**
	 * Returns an array of each field type's key and attribute name
	 *
	 * @return array
	 */
	public function getAttributeNames()
	{
		$attributeNames = array();

		foreach ($this->items as $item)
		{
			$attributeNames[$item->getKey()] = $item->field->label;
		}

		return $attributeNames;
	}

	/**
	 * Sets and entry to all field types
	 *
	 * @param \CmsCanvas\Models\Content\Entry
	 * @param void
	 */
	public function setEntry(\CmsCanvas\Models\Content\Entry $entry)
	{
		foreach ($this->items as $item)
		{
			$item->setEntry($entry);
		}
	}

}