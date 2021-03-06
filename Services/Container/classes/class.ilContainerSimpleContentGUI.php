<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once("./Services/Container/classes/class.ilContainerContentGUI.php");

/**
* Shows all items in one block.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilContainerSimpleContentGUI extends ilContainerContentGUI
{
	protected $force_details;
	
	/**
	* Constructor
	*
	*/
	function __construct($container_gui_obj)
	{
		parent::__construct($container_gui_obj);
		$this->initDetails();
	}


	/**
	* Get content HTML for main column. 
	*/
	function getMainContent()
	{
		global $lng,$ilTabs;

		// see bug #7452
//		$ilTabs->setSubTabActive($this->getContainerObject()->getType().'_content');

		include_once 'Services/Object/classes/class.ilObjectListGUIFactory.php';

		$tpl = new ilTemplate("tpl.container_page.html", true, true,
			"Services/Container");

		// Feedback
		// @todo
//		$this->__showFeedBack();

		$this->__showMaterials($tpl);
			
		return $tpl->get();
	}

	/**
	* Show Materials
	*/
	function __showMaterials($a_tpl)
	{
		global $ilAccess, $lng;

		$this->items = $this->getContainerObject()->getSubItems($this->getContainerGUI()->isActiveAdministrationPanel());
		$this->clearAdminCommandsDetermination();
		
		$output_html = $this->getContainerGUI()->getContainerPageHTML();
		
		// get embedded blocks
		if ($output_html != "")
		{
			$output_html = $this->insertPageEmbeddedBlocks($output_html);
		}

		$tpl = $this->newBlockTemplate();
		
		// item groups
		$this->getItemGroupsHTML($tpl);
		
		if (is_array($this->items["_all"]))
		{
			// all rows
			$item_html = array();
			$position = 1;
			foreach($this->items["_all"] as $k => $item_data)
			{
				if ($this->rendered_items[$item_data["child"]] !== true)
				{
					if ($item_data["type"] == "itgr")
					{
						continue;
					}
					
					$html = $this->renderItem($item_data,$position++,true);
					if ($html != "")
					{
						$item_html[] = $html;
					}
				}
			}
			
			// if we have at least one item, output the block
			if (count($item_html) > 0)
			{
				$this->addHeaderRow($tpl, "", $lng->txt("content"));
				foreach($item_html as $h)
				{
					$this->addStandardRow($tpl, $h);
				}
			}
		}

		$output_html .= $tpl->get();
		$a_tpl->setVariable("CONTAINER_PAGE_CONTENT", $output_html);
	}
	
	/**
	 * init details
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function initDetails()
	{
		global $ilUser;
		
		if($_GET['expand'])
		{
			if($_GET['expand'] > 0)
			{
				$_SESSION['sess']['expanded'][abs((int) $_GET['expand'])] = self::DETAILS_ALL;
			}
			else
			{
				$_SESSION['sess']['expanded'][abs((int) $_GET['expand'])] = self::DETAILS_TITLE;
			}
		}
		
		
		if($this->getContainerObject()->getType() == 'crs')
		{
			include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
			if($session = ilSessionAppointment::lookupNextSessionByCourse($this->getContainerObject()->getRefId()))
			{
				$this->force_details = $session;
			}
			elseif($session = ilSessionAppointment::lookupLastSessionByCourse($this->getContainerObject()->getRefId()))
			{
				$this->force_details = $session;
			}
		}
	}
	
	/**
	 * get details level
	 *
	 * @access public
	 * @param	int	$a_session_id
	 * @return	int	DEATAILS_LEVEL
	 */
	public function getDetailsLevel($a_session_id)
	{
		if($this->getContainerGUI()->isActiveAdministrationPanel())
		{
			return self::DETAILS_DEACTIVATED;
		}
		if(isset($_SESSION['sess']['expanded'][$a_session_id]))
		{
			return $_SESSION['sess']['expanded'][$a_session_id];
		}
		if($a_session_id == $this->force_details)
		{
			return self::DETAILS_ALL;
		}
		else
		{
			return self::DETAILS_TITLE;
		}
	}
	
	

} // END class.ilContainerSimpleContentGUI
?>
