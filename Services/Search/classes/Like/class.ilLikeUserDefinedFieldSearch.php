<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Search/classes/class.ilUserDefinedFieldSearch.php';

/**
* Class ilLikeUserDefinedFieldSearch
*
* Performs Mysql Like search in table usr_defined_data
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilLikeUserDefinedFieldSearch.php 23143 2010-03-09 12:15:33Z smeyer $
* 
* @package ilias-search
*
*/
class ilLikeUserDefinedFieldSearch extends ilUserDefinedFieldSearch
{

	/**
	* Constructor
	* @access public
	*/
	function ilLikeUserDefinedFieldSearch(&$qp_obj)
	{
		parent::ilUserDefinedFieldSearch($qp_obj);
	}
	
	/**
	 * 
	 * @param
	 * @return
	 */
	public function setFields($a_fields)
	{
		foreach($a_fields as $field)
		{
			$fields[] = 'f_'.$field;
		}
		parent::setFields($fields ? $fields : array());
	}
	

	function __createWhereCondition()
	{
		global $ilDB;
		
		$fields = $this->getFields();
		$field = $fields[0];

		$and = "  WHERE field_id = ".$ilDB->quote((int) substr($field, 2), "integer")." AND ( ";
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			if($counter++)
			{
				$and .= " OR ";
			}

			if(strpos($word,'^') === 0)
			{
				$and .= $ilDB->like("value", "text", substr($word,1)."%");
			}
			else
			{
				$and .= $ilDB->like("value", "text", "%".$word."%");
			}
		}
		return $and.") ";
	}
}
?>
