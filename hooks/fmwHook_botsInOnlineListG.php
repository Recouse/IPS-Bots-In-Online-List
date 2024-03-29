//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class hook49 extends _HOOK_CLASS_
{
	/**
	 * Show Online Users
	 *
	 * @return	void
	 */
	protected function manage()
	{
		if ( \IPS\Settings::i()->botsInOnlineList_groups == '*' || \IPS\Member::loggedIn()->inGroup( explode( ',', \IPS\Settings::i()->botsInOnlineList_groups ) ) )
		{
			/* Set Session Location */
			\IPS\Session::i()->setLocation( \IPS\Http\Url::internal( 'app=core&module=online&controller=online', 'front', 'online' ), array(), 'loc_viewing_online_users' );

			/* Sessions are written on shutdown so let's do it now instead */
			\IPS\Session\Front::i()->setTheme( \IPS\Member::loggedIn()->skin ?: 0 );
			session_write_close();

			/* Initial filters */
			$where = array(
				array( "core_sessions.running_time>?", \IPS\DateTime::create()->sub( new \DateInterval( 'PT30M' ) )->getTimeStamp() ),
			);

			if ( !\IPS\Member::loggedIn()->isAdmin() )
			{
				$where[] = array( "core_sessions.login_type!=?", \IPS\Session\Front::LOGIN_TYPE_ANONYMOUS );
			}

			$where[] = "core_groups.g_hide_online_list=0";

			/* Create the table */
			$table = new \IPS\Helpers\Table\Db( 'core_sessions', \IPS\Http\Url::internal( 'app=core&module=online&controller=online', 'front', 'online' ), $where );
			$table->tableTemplate = array( \IPS\Theme::i()->getTemplate( 'online', 'core', 'front' ), 'onlineUsersTable' );
			$table->rowsTemplate	  = array( \IPS\Theme::i()->getTemplate( 'online', 'core', 'front' ), 'onlineUsersRow' );
			$table->langPrefix = 'online_users_';
			$table->include = array( 'photo', 'member_name', 'location_lang', 'running_time', 'ip_address', 'login_type' );
			$table->limit = 30;
			$table->noSort	= array( 'photo', 'location_lang' );

			/* Joins */
			$table->joins = array(
				array(
					'select' => 'm.member_id',
					'from' => array( 'core_members', 'm' ),
					'where' => 'm.member_id=core_sessions.member_id'
				),
				array(
					'from' => 'core_groups',
					'where' => 'core_sessions.member_group=core_groups.g_id'
				),
			);

			/* Custom parsers */
			$table->parsers = array(
				'location_lang'	=> function( $val, $row )
				{
					return \IPS\Session\Front::getLocation( $row );
				},
				'photo' => function( $val, $row )
				{
					return \IPS\Theme::i()->getTemplate( 'global', 'core' )->userPhoto( \IPS\Member::load( $row['member_id'] ), 'mini' );
				},
				'running_time' => function( $val, $row )
				{
					return \IPS\DateTime::ts( $val )->relative();
				},
				'member_name' => function( $val, $row )
				{
					if( $row['member_id'] )
					{
						return \IPS\Theme::i()->getTemplate( 'global', 'core' )->userLink( \IPS\Member::load( $row['member_id'] ) );
					}
					elseif ( $row['login_type'] == \IPS\Session\Front::LOGIN_TYPE_SPIDER )
					{
						$prefixsuffix = explode( ',', \IPS\Settings::i()->botsInOnlineList_prefixsuffix );

						return $prefixsuffix[0] . ucwords( $row['uagent_key'] ) . $prefixsuffix[1];
					}
					else
					{
						return \IPS\Member::loggedIn()->language()->addToStack( 'guest' );
					}
				},
			);

			$table->filters = array(
				'filter_loggedin'	=> 'm.member_id <> 0',
			);

			foreach ( \IPS\Member\Group::groups() as $group )
			{
				/* Hiding from online list? */
				if( $group->g_hide_online_list )
				{
					continue;
				}

				/* Alias the lang keys */
				$realLangKey = "core_group_{$group->g_id}";
				$fakeLangKey = "online_users_group_{$group->g_id}";
				\IPS\Member::loggedIn()->language()->words[ $fakeLangKey ] = \IPS\Member::loggedIn()->language()->addToStack( $realLangKey, FALSE );

				if( $group->g_id == \IPS\Settings::i()->guest_group )
				{
					$table->filters[ 'group_' . $group->g_id ] = 'm.member_id IS NULL';
				}
				else
				{
					$table->filters[ 'group_' . $group->g_id ] = 'm.member_group_id=' . $group->g_id;
				}
			}

			$table->sortBy = $table->sortBy ?: 'running_time';
			$table->sortDirection = $table->sortDirection ?: 'desc';

			/* Get the count */
			$counter = \IPS\Db::i()->select( 'COUNT(*)', 'core_sessions', $where );

			foreach( $table->joins as $join )
			{
				$counter = $counter->join( $join['from'], $join['where'] );
			}

			$totalCount = $counter->first();

			/* Display */
			\IPS\Output::i()->title	 = \IPS\Member::loggedIn()->language()->addToStack('online_users');
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'online', 'core', 'front' )->onlineUsersList( (string) $table, $totalCount );
		}
		else
		{
			return parent::manage();
		}
	}
}
