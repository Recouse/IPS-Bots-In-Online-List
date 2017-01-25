//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class hook46 extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'whosOnline' => 
  array (
    0 => 
    array (
      'selector' => 'div.ipsWidget_inner',
      'type' => 'add_inside_end',
      'content' => '{{if \IPS\Settings::i()->botsInOnlineList_groups == \'*\' || \IPS\Member::loggedIn()->inGroup( explode( \',\', \IPS\Settings::i()->botsInOnlineList_groups ) )}}
{{if \IPS\Settings::i()->botsInOnlineList_type == \'after\'}}
{{$getBots = new \IPS\core\modules\front\system\plugins();}}
{{$bots = $getBots->botsInOnlineList();}}
{template="botsInOnlineList" group="plugins" location="global" app="core" params="$bots"}
{{endif}}
{{endif}}',
    ),
    1 => 
    array (
      'selector' => 'div.ipsWidget_inner > ul.ipsList_inline.ipsList_csv.ipsList_noSpacing',
      'type' => 'add_inside_end',
      'content' => '{{if \IPS\Settings::i()->botsInOnlineList_groups == \'*\' || \IPS\Member::loggedIn()->inGroup( explode( \',\', \IPS\Settings::i()->botsInOnlineList_groups ) )}}
{{if \IPS\Settings::i()->botsInOnlineList_type == \'in\'}}
{{$getBots = new \IPS\core\modules\front\system\plugins();}}
{{$bots = $getBots->botsInOnlineList();}}
{template="botsInOnlineListIn" group="plugins" location="global" app="core" params="$bots"}
{{endif}}
{{endif}}',
    ),
    2 => 
    array (
      'selector' => 'h3.ipsType_reset.ipsWidget_title',
      'type' => 'replace',
      'content' => '{{$getBots = new \IPS\core\modules\front\system\plugins();}}
{{$bots = $getBots->botsInOnlineList();}}
{template="botsInOnlineListTitle" group="plugins" location="global" app="core" params="$members,$guests,$anonymous,$bots,$memberCount,$orientation"}',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */


}
