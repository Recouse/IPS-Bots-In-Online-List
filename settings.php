//<?php

$prefixsuffix = explode( ',', \IPS\Settings::i()->botsInOnlineList_prefixsuffix );

$form->add( new \IPS\Helpers\Form\Select( 'botsInOnlineList_groups', \IPS\Settings::i()->botsInOnlineList_groups == '*' ? '*' : explode( ',', \IPS\Settings::i()->botsInOnlineList_groups ), FALSE, array( 'options' => \IPS\Member\Group::groups(), 'parse' => 'normal', 'multiple' => true, 'unlimited' => '*', 'unlimitedLang' => 'all_groups' ), NULL, NULL, NULL, 'botsInOnlineList_groups' ) );
$form->add( new \IPS\Helpers\Form\Select( 'botsInOnlineList_type', \IPS\Settings::i()->botsInOnlineList_type, FALSE, array( 'options' => array( 'after' => 'botsInOnlineList_typeAfter', 'in' => 'botsInOnlineList_typeIn' ) ) ) );
$form->add( new \IPS\Helpers\Form\Custom( 'botsInOnlineList_prefixsuffix', array( 'prefix' => $prefixsuffix[0], 'suffix' => $prefixsuffix[1] ), FALSE, array(
	'getHtml'	=> function( $element )
	{
		$color = NULL;
		if ( preg_match( '/^<span style=\'color:#((?:(?:[a-f0-9]{3})|(?:[a-f0-9]{6})));\'>$/i', $element->value['prefix'], $matches ) and $element->value['suffix'] === '</span>' )
		{
			$color = $matches[1];
			$element->value['prefix'] = NULL;
			$element->value['suffix'] = NULL;
		}

		return \IPS\Theme::i()->getTemplate( 'plugins', 'core', 'global' )->prefixSuffix( $element->name, $color, $element->value['prefix'], $element->value['suffix'] );
	},
	'formatValue' => function( $element )
	{
		if ( $element->value['prefix'] or $element->value['suffix'] )
		{
			return array( $element->value['prefix'], $element->value['suffix'] );
		}
		elseif ( isset( $element->value['color'] ) )
		{
			$color = mb_strtolower( $element->value['color'] );
			if ( mb_substr( $color, 0, 1 ) !== '#' )
			{
				$color = '#' . $color;
			}

			if( !in_array( $color, array( '#fff', '#ffffff', '#000', '#000000' ) ) )
			{
				return array( "<span style='color:{$color}'>", '</span>' );
			}
		}

		return array( '', '' );
	}
), NULL, NULL, NULL, 'botsInOnlineList_prefixsuffix' ) );

if ( $values = $form->values() )
{
	$values['botsInOnlineList_prefixsuffix'] = implode( ',', $values['botsInOnlineList_prefixsuffix'] );

	$form->saveAsSettings();
	return TRUE;
}

return $form;