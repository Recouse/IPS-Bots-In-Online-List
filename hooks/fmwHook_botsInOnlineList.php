//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class hook47 extends _HOOK_CLASS_
{
	public function botsInOnlineList()
	{
		try
		{
			/* Do we have permission? */
			if ( !\IPS\Member::loggedIn()->canAccessModule( \IPS\Application\Module::get( 'core', 'online' ) ) )
			{
				return "";
			}

			/* Init */
			$bots = array();

			/* Query */
			$where = array(
				array( 'core_sessions.running_time>' . \IPS\DateTime::create()->sub( new \DateInterval( 'PT30M' ) )->getTimeStamp() ),
				array( 'core_groups.g_hide_online_list=0' )
			);
			foreach( \IPS\Db::i()->select( 'core_sessions.login_type,core_sessions.uagent_key,core_sessions.location_lang', 'core_sessions', $where, 'core_sessions.running_time DESC' )->join( 'core_groups', 'core_sessions.member_group=core_groups.g_id' ) as $row )
			{
				if ( $row['login_type'] == \IPS\Session\Front::LOGIN_TYPE_SPIDER )
				{
					$bots[] = ucwords( $row['uagent_key'] );
				}
			}

			$botsCount = count( $bots );
			$botsName = array_unique( $bots );
			$outputBots = array(
				'bots' => $botsName,
				'count' => $botsCount
			);

			/* Return bots data */
			return $outputBots;
		}
		catch ( \RuntimeException $e )
		{
			throw $e;
		}
	}
}
