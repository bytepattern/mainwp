
// Init Per Group data
updatesoverview_updates_init_group_view = function() {
  jQuery( '.element_ui_view_values' ).each( function () {
    var parent = jQuery( this ).parent();
    var uid = jQuery( this ).attr( 'elem-uid' );
    var total = jQuery( this ).attr( 'total' );
    var can_update = jQuery( this ).attr( 'can-update' );

    if ( total == 0 ) {
      // carefully remove this, or it will causing error with display or according sorting
      jQuery( parent ).find( "[row-uid='" + uid + "']" ).next().remove(); // remove content according part
      jQuery( parent ).find( "[row-uid='" + uid + "']" ).remove(); // remove title according part
    } else {
      jQuery( parent ).find( "[total-uid='" + uid + "']" ).html( total + ' ' + ( total == 1 ? __( 'Update' ) : __( 'Updates' ) ) );
      jQuery( parent ).find( "[total-uid='" + uid + "']" ).attr( 'sort-value', total );
    }

    if ( can_update ) {
      jQuery( parent ).find( "[btn-all-uid='" + uid + "']" ).text( total == 1 ? __( 'Update' ) : __( 'Update All' ) ).show();
    }
  } );
}

// Update individual WP
updatesoverview_upgrade = function ( id, obj ) {

    var parent = jQuery( obj ).closest( '.mainwp-wordpress-update' );
    var upgradeElement = jQuery( parent ).find( '#wp-updated-' + id );

    if ( upgradeElement.val() != 0 )
      return false;


  updatesoverviewContinueAfterBackup = function ( pId, pUpgradeElement ) {
    return function () {
      jQuery( '.mainwp-wordpress-update[site_id="' + pId + '"] > td:last-child' ).html( '<i class="notched circle loading icon"></i> ' + __( 'Updating. Please wait...' ) );
      pUpgradeElement.val( 1 );
      var data = mainwp_secure_data( {
        action: 'mainwp_upgradewp',
        id: pId
      } );

      jQuery.post( ajaxurl, data, function ( response ) {        
        if ( response.error ) {          
          jQuery( '.mainwp-wordpress-update[site_id="' + pId + '"] > td:last-child' ).html( '<i class="red times icon"></i>' );
        } else {          
          jQuery( '.mainwp-wordpress-update[site_id="' + pId + '"] > td:last-child' ).html( '<i class="green check icon"></i>' );
        }


      }, 'json' );
    }
  }( id, upgradeElement );

  var sitesToUpdate = [ id ];
  var siteNames = [ ];

    siteNames[id] = jQuery( '.mainwp-wordpress-update[site_id="' + id + '"]' ).attr( 'site_name' );

    var msg =  __( 'Are you sure you want to update the Wordpress core files on the selected site?' );
    mainwp_confirm(msg, function(){
        return mainwp_updatesoverview_checkBackups( sitesToUpdate, siteNames );
    }, false, 1);
};

/** Update bulk **/

var websitesToUpgrade = [ ];
var updatesoverviewContinueAfterBackup = undefined;
var limitUpdateAll = 0;
var continueUpdatesAll = '', continueUpdatesSlug = '';
var continueUpdating = false;

jQuery( document ).on( 'click', '#updatesoverview-backup-ignore', function () {
    if ( updatesoverviewContinueAfterBackup != undefined ) {
        mainwpPopup( '#updatesoverview-backup-box' ).close();
        console.log( updatesoverviewContinueAfterBackup );
        updatesoverviewContinueAfterBackup();
        updatesoverviewContinueAfterBackup = undefined;
    }
} );


var dashboardActionName = '';
var starttimeDashboardAction = 0;
var countRealItemsUpdated = 0;
var couttItemsToUpdate = 0;
var itemsToUpdate = [];

updatesoverview_update_popup_init = function ( data ) {
    data = data || { };
    data.callback = function () {
        bulkTaskRunning = false;
        window.location.href = location.href;
    };
    data.statusText =  __( 'updated' );
    mainwpPopup( '#mainwp-sync-sites-modal' ).init( data );
}

// Update Group
updatesoverview_wordpress_global_upgrade_all = function ( groupId ) {
  if ( bulkTaskRunning )
    return false;

  //Step 1: build form
  var sitesToUpdate = [ ];
  var siteNames = { };
  var foundChildren = [ ];

  if ( typeof groupId !== 'undefined' ) {
    // groups selector is only one for each screen
    foundChildren = jQuery( '#update_wrapper_wp_upgrades_group_' + groupId ).find( 'tr.mainwp-wordpress-update[updated="0"]' );
  } else {
    // childs selector is only one for each screen
    foundChildren = jQuery( 'tr.mainwp-wordpress-update[updated="0"]' );
  }

  if ( foundChildren.length == 0 )
    return false;

  var sitesCount = 0;

  mainwpPopup( '#mainwp-sync-sites-modal' ).clearList();

  for ( var i = 0; i < foundChildren.length; i++ ) {
    if ( limitUpdateAll > 0 && i >= limitUpdateAll && typeof groupId === 'undefined' ) {
      continueUpdatesAll = 'wpcore_global_upgrade_all';
      break;
    }

    var child = foundChildren[i];
    var siteId = jQuery( child ).attr( 'site_id' );
    var siteName = jQuery( child ).attr( 'site_name' );

    if ( sitesToUpdate.indexOf( siteId ) == -1 ) {
      sitesCount++;
      sitesToUpdate.push( siteId );
      siteNames[siteId] = siteName;
    }
  }

  var _callback = function() {
    if ( typeof groupId !== 'undefined' ) {
    // ok
    } else

      for ( var j = 0; j < sitesToUpdate.length; j++ ) {
        mainwpPopup( '#mainwp-sync-sites-modal' ).appendItemsList( decodeURIComponent( siteNames[sitesToUpdate[j]] ) + ' (WordPress update)', '<span class="updatesoverview-upgrade-status-wp" siteid="' + sitesToUpdate[j] + '">' + '<i class="clock outline icon"></i> ' + '</span>' );
      }

      updatesoverviewContinueAfterBackup = function ( pSitesCount, pSitesToUpdate ) {
        return function () {
          var initData = {
              title: __( 'Updating All' ),
              total: pSitesCount,
              pMax: pSitesCount
          };

            updatesoverview_update_popup_init( initData );

            var dateObj = new Date();
            dashboardActionName = 'upgrade_all_wp_core';
            starttimeDashboardAction = dateObj.getTime();

          //Step 3: start updates
          updatesoverview_wordpress_upgrade_all_int( pSitesToUpdate );

          updatesoverviewContinueAfterBackup = undefined;
        }
      }( sitesCount, sitesToUpdate );

    return mainwp_updatesoverview_checkBackups( sitesToUpdate, siteNames );
  };

  // new confirm message
  if ( !continueUpdating ) {
    if ( jQuery( siteNames ).length > 0 ) {
      var sitesList = [ ];
      jQuery.each( siteNames, function ( index, value ) {
        if ( value ) { // to fix
          sitesList.push( decodeURIComponent( value ) );
        }
      } );
      var confirmMsg = __( 'You are about to update %1 on the following site(s): <br/><div class="ui message">%2</div> <strong>Do you want to proceed?</strong>', __( 'WordPress Core' ), sitesList.join( '<br />' ) );
      mainwp_confirm( confirmMsg,_callback, false, 2);
    }
    return false;
  }
  _callback();
  return false;
};

updatesoverview_wordpress_upgrade_all_int = function ( websiteIds )
{
    websitesToUpgrade = websiteIds;
    currentWebsite = 0;
    websitesDone = 0;
    websitesTotal = websitesLeft = websitesToUpgrade.length;

    bulkTaskRunning = true;
    updatesoverview_wordpress_upgrade_all_loop_next();
};
updatesoverview_wordpress_upgrade_all_loop_next = function ()
{
    while ( bulkTaskRunning && ( currentThreads < maxThreads ) && ( websitesLeft > 0 ) )
    {
        updatesoverview_wordpress_upgrade_all_upgrade_next();
    }
};
updatesoverview_wordpress_upgrade_all_update_site_status = function ( siteId, newStatus )
{
    jQuery( '.updatesoverview-upgrade-status-wp[siteid="' + siteId + '"]' ).html( newStatus );
};
updatesoverview_wordpress_upgrade_all_upgrade_next = function ()
{
    currentThreads++;
    websitesLeft--;

    var websiteId = websitesToUpgrade[currentWebsite++];
    updatesoverview_wordpress_upgrade_all_update_site_status( websiteId, __( '<i class="sync loading icon"></i>' ) );

    updatesoverview_wordpress_upgrade_int( websiteId, true );
};
updatesoverview_wordpress_upgrade_all_update_done = function ()
{
    currentThreads--;
    if ( !bulkTaskRunning )
        return;
    websitesDone++;
    mainwpPopup( '#mainwp-sync-sites-modal' ).setProgressValue( websitesDone );

    if ( websitesDone == websitesTotal )
    {
        updatesoverview_check_to_continue_updates();
        return;
    }

    updatesoverview_wordpress_upgrade_all_loop_next();
};
updatesoverview_wordpress_upgrade_int = function ( websiteId, bulkMode )
{

    var data = mainwp_secure_data( {
        action: 'mainwp_upgradewp',
        id: websiteId
    } );
    jQuery.post( ajaxurl, data, function ( pWebsiteId, pBulkMode )
    {
        return function ( response )
        {
            var result;
            if ( response.error )
            {
                result = getErrorMessage( response.error );
                if ( pBulkMode )
                    updatesoverview_wordpress_upgrade_all_update_site_status( pWebsiteId, '<i class="red times icon"></i>' );                
            } else
            {
                result = response.result;
                if ( pBulkMode )
                    updatesoverview_wordpress_upgrade_all_update_site_status( pWebsiteId, '<i class="green check icon"></i>' );
                countRealItemsUpdated++;
                couttItemsToUpdate++;
            }
            updatesoverview_wordpress_upgrade_all_update_done();
            if ( websitesDone == websitesTotal )
            {
                 updatesoverview_send_twitt_info();
            }
        }
    }( websiteId, bulkMode ), 'json' );

    return false;
};

var currentTranslationSlugToUpgrade = undefined;
var websitesTranslationSlugsToUpgrade = undefined;
updatesoverview_translations_global_upgrade_all = function ( groupId )
{
    if ( bulkTaskRunning )
        return false;

    //Step 1: build form
    var sitesToUpdate = [ ];
    var siteNames = { };
    var sitesTranslationSlugs = { };
    var foundChildren = [ ];

    if ( typeof groupId !== 'undefined' )
        foundChildren = jQuery( '#update_wrapper_translation_upgrades_group_' + groupId ).find( 'tr.mainwp-translation-update[updated="0"]' );
    else
        foundChildren = jQuery( '#translations-updates-global' ).find( 'table tr[updated="0"]' );

    if ( foundChildren.length == 0 )
        return false;
    var sitesCount = 0;

    mainwpPopup( '#mainwp-sync-sites-modal' ).clearList();

    for ( var i = 0; i < foundChildren.length; i++ )
    {
        if ( limitUpdateAll > 0 && i >= limitUpdateAll && typeof groupId === 'undefined' ) {
            continueUpdatesAll = 'translations_global_upgrade_all';
            break;
        }
        var child = jQuery( foundChildren[i] );
        var parent = child.parent(); // to fix

        var siteElement;
        var translationElement;

        var checkAttr = child.attr( 'site_id' );
        if ( ( typeof checkAttr !== 'undefined' ) && ( checkAttr !== false ) )
        {
            siteElement = child;
            translationElement = parent;
        } else
        {
            siteElement = parent;
            translationElement = child;
        }

        var siteId = siteElement.attr( 'site_id' );
        var siteName = siteElement.attr( 'site_name' );
        var translationSlug = translationElement.attr( 'translation_slug' );

        if ( sitesToUpdate.indexOf( siteId ) == -1 )
        {
            sitesCount++;
            sitesToUpdate.push( siteId );
            siteNames[siteId] = siteName;
        }
        if ( sitesTranslationSlugs[siteId] == undefined )
        {
            sitesTranslationSlugs[siteId] = translationSlug;
        } else
        {
            sitesTranslationSlugs[siteId] += ',' + translationSlug;
        }
    }

    var _callback = function() {
        if ( typeof groupId !== 'undefined' ) {
            // ok
        } else

        for ( var i = 0; i < sitesToUpdate.length; i++ )
        {
            var updateCount = sitesTranslationSlugs[sitesToUpdate[i]].match( /\,/g );
            if ( updateCount == null )
                updateCount = 1;
            else
                updateCount = updateCount.length + 1;

            mainwpPopup( '#mainwp-sync-sites-modal' ).appendItemsList( decodeURIComponent( siteNames[sitesToUpdate[i]] ) + ' (' + updateCount + ' translations)' , '<span class="updatesoverview-upgrade-status-wp" siteid="' + sitesToUpdate[i] + '">' + '<i class="clock outline icon"></i> ' + '</span>' );
        }

        updatesoverviewContinueAfterBackup = function ( pSitesCount, pSitesToUpdate, pSitesTranslationSlugs ) {
            return function ()
            {

                var initData = {
                    title: __( 'Updating all...' ),
                    total: pSitesCount,
                    pMax: pSitesCount
                };
                updatesoverview_update_popup_init( initData );

                var dateObj = new Date();
                dashboardActionName = 'upgrade_all_translations';
                starttimeDashboardAction = dateObj.getTime();
                countRealItemsUpdated = 0;
        
                //Step 3: start updates
                updatesoverview_translations_upgrade_all_int( undefined, pSitesToUpdate, pSitesTranslationSlugs );

                updatesoverviewContinueAfterBackup = undefined;
            }
        }( sitesCount, sitesToUpdate, sitesTranslationSlugs );


        return mainwp_updatesoverview_checkBackups( sitesToUpdate, siteNames );
    }


    // new confirm message
    if ( !continueUpdating ) {
        if ( jQuery( siteNames ).length > 0 ) {
            var sitesList = [ ];
            jQuery.each( siteNames, function ( index, value ) {
                if ( value ) { // to fix
                    sitesList.push( decodeURIComponent( value ) );
                }
            } );
            var confirmMsg = __( 'You are about to update %1 on the following site(s):\n%2?', 'translations', sitesList.join( ', ' ) );
            mainwp_confirm(confirmMsg, _callback, false, 2 );
        }
        return false;
    }
    _callback();
    return false;
};
updatesoverview_translations_upgrade_all = function ( slug, translationName )
{
    if ( bulkTaskRunning )
        return false;
    //Step 1: build form
    var sitesToUpdate = [ ];
    var siteNames = [ ];
//    var foundChildren = jQuery( 'div[translation_slug="' + slug + '"]' ).children( 'div[updated="0"]' );
    var foundChildren = jQuery( '.translations-bulk-updates[translation_slug="' + slug + '"]' ).find( 'tr[updated="0"]' );

    if ( foundChildren.length == 0 )
        return false;

    mainwpPopup( '#mainwp-sync-sites-modal' ).clearList();

    for ( var i = 0; i < foundChildren.length; i++ )
    {
        if ( limitUpdateAll > 0 && i >= limitUpdateAll ) {
            continueUpdatesAll = 'translations_upgrade_all';
            continueUpdatesSlug = slug;
            break;
        }
        var child = foundChildren[i];
        var siteId = jQuery( child ).attr( 'site_id' );
        var siteName = jQuery( child ).attr( 'site_name' );
        siteNames[siteId] = siteName;
        sitesToUpdate.push( siteId );
    }

    translationName = decodeURIComponent( translationName );
    translationName = translationName.replace( /\+/g, ' ' );

    var _callback = function() {

        for ( var i = 0; i < sitesToUpdate.length; i++ )
        {
            mainwpPopup( '#mainwp-sync-sites-modal' ).appendItemsList( decodeURIComponent( siteNames[sitesToUpdate[i]] ), '<span class="updatesoverview-upgrade-status-wp" siteid="' + sitesToUpdate[i] + '">' + '<i class="clock outline icon"></i> ' + '</span>' );
        }

        var sitesCount = sitesToUpdate.length;

        updatesoverviewContinueAfterBackup = function ( pSitesCount, pSlug, pSitesToUpdate ) {
            return function ()
            {

                // init and show popup

                var initData = {
                    title: __( 'Updating %1', decodeURIComponent( translationName ) ),
                    total: pSitesCount,
                    pMax: pSitesCount
                };
                updatesoverview_update_popup_init( initData );
                var dateObj = new Date();
                dashboardActionName = 'upgrade_all_translations';
                starttimeDashboardAction = dateObj.getTime();
                countRealItemsUpdated = 0;
                itemsToUpdate = [];

                //Step 3: start updates
                updatesoverview_translations_upgrade_all_int( pSlug, pSitesToUpdate );

                updatesoverviewContinueAfterBackup = undefined;
            }
        }( sitesCount, slug, sitesToUpdate );
        return mainwp_updatesoverview_checkBackups( sitesToUpdate, siteNames );
    }

    // new confirm message
    if ( !continueUpdating ) {
        if ( jQuery( siteNames ).length > 0 ) {
            var sitesList = [ ];
            jQuery.each( siteNames, function ( index, value ) {
                if ( value ) { // to fix
                    sitesList.push( decodeURIComponent( value ) );
                }
            } );
            var confirmMsg = __( 'You are about to update the %1 translation on the following site(s): <br/><div class="ui message">%2</div> <strong>Do you want to proceed?</strong>', translationName, sitesList.join( '<br />' ) );
            mainwp_confirm(confirmMsg, _callback, false, 2 );
        }
        return false;
    }

    _callback();
    return false;
};
updatesoverview_translations_upgrade_all_int = function ( slug, websiteIds, sitesTranslationSlugs )
{
    currentTranslationSlugToUpgrade = slug;
    websitesTranslationSlugsToUpgrade = sitesTranslationSlugs;
    websitesToUpdateTranslations = websiteIds;
    currentWebsite = 0;
    websitesDone = 0;
    websitesTotal = websitesLeft = websitesToUpdateTranslations.length;

    bulkTaskRunning = true;
    updatesoverview_translations_upgrade_all_loop_next();
};
updatesoverview_translations_upgrade_all_loop_next = function ()
{
    while ( bulkTaskRunning && ( currentThreads < maxThreads ) && ( websitesLeft > 0 ) )
    {
        updatesoverview_translations_upgrade_all_upgrade_next();
    }
};
updatesoverview_translations_upgrade_all_update_site_status = function ( siteId, newStatus )
{
    jQuery( '.updatesoverview-upgrade-status-wp[siteid="' + siteId + '"]' ).html( newStatus );
};
updatesoverview_translations_upgrade_all_upgrade_next = function ()
{
    currentThreads++;
    websitesLeft--;

    var websiteId = websitesToUpdateTranslations[currentWebsite++];
    updatesoverview_translations_upgrade_all_update_site_status( websiteId, '<i class="sync loading icon"></i>' );

    var slugToUpgrade = currentTranslationSlugToUpgrade;
    if ( slugToUpgrade == undefined )
        slugToUpgrade = websitesTranslationSlugsToUpgrade[websiteId];
    updatesoverview_translations_upgrade_int( slugToUpgrade, websiteId, true, true );
};

updatesoverview_translations_upgrade_all_update_done = function ()
{
    currentThreads--;
    if ( !bulkTaskRunning )
        return;
    websitesDone++;
    mainwpPopup( '#mainwp-sync-sites-modal' ).setProgressValue( websitesDone );

    if ( websitesDone == websitesTotal )
    {
        updatesoverview_check_to_continue_updates();
        return;
    }

    updatesoverview_translations_upgrade_all_loop_next();
};
updatesoverview_translations_upgrade_int = function ( slug, websiteId, bulkMode, noCheck )
{
    updatesoverviewContinueAfterBackup = function ( pSlug, pWebsiteId, pBulkMode ) {
        return function ()
        {
            var slugParts = pSlug.split( ',' );
            for ( var i = 0; i < slugParts.length; i++ )
            {
                var websiteHolder = jQuery( '.translations-bulk-updates[translation_slug="' + slugParts[i] + '"] tr[site_id="' + pWebsiteId + '"]' );
                if ( !websiteHolder.exists() )
                {
                    websiteHolder = jQuery( '.translations-bulk-updates[site_id="' + pWebsiteId + '"] tr[translation_slug="' + slugParts[i] + '"]' );
                }

                websiteHolder.find('td:last-child' ).html( '<i class="notched circle loading icon"></i> ' + __( 'Updating. Please wait...' ) );
            }

            var data = mainwp_secure_data( {
                action: 'mainwp_upgradeplugintheme',
                websiteId: pWebsiteId,
                type: 'translation',
                slug: pSlug
            } );
            jQuery.ajax( {
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function ( pSlug, pWebsiteId, pBulkMode )
                {
                    return function ( response )
                    {
                        var slugParts = pSlug.split( ',' );
                        var done = false;
                        for ( var i = 0; i < slugParts.length; i++ )
                        {
                            var result;
                            var websiteHolder = jQuery( '.translations-bulk-updates[translation_slug="' + slugParts[i] + '"] tr[site_id="' + pWebsiteId + '"]' );
                            if ( !websiteHolder.exists() )
                            {
                                websiteHolder = jQuery( '.translations-bulk-updates[site_id="' + pWebsiteId + '"] tr[translation_slug="' + slugParts[i] + '"]' );
                            }

                            if ( response.error )
                            {
                                result = getErrorMessage( response.error );
                                if ( !done && pBulkMode )
                                    updatesoverview_translations_upgrade_all_update_site_status( pWebsiteId, '<i class="red times icon"></i>' );
                                websiteHolder.find( 'td:last-child' ).html( '<i class="red times icon"></i>' );
                            } else
                            {
                                var res = response.result;

                                if ( res[slugParts[i]] )
                                {
                                    if ( !done && pBulkMode )
                                        updatesoverview_translations_upgrade_all_update_site_status( pWebsiteId, '<i class="green check icon"></i>' );
                                    websiteHolder.attr( 'updated', 1 );
                                    websiteHolder.find( 'td:last-child' ).html( '<i class="green check icon"></i>' );                                    
                                    countRealItemsUpdated++;
                                    if (itemsToUpdate.indexOf(slugParts[i]) == -1) itemsToUpdate.push(slugParts[i]);                                
                                } else
                                {
                                    if ( !done && pBulkMode )
                                        updatesoverview_translations_upgrade_all_update_site_status( pWebsiteId, '<i class="red times icon"></i>' );
                                    websiteHolder.find( 'td:last-child' ).html( '<i class="red times icon"></i>' );
                                }
                            }
                            if ( !done && pBulkMode )
                            {
                                updatesoverview_translations_upgrade_all_update_done();
                                done = true;
                            }
                        }
                        
                        if (websitesDone == websitesTotal)
                        {
                            couttItemsToUpdate = itemsToUpdate.length;
                            updatesoverview_send_twitt_info();
                        }
                    }
                }( pSlug, pWebsiteId, pBulkMode ),
                tryCount: 0,
                retryLimit: 3,
                endError: function ( pSlug, pWebsiteId, pBulkMode )
                {
                    return function ()
                    {
                        var slugParts = pSlug.split( ',' );
                        var done = false;
                        for ( var i = 0; i < slugParts.length; i++ )
                        {
                            var result;
                            var websiteHolder = jQuery( '.translations-bulk-updates[translation_slug="' + slugParts[i] + '"] tr[site_id="' + pWebsiteId + '"]' );
                            if ( !websiteHolder.exists() )
                            {
                                websiteHolder = jQuery( '.translations-bulk-updates[site_id="' + pWebsiteId + '"] tr[translation_slug="' + slugParts[i] + '"]' );
                            }

                            result = __( 'FAILED' );
                            if ( !done && pBulkMode )
                            {
                                updatesoverview_translations_upgrade_all_update_site_status( pWebsiteId, '<span class="mainwp-red"><i class="exclamation icon"></i> ' + __( 'FAILED' ) + '</span>' );
                                updatesoverview_translations_upgrade_all_update_done();
                                done = true;
                            }

                            websiteHolder.find( 'td:last-child' ).html( result );
                        }
                        
                        if (websitesDone == websitesTotal)
                        {
                            couttItemsToUpdate = itemsToUpdate.length;
                            updatesoverview_send_twitt_info();
                        }                    
                    }
                }( pSlug, pWebsiteId, pBulkMode ),
                error: function ( xhr ) {
                    this.tryCount++;
                    if ( this.tryCount >= this.retryLimit ) {
                        this.endError();
                        return;
                    }

                    var fnc = function ( pRqst, pXhr ) {
                        return function () {
                            if ( pXhr.status == 404 ) {
                                //handle error
                                jQuery.ajax( pRqst );
                            } else if ( pXhr.status == 500 ) {
                                //handle error
                            } else {
                                //handle error
                            }
                        }
                    }( this, xhr );
                    setTimeout( fnc, 500 );
                },
                dataType: 'json'
            } );

            updatesoverviewContinueAfterBackup = undefined;
        }
    }( slug, websiteId, bulkMode );

    if ( noCheck )
    {
        updatesoverviewContinueAfterBackup();
        return false;
    }

    var sitesToUpdate = [ websiteId ];
    var siteNames = [ ];
    siteNames[websiteId] = jQuery( 'div[site_id="' + websiteId + '"]' ).attr( 'site_name' );

    return mainwp_updatesoverview_checkBackups( sitesToUpdate, siteNames );
};
var currentPluginSlugToUpgrade = undefined;
var websitesPluginSlugsToUpgrade = undefined;
updatesoverview_plugins_global_upgrade_all = function ( groupId )
{
    if ( bulkTaskRunning )
        return false;

    //Step 1: build form
    var sitesToUpdate = [ ];
    var siteNames = { };
    var sitesPluginSlugs = { };
    var foundChildren = [ ];
    if ( typeof groupId !== 'undefined' )
        foundChildren = jQuery( '#update_wrapper_plugin_upgrades_group_' + groupId ).find( 'tr.mainwp-plugin-update[updated="0"]' );
    else
        foundChildren = jQuery( '#plugins-updates-global' ).find( 'table tr[updated="0"]' );

    if ( foundChildren.length == 0 )
        return false;
    var sitesCount = 0;

    mainwpPopup( '#mainwp-sync-sites-modal' ).clearList();

    for ( var i = 0; i < foundChildren.length; i++ )
    {
        if ( limitUpdateAll > 0 && i >= limitUpdateAll && typeof groupId === 'undefined' ) {
            continueUpdatesAll = 'plugins_global_upgrade_all';
            break;
        }
        var child = jQuery( foundChildren[i] );
        var parent = child.parent(); // to fix

        var siteElement;
        var pluginElement;

        var checkAttr = child.attr( 'site_id' );
        if ( ( typeof checkAttr !== 'undefined' ) && ( checkAttr !== false ) )
        {
            siteElement = child;
            pluginElement = parent;
        } else
        {
            siteElement = parent;
            pluginElement = child;
        }

        var siteId = siteElement.attr( 'site_id' );
        var siteName = siteElement.attr( 'site_name' );
        var pluginSlug = pluginElement.attr( 'plugin_slug' );

        if ( sitesToUpdate.indexOf( siteId ) == -1 )
        {
            sitesCount++;
            sitesToUpdate.push( siteId );
            siteNames[siteId] = siteName;
        }
        if ( sitesPluginSlugs[siteId] == undefined )
        {
            sitesPluginSlugs[siteId] = pluginSlug;
        } else
        {
            sitesPluginSlugs[siteId] += ',' + pluginSlug;
        }
    }

    var _callback = function() {
        if ( typeof groupId !== 'undefined' ) {
            // ok
        } else

        for ( var i = 0; i < sitesToUpdate.length; i++ )
        {
            var updateCount = sitesPluginSlugs[sitesToUpdate[i]].match( /\,/g );
            if ( updateCount == null )
                updateCount = 1;
            else
                updateCount = updateCount.length + 1;
            mainwpPopup( '#mainwp-sync-sites-modal' ).appendItemsList( decodeURIComponent( siteNames[sitesToUpdate[i]] ) + ' (' + updateCount + ' plugins)', '<span class="updatesoverview-upgrade-status-wp" siteid="' + sitesToUpdate[i] + '">' + '<i class="clock outline icon"></i> ' + '</span>' );
        }

        updatesoverviewContinueAfterBackup = function ( pSitesCount, pSitesToUpdate, pSitesPluginSlugs ) {
            return function ()
            {

                var initData = {
                    title: __( 'Updating all' ),
                    total: pSitesCount,
                    pMax: pSitesCount
                };
                updatesoverview_update_popup_init( initData );

                var dateObj = new Date();
                dashboardActionName = 'upgrade_all_plugins';
                starttimeDashboardAction = dateObj.getTime();
                countRealItemsUpdated = 0;
        
                //Step 3: start updates
                updatesoverview_plugins_upgrade_all_int( undefined, pSitesToUpdate, pSitesPluginSlugs );

                updatesoverviewContinueAfterBackup = undefined;
            }
        }( sitesCount, sitesToUpdate, sitesPluginSlugs );


        return mainwp_updatesoverview_checkBackups( sitesToUpdate, siteNames );
    }

    // new confirm message
    if ( !continueUpdating ) {
        if ( jQuery( siteNames ).length > 0 ) {
            var sitesList = [ ];
            jQuery.each( siteNames, function ( index, value ) {
                if ( value ) { // to fix
                    sitesList.push( decodeURIComponent( value ) );
                }
            } );
            var confirmMsg = __( 'You are about to update %1 on the following site(s): <br/><div class="ui message">%2</div> <strong>Do you want to proceed?</strong>', __( 'plugins' ), sitesList.join( '<br />' ) );
            mainwp_confirm(confirmMsg, _callback, false, 2 );
        }
        return false;
    }
    _callback();
    return false;
};
updatesoverview_plugins_upgrade_all = function ( slug, pluginName )
{
    if ( bulkTaskRunning )
        return false;

    //Step 1: build form
    var sitesToUpdate = [ ];
    var siteNames = [ ];
//    var foundChildren = jQuery( 'div[plugin_slug="' + slug + '"]' ).children( 'div[updated="0"]' );
    var foundChildren = jQuery( 'tr[plugin_slug="' + slug + '"]' ).find( 'table tr[updated="0"]' );

    if ( foundChildren.length == 0 )
        return false;
    mainwpPopup( '#mainwp-sync-sites-modal' ).clearList();

    for ( var i = 0; i < foundChildren.length; i++ )
    {
        if ( limitUpdateAll > 0 && i >= limitUpdateAll ) {
            continueUpdatesAll = 'plugins_upgrade_all';
            continueUpdatesSlug = slug;
            break;
        }
        var child = foundChildren[i];
        var siteId = jQuery( child ).attr( 'site_id' );
        var siteName = jQuery( child ).attr( 'site_name' );
        siteNames[siteId] = siteName;
        sitesToUpdate.push( siteId );
    }

    console.log(sitesToUpdate);

    pluginName = decodeURIComponent( pluginName );
    pluginName = pluginName.replace( /\+/g, ' ' );

    var _callback = function() {

        for ( var i = 0; i < sitesToUpdate.length; i++ )
        {
            mainwpPopup( '#mainwp-sync-sites-modal' ).appendItemsList( decodeURIComponent( siteNames[sitesToUpdate[i]] ),  '<span class="updatesoverview-upgrade-status-wp" siteid="' + sitesToUpdate[i] + '">' + '<i class="clock outline icon"></i> ' + '</span>' );
        }

        var sitesCount = sitesToUpdate.length;

        updatesoverviewContinueAfterBackup = function ( pSitesCount, pSlug, pSitesToUpdate ) {
            return function ()
            {

                var initData = {
                    title: __( 'Updating %1', decodeURIComponent( pluginName ) ),
                    total: pSitesCount,
                    pMax: pSitesCount
                };
                updatesoverview_update_popup_init( initData );

                var dateObj = new Date();
                dashboardActionName = 'upgrade_all_plugins';
                starttimeDashboardAction = dateObj.getTime();
                countRealItemsUpdated = 0;
        
                //Step 3: start updates
                updatesoverview_plugins_upgrade_all_int( pSlug, pSitesToUpdate );

                updatesoverviewContinueAfterBackup = undefined;
            }
        }( sitesCount, slug, sitesToUpdate );

        return mainwp_updatesoverview_checkBackups( sitesToUpdate, siteNames );
    }

    // new confirm message
    if ( !continueUpdating ) {
        if ( siteNames.length > 0 ) {
            var sitesList = [ ];
            jQuery.each( siteNames, function ( index, value ) {
                if ( value ) { // to fix
                    sitesList.push( decodeURIComponent( value ) );
                }
            } );
            var confirmMsg = __( 'You are about to update the %1 plugin on the following site(s): <br/><div class="ui message">%2</div> <strong>Do you want to proceed?</strong>', pluginName, sitesList.join( '<br />' ) );
            mainwp_confirm(confirmMsg, _callback, false, 2 );
        }
        return false;
    }
    _callback();
    return false;

};
updatesoverview_plugins_upgrade_all_int = function ( slug, websiteIds, sitesPluginSlugs )
{
    currentPluginSlugToUpgrade = slug;
    websitesPluginSlugsToUpgrade = sitesPluginSlugs;
    websitesToUpdatePlugins = websiteIds;
    currentWebsite = 0;
    websitesDone = 0;
    websitesTotal = websitesLeft = websitesToUpdatePlugins.length;

    bulkTaskRunning = true;
    updatesoverview_plugins_upgrade_all_loop_next();
};
updatesoverview_plugins_upgrade_all_loop_next = function ()
{
    while ( bulkTaskRunning && ( currentThreads < maxThreads ) && ( websitesLeft > 0 ) )
    {
        updatesoverview_plugins_upgrade_all_upgrade_next();
    }
};
updatesoverview_plugins_upgrade_all_update_site_status = function ( siteId, newStatus )
{
    jQuery( '.updatesoverview-upgrade-status-wp[siteid="' + siteId + '"]' ).html( newStatus );
};
updatesoverview_plugins_upgrade_all_upgrade_next = function ()
{
    currentThreads++;
    websitesLeft--;

    var websiteId = websitesToUpdatePlugins[currentWebsite++];
    updatesoverview_plugins_upgrade_all_update_site_status( websiteId, '<i class="sync loading icon"></i>' );

    var slugToUpgrade = currentPluginSlugToUpgrade;
    if ( slugToUpgrade == undefined )
        slugToUpgrade = websitesPluginSlugsToUpgrade[websiteId];
    updatesoverview_plugins_upgrade_int( slugToUpgrade, websiteId, true, true );
};


updatesoverview_send_twitt_info = function() {
    var send = false;
    if (mainwpParams.enabledTwit == true) {
        var dateObj = new Date();
        var countSec = (dateObj.getTime() - starttimeDashboardAction) / 1000;
        if (countSec <= mainwpParams.maxSecondsTwit) {
            send = true;
            var data = {
                action:'mainwp_twitter_dashboard_action',
                actionName: dashboardActionName,
                countSites: websitesDone,
                countSeconds: countSec,
                countItems: couttItemsToUpdate,
                countRealItems: countRealItemsUpdated
            };
            jQuery.post(ajaxurl, data, function () {                
            });
        }
    }
    return send;
};

updatesoverview_check_to_continue_updates = function () {
    var loc_href = location.href;
    if ( limitUpdateAll > 0 && continueUpdatesAll != '' ) {
        if ( loc_href.indexOf( "&continue_update=" ) == -1 ) {
            var loc_href = loc_href + '&continue_update=' + continueUpdatesAll;
            if ( continueUpdatesAll == 'plugins_upgrade_all' || continueUpdatesAll == 'themes_upgrade_all' || continueUpdatesAll == 'translations_upgrade_all' ) {
                loc_href += '&slug=' + continueUpdatesSlug;
            }
        }
    } else {
        if ( loc_href.indexOf( "page=mainwp_tab" ) != -1 ) {
            loc_href = 'admin.php?page=mainwp_tab';
        } else {
            loc_href = 'admin.php?page=UpdatesManage';
        }
    }
    setTimeout( function ()
    {
        bulkTaskRunning = false;
        mainwpPopup( '#mainwp-sync-sites-modal' ).close(true);
    }, 3000 );
    return false;
}

updatesoverview_plugins_upgrade_all_update_done = function ()
{
    currentThreads--;
    if ( !bulkTaskRunning )
        return;
    websitesDone++;

    mainwpPopup( '#mainwp-sync-sites-modal' ).setProgressValue( websitesDone );

    if ( websitesDone == websitesTotal )
    {
        updatesoverview_check_to_continue_updates();
        return;
    }

    updatesoverview_plugins_upgrade_all_loop_next();
};
updatesoverview_plugins_upgrade_int = function ( slug, websiteId, bulkMode, noCheck )
{
    updatesoverviewContinueAfterBackup = function ( pSlug, pWebsiteId, pBulkMode ) {
        return function ()
        {
            var slugParts = pSlug.split( ',' );
            for ( var i = 0; i < slugParts.length; i++ )
            {
                var websiteHolder = jQuery( '.plugins-bulk-updates[plugin_slug="' + slugParts[i] + '"] tr[site_id="' + pWebsiteId + '"]' );
                if ( !websiteHolder.exists() )
                {
                    websiteHolder = jQuery( '.plugins-bulk-updates[site_id="' + pWebsiteId + '"] tr[plugin_slug="' + slugParts[i] + '"]' );
                }
                websiteHolder.find('td:last-child' ).html( '<i class="notched circle loading icon"></i> ' + __( 'Updating. Please wait...' ) );
            }

            var data = mainwp_secure_data( {
                action: 'mainwp_upgradeplugintheme',
                websiteId: pWebsiteId,
                type: 'plugin',
                slug: pSlug
            } );
            jQuery.ajax( {
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function ( pSlug, pWebsiteId, pBulkMode )
                {
                    return function ( response )
                    {
                        var slugParts = pSlug.split( ',' );
                        var done = false;
                        for ( var i = 0; i < slugParts.length; i++ )
                        {
                            var result;
                            var websiteHolder = jQuery( '.plugins-bulk-updates[plugin_slug="' + slugParts[i] + '"] tr[site_id="' + pWebsiteId + '"]' );
                            if ( !websiteHolder.exists() )
                            {
                                websiteHolder = jQuery( '.plugins-bulk-updates[site_id="' + pWebsiteId + '"] tr[plugin_slug="' + slugParts[i] + '"]' );
                            }

                            if ( response.error )
                            {
                                if ( !done && pBulkMode )
                                    updatesoverview_plugins_upgrade_all_update_site_status( pWebsiteId, '<i class="red times icon"></i>' );
                                websiteHolder.find( 'td:last-child' ).html( '<i class="red times icon"></i>' );
                            } else
                            {
                                var res = response.result;

                                if ( res[slugParts[i]] )
                                {
                                    if ( !done && pBulkMode )
                                        updatesoverview_plugins_upgrade_all_update_site_status( pWebsiteId, '<i class="green check icon"></i>' + ' ' + mainwp_links_visit_site_and_admin('', pWebsiteId) );
                                    websiteHolder.attr( 'updated', 1 );
                                    websiteHolder.find( 'td:last-child' ).html( '<i class="green check icon"></i>' + ' ' + mainwp_links_visit_site_and_admin('', pWebsiteId) );
                                    
                                    countRealItemsUpdated++;
                                    if (itemsToUpdate.indexOf(slugParts[i]) == -1) itemsToUpdate.push(slugParts[i]);
                                
                                } else
                                {
                                    if ( !done && pBulkMode )
                                        updatesoverview_plugins_upgrade_all_update_site_status( pWebsiteId, '<i class="red times icon"></i>' );
                                    websiteHolder.find( 'td:last-child' ).html( '<i class="red times icon"></i>' );
                                }
                            }
                            if ( !done && pBulkMode )
                            {
                                updatesoverview_plugins_upgrade_all_update_done();
                                done = true;
                            }
//                            websiteHolder.find( 'td:last-child' ).html( result );
                        }
                        
                        if (websitesDone == websitesTotal)
                        {
                            couttItemsToUpdate = itemsToUpdate.length;
                            updatesoverview_send_twitt_info();
                        }
                    }
                }( pSlug, pWebsiteId, pBulkMode ),
                tryCount: 0,
                retryLimit: 3,
                endError: function ( pSlug, pWebsiteId, pBulkMode )
                {
                    return function ()
                    {
                        var slugParts = pSlug.split( ',' );
                        var done = false;
                        for ( var i = 0; i < slugParts.length; i++ )
                        {                            
                            //Siteview
                            var websiteHolder = jQuery( 'div[plugin_slug="' + slugParts[i] + '"] div[site_id="' + pWebsiteId + '"]' );
                            if ( !websiteHolder.exists() )
                            {
                                websiteHolder = jQuery( 'div[site_id="' + pWebsiteId + '"] div[plugin_slug="' + slugParts[i] + '"]' );
                            }
                            
                            if ( !done && pBulkMode )
                            {
                                updatesoverview_plugins_upgrade_all_update_site_status( pWebsiteId, '<i class="red times icon"></i>' );
                                updatesoverview_plugins_upgrade_all_update_done();
                                done = true;
                            }
                            websiteHolder.find( 'td:last-child' ).html( '<i class="red times icon"></i>' );
                        }
                                                
                        if (websitesDone == websitesTotal)
                        {
                            couttItemsToUpdate = itemsToUpdate.length;
                            updatesoverview_send_twitt_info();
                        }
                    }
                }( pSlug, pWebsiteId, pBulkMode ),
                error: function ( xhr ) {
                    this.tryCount++;
                    if ( this.tryCount >= this.retryLimit ) {
                        this.endError();
                        return;
                    }

                    var fnc = function ( pRqst, pXhr ) {
                        return function () {
                            if ( pXhr.status == 404 ) {
                                //handle error
                                jQuery.ajax( pRqst );
                            } else if ( pXhr.status == 500 ) {
                                //handle error
                            } else {
                                //handle error
                            }
                        }
                    }( this, xhr );
                    setTimeout( fnc, 500 );
                },
                dataType: 'json'
            } );

            updatesoverviewContinueAfterBackup = undefined;
        }
    }( slug, websiteId, bulkMode );

    if ( noCheck )
    {
        updatesoverviewContinueAfterBackup();
        return false;
    }

    var sitesToUpdate = [ websiteId ];
    var siteNames = [ ];
    siteNames[websiteId] = jQuery( 'div[site_id="' + websiteId + '"]' ).attr( 'site_name' );

    return mainwp_updatesoverview_checkBackups( sitesToUpdate, siteNames );
};

var currentThemeSlugToUpgrade = undefined;
var websitesThemeSlugsToUpgrade = undefined;
updatesoverview_themes_global_upgrade_all = function ( groupId )
{
    if ( bulkTaskRunning )
        return false;

    //Step 1: build form
    var sitesToUpdate = [ ];
    var siteNames = { };
    var sitesPluginSlugs = { };
    var foundChildren = [ ];
    if ( typeof groupId !== 'undefined' )
        foundChildren = jQuery( '#update_wrapper_theme_upgrades_group_' + groupId ).find( 'tr.mainwp-theme-update[updated="0"]' );
    else
        foundChildren = jQuery( '#themes-updates-global' ).find( 'table tr[updated="0"]' );

    if ( foundChildren.length == 0 )
        return false;
    var sitesCount = 0;

    mainwpPopup( '#mainwp-sync-sites-modal' ).clearList();

    for ( var i = 0; i < foundChildren.length; i++ )
    {
        if ( limitUpdateAll > 0 && i >= limitUpdateAll && typeof groupId === 'undefined' ) {
            continueUpdatesAll = 'themes_global_upgrade_all';
            break;
        }
        var child = jQuery( foundChildren[i] );
        var parent = child.parent(); // to fix

        var siteElement;
        var themeElement;

        var checkAttr = child.attr( 'site_id' );
        if ( ( typeof checkAttr !== 'undefined' ) && ( checkAttr !== false ) )
        {
            siteElement = child;
            themeElement = parent;
        } else
        {
            siteElement = parent;
            themeElement = child;
        }

        var siteId = siteElement.attr( 'site_id' );
        var siteName = siteElement.attr( 'site_name' );
        var themeSlug = themeElement.attr( 'theme_slug' );

        if ( sitesToUpdate.indexOf( siteId ) == -1 )
        {
            sitesCount++;
            sitesToUpdate.push( siteId );
            siteNames[siteId] = siteName;
        }
        if ( sitesPluginSlugs[siteId] == undefined )
        {
            sitesPluginSlugs[siteId] = themeSlug;
        } else
        {
            sitesPluginSlugs[siteId] += ',' + themeSlug;
        }
    }

    var _callback = function() {
        if ( typeof groupId !== 'undefined' ) {
            // ok
        } else

        for ( var i = 0; i < sitesToUpdate.length; i++ )
        {
            var updateCount = sitesPluginSlugs[sitesToUpdate[i]].match( /\,/g );
            if ( updateCount == null )
                updateCount = 1;
            else
                updateCount = updateCount.length + 1;
            mainwpPopup( '#mainwp-sync-sites-modal' ).appendItemsList( decodeURIComponent( siteNames[sitesToUpdate[i]] ) + ' (' + updateCount + ' themes)' , '<span class="updatesoverview-upgrade-status-wp" siteid="' + sitesToUpdate[i] + '">' + '<i class="clock outline icon"></i> ' + '</span>' );
        }

        updatesoverviewContinueAfterBackup = function ( pSitesCount, pSitesToUpdate, pSitesPluginSlugs ) {
            return function ()
            {

                var initData = {
                    title: __( 'Updating all...' ),
                    total: pSitesCount,
                    pMax: pSitesCount
                };
                updatesoverview_update_popup_init( initData );

                var dateObj = new Date();
                dashboardActionName = 'upgrade_all_themes';
                starttimeDashboardAction = dateObj.getTime();
                countRealItemsUpdated = 0;
        
                //Step 3: start updates
                updatesoverview_themes_upgrade_all_int( undefined, pSitesToUpdate, pSitesPluginSlugs );

                updatesoverviewContinueAfterBackup = undefined;
            }
        }( sitesCount, sitesToUpdate, sitesPluginSlugs );

        return mainwp_updatesoverview_checkBackups( sitesToUpdate, siteNames );
    }

    // new confirm message
    if ( !continueUpdating ) {
        if ( jQuery( siteNames ).length > 0 ) {
            var sitesList = [ ];
            jQuery.each( siteNames, function ( index, value ) {
                if ( value ) { // to fix
                    sitesList.push( decodeURIComponent( value ) );
                }
            } );
            var confirmMsg = __( 'You are about to update %1 on the following site(s): <br/><div class="ui message">%2</div> <strong>Do you want to proceed?</strong>', __( 'themes' ), sitesList.join( '<br />' ) );
            mainwp_confirm(confirmMsg, _callback, false, 2 );
        }
        return false;
    }
    _callback();
    return false;
};

updatesoverview_themes_upgrade_all = function ( slug, themeName )
{
    if ( bulkTaskRunning )
        return false;

    //Step 1: build form
    var sitesToUpdate = [ ];
    var siteNames = [ ];
    //var foundChildren = jQuery( 'div[theme_slug="' + slug + '"]' ).children( 'div[updated="0"]' );
    var foundChildren = jQuery( 'tr[theme_slug="' + slug + '"]' ).find( 'table tr[updated="0"]' );
    if ( foundChildren.length == 0 )
        return false;


    mainwpPopup( '#mainwp-sync-sites-modal' ).clearList();

    for ( var i = 0; i < foundChildren.length; i++ )
    {
        if ( limitUpdateAll > 0 && i >= limitUpdateAll ) {
            continueUpdatesAll = 'themes_upgrade_all';
            continueUpdatesSlug = slug;
            break;
        }
        var child = foundChildren[i];
        var siteId = jQuery( child ).attr( 'site_id' );
        var siteName = jQuery( child ).attr( 'site_name' );
        siteNames[siteId] = siteName;
        sitesToUpdate.push( siteId );
        mainwpPopup( '#mainwp-sync-sites-modal' ).appendItemsList( decodeURIComponent( siteName ), '<span class="updatesoverview-upgrade-status-wp" siteid="' + siteId + '">' + '<i class="clock outline icon"></i> ' + '</span>' );
    }

    themeName = decodeURIComponent( themeName );
    themeName = themeName.replace( /\+/g, ' ' );

    var _callback = function() {

        var sitesCount = sitesToUpdate.length;
        updatesoverviewContinueAfterBackup = function ( pSitesCount, pSlug, pSitesToUpdate ) {
            return function ()
            {
                //Step 2: show form

                var initData = {
                    title: __( 'Updating %1', decodeURIComponent( themeName ) ),
                    total: pSitesCount,
                    pMax: pSitesCount
                };
                updatesoverview_update_popup_init( initData );

                var dateObj = new Date();
                dashboardActionName = 'upgrade_all_themes';
                starttimeDashboardAction = dateObj.getTime();
                itemsToUpdate = [];
        
                //Step 3: start updates
                updatesoverview_themes_upgrade_all_int( pSlug, pSitesToUpdate );

                updatesoverviewContinueAfterBackup = undefined;
            }
        }( sitesCount, slug, sitesToUpdate );

        return mainwp_updatesoverview_checkBackups( sitesToUpdate, siteNames );
    }

    // new confirm message
    if ( !continueUpdating ) {
        if ( jQuery( siteNames ).length > 0 ) {
            var sitesList = [ ];
            jQuery.each( siteNames, function ( index, value ) {
                if ( value ) { // to fix
                    sitesList.push( decodeURIComponent( value ) );
                }
            } );
            var confirmMsg = __( 'You are about to update the %1 theme on the following site(s): <br/><div class="ui message">%2</div> <strong>Do you want to proceed?</strong>', themeName, sitesList.join( '<br />' ) );
            mainwp_confirm(confirmMsg, _callback, false, 2 );
        }
        return false;
    }
    _callback();
    return false;
};
updatesoverview_themes_upgrade_all_int = function ( slug, websiteIds, sitesThemeSlugs )
{
    currentThemeSlugToUpgrade = slug;
    websitesThemeSlugsToUpgrade = sitesThemeSlugs;
    websitesToUpdate = websiteIds;
    currentWebsite = 0;
    websitesDone = 0;
    websitesTotal = websitesLeft = websitesToUpdate.length;

    bulkTaskRunning = true;
    updatesoverview_themes_upgrade_all_loop_next();
};
updatesoverview_themes_upgrade_all_loop_next = function ()
{
    while ( bulkTaskRunning && ( currentThreads < maxThreads ) && ( websitesLeft > 0 ) )
    {
        updatesoverview_themes_upgrade_all_upgrade_next();
    }
};
updatesoverview_themes_upgrade_all_update_site_status = function ( siteId, newStatus )
{
    jQuery( '.updatesoverview-upgrade-status-wp[siteid="' + siteId + '"]' ).html( newStatus );
};
updatesoverview_themes_upgrade_all_upgrade_next = function ()
{
    currentThreads++;
    websitesLeft--;

    var websiteId = websitesToUpdate[currentWebsite++];
    updatesoverview_themes_upgrade_all_update_site_status( websiteId, '<i class="sync loading icon"></i>' );

    var slugToUpgrade = currentThemeSlugToUpgrade;
    if ( slugToUpgrade == undefined )
        slugToUpgrade = websitesThemeSlugsToUpgrade[websiteId];
    updatesoverview_themes_upgrade_int( slugToUpgrade, websiteId, true );
};
updatesoverview_themes_upgrade_all_update_done = function ()
{
    currentThreads--;
    if ( !bulkTaskRunning )
        return;
    websitesDone++;

    mainwpPopup( '#mainwp-sync-sites-modal' ).setProgressValue( websitesDone );

    if ( websitesDone == websitesTotal )
    {
        updatesoverview_check_to_continue_updates();
        return;
    }

    updatesoverview_themes_upgrade_all_loop_next();
};
updatesoverview_themes_upgrade_int = function ( slug, websiteId, bulkMode )
{
    var slugParts = slug.split( ',' );
    for ( var i = 0; i < slugParts.length; i++ )
    {
        var websiteHolder = jQuery( '.themes-bulk-updates[theme_slug="' + slugParts[i] + '"] tr[site_id="' + websiteId + '"]' );
        if ( !websiteHolder.exists() )
        {
            websiteHolder = jQuery( '.themes-bulk-updates[site_id="' + websiteId + '"] tr[theme_slug="' + slugParts[i] + '"]' );
        }
        websiteHolder.find('td:last-child' ).html( '<i class="notched circle loading icon"></i> ' + __( 'Updating. Please wait...' ) );

    }

    var data = mainwp_secure_data( {
        action: 'mainwp_upgradeplugintheme',
        websiteId: websiteId,
        type: 'theme',
        slug: slug
    } );
    jQuery.ajax( {
        type: "POST",
        url: ajaxurl,
        data: data,
        success: function ( pSlug, pWebsiteId, pBulkMode )
        {
            return function ( response )
            {
                var slugParts = pSlug.split( ',' );
                var done = false;
                for ( var i = 0; i < slugParts.length; i++ )
                {                    
                    var websiteHolder = jQuery( '.themes-bulk-updates[theme_slug="' + slugParts[i] + '"] tr[site_id="' + websiteId + '"]' );
                    if ( !websiteHolder.exists() )
                    {
                        websiteHolder = jQuery( '.themes-bulk-updates[site_id="' + websiteId + '"] tr[theme_slug="' + slugParts[i] + '"]' );
                    }

                    if ( response.error )
                    {
                        if ( !done && pBulkMode )
                            updatesoverview_themes_upgrade_all_update_site_status( pWebsiteId, '<i class="red times icon"></i>' );
                        websiteHolder.find( 'td:last-child' ).html( '<i class="red times icon"></i>' );
                    } else
                    {
                        var res = response.result;

                        if ( res[slugParts[i]] )
                        {
                            if ( !done && pBulkMode )
                                updatesoverview_themes_upgrade_all_update_site_status( pWebsiteId, '<i class="green check icon"></i>' + ' ' + mainwp_links_visit_site_and_admin('', websiteId) );
                            websiteHolder.attr( 'updated', 1 );
                            websiteHolder.find( 'td:last-child' ).html( '<i class="green check icon"></i>' + ' ' + mainwp_links_visit_site_and_admin('', websiteId) );

                            countRealItemsUpdated++;
                            if (itemsToUpdate.indexOf(slugParts[i]) == -1) itemsToUpdate.push(slugParts[i]);
                        } else
                        {
                            if ( !done && pBulkMode )
                                updatesoverview_themes_upgrade_all_update_site_status( pWebsiteId, '<i class="red times icon"></i>' );
                            websiteHolder.find( 'td:last-child' ).html( '<i class="red times icon"></i>' );
                        }

                    }
                    if ( !done && pBulkMode )
                    {
                        updatesoverview_themes_upgrade_all_update_done();
                        done = true;
                    }
                                        
                    if (websitesDone == websitesTotal)
                    {
                        couttItemsToUpdate = itemsToUpdate.length;
                        updatesoverview_send_twitt_info();
                    }
                }
            }
        }( slug, websiteId, bulkMode ),
        tryCount: 0,
        retryLimit: 3,
        endError: function ( pSlug, pWebsiteId, pBulkMode )
        {
            return function ()
            {
                var slugParts = pSlug.split( ',' );
                var done = false;
                for ( var i = 0; i < slugParts.length; i++ )
                {
                    var result;
                    var websiteHolder = jQuery( 'div[theme_slug="' + slugParts[i] + '"] div[site_id="' + pWebsiteId + '"]' );
                    if ( !websiteHolder.exists() )
                    {
                        websiteHolder = jQuery( 'div[site_id="' + pWebsiteId + '"] div[theme_slug="' + slugParts[i] + '"]' );
                    }

                    if ( !done && pBulkMode )
                    {
                        updatesoverview_themes_upgrade_all_update_site_status( pWebsiteId, '<i class="red times icon"></i>' );
                        updatesoverview_themes_upgrade_all_update_done();
                        done = true;
                    }
                    websiteHolder.find( 'td:last-child' ).html( '<i class="red times icon"></i>' );
                }
                
                if (websitesDone == websitesTotal)
                {
                    couttItemsToUpdate = itemsToUpdate.length;
                    updatesoverview_send_twitt_info();
                }
            }
        }( slug, websiteId, bulkMode ),
        error: function ( xhr ) {
            this.tryCount++;
            if ( this.tryCount >= this.retryLimit ) {
                this.endError();
                return;
            }

            var fnc = function ( pRqst, pXhr ) {
                return function () {
                    if ( pXhr.status == 404 ) {
                        //handle error
                        jQuery.ajax( pRqst );
                    } else if ( pXhr.status == 500 ) {
                        //handle error
                    } else {
                        //handle error
                    }
                }
            }( this, xhr );
            setTimeout( fnc, 500 );
        },
        dataType: 'json'
    } );

    return false;
};

updatesoverview_global_upgrade_all = function ( which )
{

    if ( bulkTaskRunning )
        return false;

    //Step 1: build form
    var sitesToUpdate = [ ];
    var sitesToUpgrade = [ ];
    var sitesPluginSlugs = { };
    var sitesThemeSlugs = { };
    var sitesTranslationSlugs = { };
    var siteNames = { };

    mainwpPopup( '#mainwp-sync-sites-modal' ).clearList();

    var sitesCount = 0;
    var foundChildren = undefined;

    if (which == 'all' || which == 'wp') {
        //Find wordpress to update
        foundChildren = jQuery( '#wp_upgrades' ).find( 'div[updated="0"]' );
        if ( foundChildren.length != 0 )
        {
            for ( var i = 0; i < foundChildren.length; i++ )
            {
                var child = jQuery( foundChildren[i] );
                var siteId = child.attr( 'site_id' );
                var siteName = child.attr( 'site_name' );
                if ( sitesToUpdate.indexOf( siteId ) == -1 )
                {
                    sitesCount++;
                    sitesToUpdate.push( siteId );
                    siteNames[siteId] = siteName;
                }
                if ( sitesToUpgrade.indexOf( siteId ) == -1 )
                    sitesToUpgrade.push( siteId );
            }
        }
    }

    if (which == 'all' || which == 'plugin') {
        //Find plugins to update
        foundChildren = jQuery( '#wp_plugin_upgrades' ).find( 'div[updated="0"]' );
        if ( foundChildren.length != 0 )
        {
        for ( var i = 0; i < foundChildren.length; i++ )
        {
            var child = jQuery( foundChildren[i] );
            siteElement = child;

            var siteId = siteElement.attr( 'site_id' );
            var siteName = siteElement.attr( 'site_name' );
            var pluginSlug = siteElement.attr( 'plugin_slug' );

            if ( sitesToUpdate.indexOf( siteId ) == -1 )
            {
                sitesCount++;
                sitesToUpdate.push( siteId );
                siteNames[siteId] = siteName;
            }

            if ( sitesPluginSlugs[siteId] == undefined )
            {
                sitesPluginSlugs[siteId] = pluginSlug;
            } else
            {
                sitesPluginSlugs[siteId] += ',' + pluginSlug;
            }
        }
    }
    }

    if (which == 'all' || which == 'theme') {
        //Find themes to update
        foundChildren = jQuery( '#wp_theme_upgrades' ).find( 'div[updated="0"]' );
        if ( foundChildren.length != 0 )
        {
        for ( var i = 0; i < foundChildren.length; i++ )
        {
            var child = jQuery( foundChildren[i] );
            var siteElement = child;

            var siteId = siteElement.attr( 'site_id' );
            var siteName = siteElement.attr( 'site_name' );
            var themeSlug = siteElement.attr( 'theme_slug' );

            if ( sitesToUpdate.indexOf( siteId ) == -1 )
            {
                sitesCount++;
                sitesToUpdate.push( siteId );
                siteNames[siteId] = siteName;
            }

            if ( sitesThemeSlugs[siteId] == undefined )
            {
                sitesThemeSlugs[siteId] = themeSlug;
            } else
            {
                sitesThemeSlugs[siteId] += ',' + themeSlug;
            }
        }
    }
    }

    if (which == 'all' || which == 'translation') {
        //Find translation to update
        foundChildren = jQuery( '#wp_translation_upgrades' ).find( 'div[updated="0"]' );
        if ( foundChildren.length != 0 )
        {
        for ( var i = 0; i < foundChildren.length; i++ )
        {
            var child = jQuery( foundChildren[i] );
            var siteElement = child;

            var siteId = siteElement.attr( 'site_id' );
            var siteName = siteElement.attr( 'site_name' );
            var transSlug = siteElement.attr( 'translation_slug' );

            if ( sitesToUpdate.indexOf( siteId ) == -1 )
            {
                sitesCount++;
                sitesToUpdate.push( siteId );
                siteNames[siteId] = siteName;
            }

            if ( sitesTranslationSlugs[siteId] == undefined )
            {
                sitesTranslationSlugs[siteId] = transSlug;
            } else
            {
                sitesTranslationSlugs[siteId] += ',' + transSlug;
            }
        }
    }
    }

    var _callback = function() {

            //Build form
            for ( var j = 0; j < sitesToUpdate.length; j++ )
            {
                var siteId = sitesToUpdate[j];

                var whatToUpgrade = '';

                if ( sitesToUpgrade.indexOf( siteId ) != -1 )
                    whatToUpgrade = '<span class="wordpress">WordPress core files</span>';

                if ( sitesPluginSlugs[siteId] != undefined )
                {
                    var updateCount = sitesPluginSlugs[siteId].match( /\,/g );
                    if ( updateCount == null )
                        updateCount = 1;
                    else
                        updateCount = updateCount.length + 1;

                    if ( whatToUpgrade != '' )
                        whatToUpgrade += ', ';

                    whatToUpgrade += '<span class="plugin">' + updateCount + ' plugin' + ( updateCount > 1 ? 's' : '' ) + '</span>';
                }

                if ( sitesThemeSlugs[siteId] != undefined )
                {
                    var updateCount = sitesThemeSlugs[siteId].match( /\,/g );
                    if ( updateCount == null )
                        updateCount = 1;
                    else
                        updateCount = updateCount.length + 1;

                    if ( whatToUpgrade != '' )
                        whatToUpgrade += ', ';

                    whatToUpgrade += '<span class="theme">' + updateCount + ' theme' + ( updateCount > 1 ? 's' : '' ) + '</span>';
                }


                if ( sitesTranslationSlugs[siteId] != undefined )
                {
                    var updateCount = sitesTranslationSlugs[siteId].match( /\,/g );
                    if ( updateCount == null )
                        updateCount = 1;
                    else
                        updateCount = updateCount.length + 1;

                    if ( whatToUpgrade != '' )
                        whatToUpgrade += ', ';

                    whatToUpgrade += '<span class="translation">' + updateCount + ' translation' + ( updateCount > 1 ? 's' : '' ) + '</span>';
                }
                mainwpPopup( '#mainwp-sync-sites-modal' ).appendItemsList( decodeURIComponent( siteNames[siteId] ) + ' (' + whatToUpgrade + ')', '<span class="updatesoverview-upgrade-status-wp" siteid="' + siteId + '">' + '<i class="clock outline icon"></i> ' + '</span>' );
            }

            updatesoverviewContinueAfterBackup = function ( pSitesCount, pSitesToUpdate, pSitesToUpgrade, pSitesPluginSlugs, pSitesThemeSlugs, psitesTranslationSlugs ) {
                return function ()
                {
                    //Step 2: show form

                    var initData = {
                        title: __( 'Updating All' ),
                        total: pSitesCount,
                        pMax: pSitesCount
                    };
                    updatesoverview_update_popup_init( initData );
                    
                    var dateObj = new Date();
                    dashboardActionName = 'upgrade_everything';
                    starttimeDashboardAction = dateObj.getTime();
                    countRealItemsUpdated = 0;

                    //Step 3: start updates
                    updatesoverview_upgrade_all_int( pSitesToUpdate, pSitesToUpgrade, pSitesPluginSlugs, pSitesThemeSlugs, psitesTranslationSlugs );

                    updatesoverviewContinueAfterBackup = undefined;
                };
            }( sitesCount, sitesToUpdate, sitesToUpgrade, sitesPluginSlugs, sitesThemeSlugs, sitesTranslationSlugs );

            return mainwp_updatesoverview_checkBackups( sitesToUpdate, siteNames );
    } // end _callback()

    // new confirm message
    if ( jQuery( siteNames ).length > 0 ) {
        var sitesList = [ ];
        jQuery.each( siteNames, function ( index, value ) {
            if ( value ) { // to fix
                sitesList.push( decodeURIComponent( value ) );
            }
        } );

        var whichUpdates = __( 'WordPress core files, plugins, themes and translations' );
        if (which == 'wp') {
            whichUpdates = __( 'WordPress core files' );
        } else if (which == 'plugin') {
            whichUpdates = __( 'plugins' );
        } else if (which == 'theme') {
            whichUpdates = __( 'themes' );
        } else if (which == 'translation') {
            whichUpdates = __( 'translations' );
        }

        var confirmMsg = __( 'You are about to update %1 on the following site(s): <br/><div class="ui message">%2</div> <strong>Do you want to proceed?</strong>', whichUpdates , sitesList.join( '<br />' ) );

        mainwp_confirm(confirmMsg, _callback, false, 2 );
        return false;
    } else {
        return false;
    }

};

updatesoverview_upgrade_all_int = function ( pSitesToUpdate, pSitesToUpgrade, pSitesPluginSlugs, pSitesThemeSlugs, psitesTranslationSlugs )
{
    websitesToUpdate = pSitesToUpdate;

    websitesToUpgrade = pSitesToUpgrade;

    websitesPluginSlugsToUpgrade = pSitesPluginSlugs;
    currentPluginSlugToUpgrade = undefined;

    websitesThemeSlugsToUpgrade = pSitesThemeSlugs;
    currentThemeSlugToUpgrade = undefined;

    websitesTransSlugsToUpgrade = psitesTranslationSlugs;
    currentTransSlugToUpgrade = undefined;

    currentWebsite = 0;
    websitesDone = 0;
    websitesTotal = websitesLeft = websitesToUpdate.length;

    bulkTaskRunning = true;
    updatesoverview_upgrade_all_loop_next();
};

updatesoverview_upgrade_all_loop_next = function ()
{
    while ( bulkTaskRunning && ( currentThreads < maxThreads ) && ( websitesLeft > 0 ) )
    {
        updatesoverview_upgrade_all_upgrade_next();
    }
};
updatesoverview_upgrade_all_update_site_status = function ( siteId, newStatus )
{
    jQuery( '.updatesoverview-upgrade-status-wp[siteid="' + siteId + '"]' ).html( newStatus );
};
updatesoverview_upgrade_all_update_site_bold = function ( siteId, sub )
{
    jQuery( '.updatesoverview-upgrade-status-wp[siteid="' + siteId + '"]' ).parent().parent().find( '.' + sub ).css( 'font-weight', 'bold' );
};
updatesoverview_upgrade_all_upgrade_next = function ()
{
    currentThreads++;
    websitesLeft--;

    var websiteId = websitesToUpdate[currentWebsite++];
    updatesoverview_upgrade_all_update_site_status( websiteId, '<i class="sync loading icon"></i>' );

    var themeSlugToUpgrade = websitesThemeSlugsToUpgrade[websiteId];
    var pluginSlugToUpgrade = websitesPluginSlugsToUpgrade[websiteId];
    var transSlugToUpgrade = websitesTransSlugsToUpgrade[websiteId];
    var wordpressUpgrade = ( websitesToUpgrade.indexOf( websiteId ) != -1 );

    updatesoverview_upgrade_int( websiteId, themeSlugToUpgrade, pluginSlugToUpgrade, wordpressUpgrade, transSlugToUpgrade );
};

updatesoverview_upgrade_int = function ( websiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pTransSlugToUpgrade )
{
   updatesoverview_upgrade_int_flow( websiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, ( pThemeSlugToUpgrade == undefined ), ( pPluginSlugToUpgrade == undefined ), !pWordpressUpgrade, undefined, pTransSlugToUpgrade, ( pTransSlugToUpgrade == undefined ) );
    return false;
};
updatesoverview_upgrade_all_update_done = function ()
{
    currentThreads--;
    if ( !bulkTaskRunning )
        return;
    websitesDone++;

    mainwpPopup( '#mainwp-sync-sites-modal' ).setProgressValue( websitesDone );

    if ( websitesDone == websitesTotal )
    {
        setTimeout( function ()
        {
            bulkTaskRunning = false;
            // close and refresh page
            mainwpPopup( '#mainwp-sync-sites-modal' ).close(true);

        }, 3000 );
        return;
    }

    updatesoverview_upgrade_all_loop_next();
};

updatesoverview_upgrade_int_flow = function ( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pPluginDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone )
{
    if ( !pThemeDone )
    {
        var data = mainwp_secure_data( {
            action: 'mainwp_upgradeplugintheme',
            websiteId: pWebsiteId,
            type: 'theme',
            slug: pThemeSlugToUpgrade
        } );

        jQuery.ajax( {
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function ( pWebsiteId, pSlug, pPluginSlugToUpgrade, pWordpressUpgrade, pPluginDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone )
            {
                return function ( response )
                {
                    var slugParts = pSlug.split( ',' );
                    for ( var i = 0; i < slugParts.length; i++ )
                    {
                        var result;

                        var websiteHolder = jQuery( 'div[theme_slug="' + slugParts[i] + '"] div[site_id="' + pWebsiteId + '"]' );
                        if ( !websiteHolder.exists() )
                        {
                            websiteHolder = jQuery( 'div[site_id="' + pWebsiteId + '"] div[theme_slug="' + slugParts[i] + '"]' );
                        }
                        if ( response.error )
                        {
                            result = getErrorMessage( response.error );
                            pErrorMessage = result;
                        } else
                        {
                            var res = response.result;

                            if ( res[slugParts[i]] )
                            {
                                websiteHolder.attr( 'updated', 1 );
                                countRealItemsUpdated++;
                                if (itemsToUpdate.indexOf(slugParts[i]) == -1) itemsToUpdate.push(slugParts[i]);
                            } else
                            {
                                result = __( 'Update failed!' );
                                pErrorMessage = result;
                            }

                        }
                    }
                    updatesoverview_upgrade_all_update_site_bold( pWebsiteId, 'theme' );

                    //If all done: continue, else delay 400ms to not stress the server
                    var fnc = function () {
                        updatesoverview_upgrade_int_flow( pWebsiteId, pSlug, pPluginSlugToUpgrade, pWordpressUpgrade, true, pPluginDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone );
                    };

                    if ( pPluginDone && pUpgradeDone && pTransDone )
                        fnc();
                    else
                        setTimeout( fnc, 400 );
                }
            }( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pPluginDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone ),
            tryCount: 0,
            retryLimit: 3,
            endError: function ( WebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pPluginDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone )
            {
                return function ()
                {
                    updatesoverview_upgrade_int_flow( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, true, true, true, 'Error processing request', pTransSlugToUpgrade, true );
                }
            }( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pPluginDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone ),
            error: function ( xhr ) {
                this.tryCount++;
                if ( this.tryCount >= this.retryLimit ) {
                    this.endError();
                    return;
                }

                var fnc = function ( pRqst, pXhr ) {
                    return function () {
                        if ( pXhr.status == 404 ) {
                            //handle error
                            jQuery.ajax( pRqst );
                        } else if ( pXhr.status == 500 ) {
                            //handle error
                        } else {
                            //handle error
                        }
                    }
                }( this, xhr );
                setTimeout( fnc, 1000 );
            },
            dataType: 'json'
        } );
    } else if ( !pPluginDone )
    {
        var data = mainwp_secure_data( {
            action: 'mainwp_upgradeplugintheme',
            websiteId: pWebsiteId,
            type: 'plugin',
            slug: pPluginSlugToUpgrade
        } );

        jQuery.ajax( {
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function ( pWebsiteId, pThemeSlugToUpgrade, pSlug, pWordpressUpgrade, pThemeDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone )
            {
                return function ( response )
                {                       
                    var slugParts = pSlug.split( ',' );
                    for ( var i = 0; i < slugParts.length; i++ )
                    {
                        var result;
                        var websiteHolder = jQuery( 'div[theme_slug="' + slugParts[i] + '"] div[site_id="' + pWebsiteId + '"]' );
                        if ( !websiteHolder.exists() )
                        {
                            websiteHolder = jQuery( 'div[site_id="' + pWebsiteId + '"] div[theme_slug="' + slugParts[i] + '"]' );
                        }
                        if ( response.error )
                        {
                            result = getErrorMessage( response.error );
                            pErrorMessage = result;
                        } 
                        else
                        {
                            var res = response.result;   // result is an object                                                     
                            if ( res[ encodeURIComponent( slugParts[i] ) ] )
                            {                                
                                websiteHolder.attr( 'updated', 1 );
                                countRealItemsUpdated++;
                                if (itemsToUpdate.indexOf(slugParts[i]) == -1) itemsToUpdate.push(slugParts[i]);
                            } else
                            {                             
                                result = __( 'Update failed!' );
                                pErrorMessage = result;                                
                            }

                        }
                    }
                    updatesoverview_upgrade_all_update_site_bold( pWebsiteId, 'plugin' );

                    //If all done: continue, else delay 400ms to not stress the server
                    var fnc = function () {
                        updatesoverview_upgrade_int_flow( pWebsiteId, pThemeSlugToUpgrade, pSlug, pWordpressUpgrade, pThemeDone, true, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone );
                    };

                    if ( pThemeDone && pUpgradeDone && pTransDone )
                        fnc();
                    else
                        setTimeout( fnc, 400 );
                }
            }( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone ),
            tryCount: 0,
            retryLimit: 3,
            endError: function ( WebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pPluginDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone )
            {
                return function ()
                {
                    updatesoverview_upgrade_int_flow( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, true, true, true, 'Error processing request', pTransSlugToUpgrade, true );
                }
            }( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pPluginDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone ),
            error: function ( xhr ) {
                this.tryCount++;
                if ( this.tryCount >= this.retryLimit ) {
                    this.endError();
                    return;
                }

                var fnc = function ( pRqst, pXhr ) {
                    return function () {
                        if ( pXhr.status == 404 ) {
                            //handle error
                            jQuery.ajax( pRqst );
                        } else if ( pXhr.status == 500 ) {
                            //handle error
                        } else {
                            //handle error
                        }
                    }
                }( this, xhr );
                setTimeout( fnc, 1000 );
            },
            dataType: 'json'
        } );
    } else if ( !pUpgradeDone )
    {
        var data = mainwp_secure_data( {
            action: 'mainwp_upgradewp',
            id: pWebsiteId
        } );

        jQuery.ajax( {
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function ( WebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pPluginDone, pErrorMessage, pTransSlugToUpgrade, pTransDone )
            {
                return function ( response )
                {
                    var result;
                    var websiteHolder = jQuery( 'div[site_id="' + pWebsiteId + '"]' );

                    if ( response.error )
                    {
                        result = getErrorMessage( response.error );
                        pErrorMessage = result;
                    } else
                    {
                        result = response.result;
                        websiteHolder.attr( 'updated', 1 );
                        countRealItemsUpdated++;
//                        if (itemsToUpdate.indexOf('upgradewp_site_' + pWebsiteId) == -1) itemsToUpdate.push('upgradewp_site_' + pWebsiteId);
                    }
                    
                    updatesoverview_upgrade_all_update_site_bold( pWebsiteId, 'wordpress' );

                    //If all done: continue, else delay 400ms to not stress the server
                    var fnc = function () {
                        updatesoverview_upgrade_int_flow( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pPluginDone, true, pErrorMessage, pTransSlugToUpgrade, pTransDone );
                    };

                    if ( pThemeDone && pPluginDone && pTransDone )
                        fnc();
                    else
                        setTimeout( fnc, 400 );
                }
            }( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pPluginDone, pErrorMessage, pTransSlugToUpgrade, pTransDone ),
            tryCount: 0,
            retryLimit: 3,
            endError: function ( WebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pPluginDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone )
            {
                return function ()
                {
                    updatesoverview_upgrade_int_flow( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, true, true, true, 'Error processing request', pTransSlugToUpgrade, true );
                }
            }( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pPluginDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone ),
            error: function ( xhr ) {
                this.tryCount++;
                if ( this.tryCount >= this.retryLimit ) {
                    this.endError();
                    return;
                }

                var fnc = function ( pRqst, pXhr ) {
                    return function () {
                        if ( pXhr.status == 404 ) {
                            //handle error
                            jQuery.ajax( pRqst );
                        } else if ( pXhr.status == 500 ) {
                            //handle error
                        } else {
                            //handle error
                        }
                    }
                }( this, xhr );
                setTimeout( fnc, 1000 );
            },
            dataType: 'json'
        } );
    } else if ( !pTransDone )
    {
        var data = mainwp_secure_data( {
            action: 'mainwp_upgradeplugintheme',
            websiteId: pWebsiteId,
            type: 'translation',
            slug: pTransSlugToUpgrade
        } );

        jQuery.ajax( {
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function ( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pUpgradeDone, pErrorMessage, pSlug, pTransDone )
            {
                return function ( response )
                {
                    var slugParts = pSlug.split( ',' );
                    for ( var i = 0; i < slugParts.length; i++ )
                    {
                        var result;
                        var websiteHolder = jQuery( 'div[translation_slug="' + slugParts[i] + '"] div[site_id="' + pWebsiteId + '"]' );
                        if ( !websiteHolder.exists() )
                        {
                            websiteHolder = jQuery( 'div[site_id="' + pWebsiteId + '"] div[translation_slug="' + slugParts[i] + '"]' );
                        }
                        if ( response.error )
                        {
                            result = getErrorMessage( response.error );
                            pErrorMessage = result;
                        } else
                        {
                            var res = response.result;

                            if ( res[slugParts[i]] )
                            {
                                websiteHolder.attr( 'updated', 1 );
                                countRealItemsUpdated++;
                                if (itemsToUpdate.indexOf(slugParts[i]) == -1) itemsToUpdate.push(slugParts[i]);
                            } else
                            {
                                result = __( 'Update failed!' );
                                pErrorMessage = result;
                            }

                        }
                    }
                    updatesoverview_upgrade_all_update_site_bold( pWebsiteId, 'translation' );

                    //If all done: continue, else delay 400ms to not stress the server
                    var fnc = function () {
                        updatesoverview_upgrade_int_flow( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pPluginDone, pUpgradeDone, pErrorMessage, pSlug, true );
                    };

                    if ( pThemeDone && pUpgradeDone && pPluginDone )
                        fnc();
                    else
                        setTimeout( fnc, 400 );
                }
            }( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone ),
            tryCount: 0,
            retryLimit: 3,
            endError: function ( WebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pPluginDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone )
            {
                return function ()
                {
                    updatesoverview_upgrade_int_flow( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, true, true, true, 'Error processing request', pTransSlugToUpgrade, true );
                }
            }( pWebsiteId, pThemeSlugToUpgrade, pPluginSlugToUpgrade, pWordpressUpgrade, pThemeDone, pPluginDone, pUpgradeDone, pErrorMessage, pTransSlugToUpgrade, pTransDone ),
            error: function ( xhr ) {
                this.tryCount++;
                if ( this.tryCount >= this.retryLimit ) {
                    this.endError();
                    return;
                }

                var fnc = function ( pRqst, pXhr ) {
                    return function () {
                        if ( pXhr.status == 404 ) {
                            //handle error
                            jQuery.ajax( pRqst );
                        } else if ( pXhr.status == 500 ) {
                            //handle error
                        } else {
                            //handle error
                        }
                    }
                }( this, xhr );
                setTimeout( fnc, 1000 );
            },
            dataType: 'json'
        } );
    } else
    {
        if ( ( pErrorMessage != undefined ) && ( pErrorMessage != '' ) )
        {
            updatesoverview_upgrade_all_update_site_status( pWebsiteId, '<i class="red times icon"></i>' );
        } else
        {
            updatesoverview_upgrade_all_update_site_status( pWebsiteId, '<i class="green check icon"></i>' );
        }
        updatesoverview_upgrade_all_update_done();
        
        if (websitesDone == websitesTotal)
        {
            couttItemsToUpdate = itemsToUpdate.length;
            updatesoverview_send_twitt_info();
        }
        return false;
    }
};

var updatesoverviewShowBusyFunction;
var updatesoverviewShowBusyTimeout;
var updatesoverviewShowBusy;
mainwp_updatesoverview_checkBackups = function ( sitesToUpdate, siteNames ) {
    if ( mainwpParams['disable_checkBackupBeforeUpgrade'] == true ) {
        if ( updatesoverviewContinueAfterBackup != undefined )
            updatesoverviewContinueAfterBackup();
        return false;
    }

    updatesoverviewShowBusyFunction = function ()
    {

        var output = __( 'Checking if a backup is required for the selected updates...' );
        mainwpPopup( '#updatesoverview-backup-box' ).getContentEl().html( output );
        jQuery( '#updatesoverview-backup-all' ).hide();
        jQuery( '#updatesoverview-backup-ignore' ).hide();

        mainwpPopup( '#updatesoverview-backup-box' ).init( { title: __( "Checking backup settings..." ), callback: function () {
                window.location.href = location.href
            } } );
    };

    updatesoverviewShowBusyTimeout = setTimeout( updatesoverviewShowBusyFunction, 300 );

    //Step 2: Check if backups are ok.
    var data = mainwp_secure_data( {
        action: 'mainwp_checkbackups',
        sites: sitesToUpdate
    } );

    jQuery.ajax( {
        type: "POST",
        url: ajaxurl,
        data: data,
        success: function ( pSiteNames ) {
            return function ( response )
            {                
                clearTimeout( updatesoverviewShowBusyTimeout );

                mainwpPopup( '#updatesoverview-backup-box' ).close();

                var siteFeedback = undefined;

                if ( response['result'] && response['result']['sites'] != undefined )
                {
                    siteFeedback = [ ];
                    for ( var currSiteId in response['result']['sites'] )
                    {
                        if ( response['result']['sites'][currSiteId] == false )
                        {
                            siteFeedback.push( currSiteId );
                        }
                    }
                    if ( siteFeedback.length == 0 )
                        siteFeedback = undefined;
                }

                if ( siteFeedback != undefined )
                {
                    var backupPrimary = '';
                    if ( response['result']['primary_backup'] && response['result']['primary_backup'] != undefined )
                        backupPrimary = response['result']['primary_backup'];

                    if ( backupPrimary == '' ) {
                        jQuery( '#updatesoverview-backup-all' ).show();
                        jQuery( '#updatesoverview-backup-ignore' ).show();
                    } else {
                        var backupLink = mainwp_get_primaryBackup_link( backupPrimary );
                        jQuery( '#updatesoverview-backup-now' ).attr( 'href', backupLink ).show();
                        jQuery( '#updatesoverview-backup-ignore' ).val( __( 'Proceed with Updates' ) ).show();
                    }

                    var output = '<span class="mainwp-red">' + __( 'A full backup has not been taken in the last days for the following sites:' ) + '</span><br /><br />';

                    if ( backupPrimary == '' ) { // default backup feature
                        for ( var j = 0; j < siteFeedback.length; j++ )
                        {
                            output += '<span class="updatesoverview-backup-site" siteid="' + siteFeedback[j] + '">' + decodeURIComponent( pSiteNames[siteFeedback[j]] ) + '</span><br />';
                        }
                    } else {
                        for ( var j = 0; j < siteFeedback.length; j++ )
                        {
                            output += '<span>' + decodeURIComponent( pSiteNames[siteFeedback[j]] ) + '</span><br />';
                        }
                    }

                    mainwpPopup( '#updatesoverview-backup-box' ).getContentEl().html( output );

                    mainwpPopup( '#updatesoverview-backup-box' ).init( { title: __( "Full backup required!" ), callback: function () {
                            updatesoverviewContinueAfterBackup = undefined;
                            window.location.href = location.href
                        } } );
                    return false;
                }

                if ( updatesoverviewContinueAfterBackup != undefined )
                    updatesoverviewContinueAfterBackup();
            }
        }( siteNames ),
        error: function ()
        {

            mainwpPopup( '#updatesoverview-backup-box' ).close(true);
        },
        dataType: 'json'
    } );

    return false;
};


jQuery( document ).on( 'click', '#updatesoverview-backupnow-close', function () {
    if ( jQuery( this ).prop( 'cancel' ) == '1' )
    {
        updatesoverviewBackupSites = [ ];
        updatesoverviewBackupError = false;
        updatesoverviewBackupDownloadRunning = false;
        mainwpPopup( '#updatesoverview-backup-box' ).close(true);
    } else
    {
        mainwpPopup( '#updatesoverview-backup-box' ).close();
        if ( updatesoverviewContinueAfterBackup != undefined )
            updatesoverviewContinueAfterBackup();
    }
} );
jQuery( document ).on( 'click', '#updatesoverview-backup-all', function () {

    // change action buttons
    mainwpPopup( '#updatesoverview-backup-box' ).setActionButtons( '<input id="updatesoverview-backupnow-close" type="button" name="Ignore" value="' + __( 'Cancel' ) + '" class="button"/>' );
    mainwpPopup( '#updatesoverview-backup-box' ).init( { title: __( "Full backup" ), callback: function () {
            updatesoverviewContinueAfterBackup = undefined;
            window.location.href = location.href
        } } );

    var sitesToBackup = jQuery( '.updatesoverview-backup-site' );
    updatesoverviewBackupSites = [ ];
    for ( var i = 0; i < sitesToBackup.length; i++ )
    {
        var currentSite = [ ];
        currentSite['id'] = jQuery( sitesToBackup[i] ).attr( 'siteid' );
        currentSite['name'] = jQuery( sitesToBackup[i] ).text();
        updatesoverviewBackupSites.push( currentSite );
    }
    updatesoverview_backup_run();
} );

var updatesoverviewBackupSites;
var updatesoverviewBackupError;
var updatesoverviewBackupDownloadRunning;

updatesoverview_backup_run = function ()
{
    mainwpPopup( '#updatesoverview-backup-box' ).getContentEl().html( dateToHMS( new Date() ) + ' ' + __( 'Starting required backup(s)...' ) );
    jQuery( '#updatesoverview-backupnow-close' ).prop( 'value', __( 'Cancel' ) );
    jQuery( '#updatesoverview-backupnow-close' ).prop( 'cancel', '1' );
    updatesoverview_backup_run_next();
};

updatesoverview_backup_run_next = function ()
{
    var backupContentEl = mainwpPopup( '#updatesoverview-backup-box' ).getContentEl();
    if ( updatesoverviewBackupSites.length == 0 )
    {
        appendToDiv( backupContentEl, __( 'Required backup(s) completed' ) + ( updatesoverviewBackupError ? ' <span class="mainwp-red">' + __( 'with errors' ) + '</span>' : '' ) + '.' );

        jQuery( '#updatesoverview-backupnow-close' ).prop( 'cancel', '0' );
        if ( updatesoverviewBackupError )
        {
            jQuery( '#updatesoverview-backupnow-close' ).prop( 'value', __( 'Continue update anyway' ) );
        } else
        {
            jQuery( '#updatesoverview-backupnow-close' ).prop( 'value', __( 'Continue update' ) );
        }
        return;
    }

    var siteName = updatesoverviewBackupSites[0]['name'];
    appendToDiv( backupContentEl, '[' + siteName + '] ' + __( 'Creating backup file...' ) );

    var siteId = updatesoverviewBackupSites[0]['id'];
    updatesoverviewBackupSites.shift();
    var data = mainwp_secure_data( {
        action: 'mainwp_backup_run_site',
        site_id: siteId
    } );

    jQuery.post( ajaxurl, data, function ( pSiteId, pSiteName ) {
        return function ( response ) {
            if ( response.error )
            {
                appendToDiv( backupContentEl, '[' + pSiteName + '] <span class="mainwp-red">ERROR: ' + getErrorMessage( response.error ) + '</span>' );
                updatesoverviewBackupError = true;
                updatesoverview_backup_run_next();
            } else
            {
                appendToDiv( backupContentEl, '[' + pSiteName + '] ' + __( 'Backup file created successfully!' ) );

                updatesoverview_backupnow_download_file( pSiteId, pSiteName, response.result.type, response.result.url, response.result.local, response.result.regexfile, response.result.size, response.result.subfolder );
            }

        }
    }( siteId, siteName ), 'json' );
};
updatesoverview_backupnow_download_file = function ( pSiteId, pSiteName, type, url, file, regexfile, size, subfolder )
{
    var backupContentEl = mainwpPopup( '#updatesoverview-backup-box' ).getContentEl();
    appendToDiv( backupContentEl, '[' + pSiteName + '] Downloading the file... <div id="updatesoverview-backupnow-status-progress" siteId="' + pSiteId + '" class="ui green progress"><div class="bar"><div class="progress"></div></div>' );
    jQuery( '#updatesoverview-backupnow-status-progress[siteId="' + pSiteId + '"]' ).progress( { value: 0, total: size } );
    var interVal = setInterval( function () {
        var data = mainwp_secure_data( {
            action: 'mainwp_backup_getfilesize',
            local: file
        } );
        jQuery.post( ajaxurl, data, function ( pSiteId ) {
            return function ( response ) {
                if ( response.error )
                    return;

                if ( updatesoverviewBackupDownloadRunning )
                {
                    var progressBar = jQuery( '#updatesoverview-backupnow-status-progress[siteId="' + pSiteId + '"]' );
                    if ( progressBar.progress( 'get value' ) < progressBar.progress( 'get total' ) )
                    {
                        progressBar.progress( 'set progress', response.result );
                    }
                }
            }
        }( pSiteId ), 'json' );
    }, 500 );

    var data = mainwp_secure_data( {
        action: 'mainwp_backup_download_file',
        site_id: pSiteId,
        type: type,
        url: url,
        local: file
    } );
    updatesoverviewBackupDownloadRunning = true;
    jQuery.post( ajaxurl, data, function ( pFile, pRegexFile, pSubfolder, pSize, pType, pInterVal, pSiteName, pSiteId, pUrl ) {
        return function ( response ) {
            updatesoverviewBackupDownloadRunning = false;
            clearInterval( pInterVal );

            if ( response.error )
            {
                appendToDiv( backupContentEl, '[' + pSiteName + '] <span class="mainwp-red">ERROR: ' + getErrorMessage( response.error ) + '</span>' );
                appendToDiv( backupContentEl, '[' + pSiteName + '] <span class="mainwp-red">' + __( 'Backup failed!' ) + '</span>' );

                updatesoverviewBackupError = true;
                updatesoverview_backup_run_next();
                return;
            }

            jQuery( '#updatesoverview-backupnow-status-progress[siteId="' + pSiteId + '"]' ).progress( 'set progress', pSize );
            appendToDiv( backupContentEl, '[' + pSiteName + '] ' + __( 'Download from the child site completed.' ) );
            appendToDiv( backupContentEl, '[' + pSiteName + '] ' + __( 'Backup completed.' ) );

            var newData = mainwp_secure_data( {
                action: 'mainwp_backup_delete_file',
                site_id: pSiteId,
                file: pUrl
            } );
            jQuery.post( ajaxurl, newData, function () {}, 'json' );

            updatesoverview_backup_run_next();
        }
    }( file, regexfile, subfolder, size, type, interVal, pSiteName, pSiteId, url ), 'json' );
};

updatesoverview_plugins_dismiss_outdate_detail = function ( slug, name, id, obj ) {
    return updatesoverview_dismiss_outdate_plugintheme_by_site( 'plugin', slug, name, id, obj );
};
updatesoverview_themes_dismiss_outdate_detail = function ( slug, name, id, obj) {
    return updatesoverview_dismiss_outdate_plugintheme_by_site( 'theme', slug, name, id, obj);
};

updatesoverview_plugins_unignore_abandoned_detail = function ( slug, id ) {
    return updatesoverview_unignore_plugintheme_abandoned_by_site( 'plugin', slug, id );
};
updatesoverview_plugins_unignore_abandoned_detail_all = function () {
    return updatesoverview_unignore_plugintheme_abandoned_by_site_all( 'plugin' );
};
updatesoverview_themes_unignore_abandoned_detail = function ( slug, id ) {
    return updatesoverview_unignore_plugintheme_abandoned_by_site( 'theme', slug, id );
};
updatesoverview_themes_unignore_abandoned_detail_all = function () {
    return updatesoverview_unignore_plugintheme_abandoned_by_site_all( 'theme' );
};

updatesoverview_dismiss_outdate_plugintheme_by_site = function ( what, slug, name, id, pObj ) {
    var data = mainwp_secure_data( {
        action: 'mainwp_dismissoutdateplugintheme',
        type: what,
        id: id,
        slug: slug,
        name: name
    } );
    var parent = jQuery(pObj). closest('tr');
    parent.find('td:last-child').html( __( 'Ignoring...' ) );
    jQuery.post( ajaxurl, data, function ( response ) {
        if ( response.result ) {
              parent.attr( 'dismissed', '-1' );
              parent.find( 'td:last-child' ).html(  __( 'Ignored!' ) );

        } else
        {
            parent.find('td:last-child').html( getErrorMessage( response.error ) );
        }
    }, 'json' );
    return false;
};

// Unignore abandoned Plugin/Theme ignored per site basis
updatesoverview_unignore_plugintheme_abandoned_by_site = function ( what, slug, id ) {
    var data = mainwp_secure_data( {
        action: 'mainwp_unignoreabandonedplugintheme',
        type: what,
        id: id,
        slug: slug
    } );

    jQuery.post( ajaxurl, data, function ( pWhat, pSlug, pId ) {
        return function ( response ) {
            if ( response.result ) {
                var siteElement;
        if ( pWhat == 'plugin' ) {
          siteElement = jQuery( 'tr[site-id="' + pId + '"][plugin-slug="' + pSlug + '"]' );
        } else {
          siteElement = jQuery( 'tr[site-id="' + pId + '"][theme-slug="' + pSlug + '"]' );
                }

        if ( !siteElement.find( 'div' ).is( ':visible' ) ) {
                    siteElement.remove();
                    return;
                }

                //Check if previous tr is same site..
                //Check if next tr is same site..
                var siteAfter = siteElement.next();

        if ( siteAfter.exists() && ( siteAfter.attr( 'site-id' ) == pId ) ) {
          siteAfter.find( 'div' ).show();
                    siteElement.remove();
                    return;
                }

                var parent = siteElement.parent();

                siteElement.remove();

                if ( parent.children( 'tr' ).size() == 0 ) {
          jQuery( '#mainwp-unignore-detail-all' ).addClass( 'disabled' );
          parent.append( '<tr><td colspan="999">' + __( 'No ignored abandoned %1s', pWhat ) + '</td></tr>' );
                }
            }
        }
    }( what, slug, id ), 'json' );
    return false;
};

// Unigore all per site ignored abandoned Plugins / Themese
updatesoverview_unignore_plugintheme_abandoned_by_site_all = function ( what ) {
    var data = mainwp_secure_data( {
        action: 'mainwp_unignoreabandonedplugintheme',
        type: what,
        id: '_ALL_',
        slug: '_ALL_'
    } );

    jQuery.post( ajaxurl, data, function ( pWhat ) {
        return function ( response ) {
            if ( response.result ) {
        var tableElement = jQuery( '#ignored-abandoned-' + pWhat + 's-list' );
                tableElement.find( 'tr' ).remove();
        tableElement.append( '<tr><td colspan="999">' + __( 'No ignored abandoned %1s', pWhat ) + '</td></tr>' );
        jQuery( '#mainwp-unignore-detail-all' ).addClass( 'disabled' );
            }
        }
    }( what ), 'json' );
    return false;
};

updatesoverview_plugins_abandoned_ignore_all = function ( slug, name, pObj ) {
    var parent = jQuery(pObj). closest('tr');
    parent.find('td:last-child').html( __( 'Ignoring...' ) );
    var data = mainwp_secure_data( {
        action: 'mainwp_dismissoutdatepluginsthemes',
        type: 'plugin',
        slug: slug,
        name: name
    } );
    jQuery.post( ajaxurl, data, function ( response ) {
        if ( response.result ) {
            parent.find('td:last-child').html( __( 'Ignored!' ) );
            jQuery('.abandoned-plugins-ignore-global[plugin_slug="' + slug + '"]').find('tr td:last-child').html( __( 'Ignored!' ) );
            jQuery('.abandoned-plugins-ignore-global[plugin_slug="' + slug + '"]').find('tr[dismissed=0]').attr('dismissed', 1);
        } else {
            parent.find('td:last-child').html( getErrorMessage( response.error ) );
        }
    }, 'json' );
    return false;
};

// Unignore all globally ignored abandoned plugins
updatesoverview_plugins_abandoned_unignore_globally_all = function () {

    var data = mainwp_secure_data( {
        action: 'mainwp_unignoreabandonedpluginsthemes',
        type: 'plugin',
        slug: '_ALL_'
    } );

    jQuery.post( ajaxurl, data, function ( response ) {
        if ( response.result ) {
      var tableElement = jQuery( '#ignored-abandoned-plugins-list' );
            tableElement.find( 'tr' ).remove();
      jQuery( '#mainwp-unignore-globally-all' ).addClass( 'disabled' );
      tableElement.append( '<tr><td colspan="999">' + __( 'No ignored abandoned plugins.' ) + '</td></tr>' );
        }
    }, 'json' );

    return false;

};

// Unignore globally ignored abandoned plugin
updatesoverview_plugins_abandoned_unignore_globally = function ( slug ) {

    var data = mainwp_secure_data( {
        action: 'mainwp_unignoreabandonedpluginsthemes',
        type: 'plugin',
        slug: slug
    } );

    jQuery.post( ajaxurl, data, function ( response ) {
        if ( response.result ) {
      var ignoreElement = jQuery( '#ignored-abandoned-plugins-list tr[plugin-slug="' + slug + '"]' );
            var parent = ignoreElement.parent();
            ignoreElement.remove();
            if ( parent.children( 'tr' ).size() == 0 ) {
        jQuery( '.mainwp-unignore-globally-all' ).addClass( 'disabled' );
        parent.append( '<tr><td colspan="999">' + __( 'No ignored abandoned plugins.' ) + '</td></tr>' );
            }
        }
    }, 'json' );
    return false;
};
updatesoverview_themes_abandoned_ignore_all = function ( slug, name, pObj ) {
    var parent = jQuery(pObj). closest('tr');
    parent.find('td:last-child').html( __( 'Ignoring...' ) );

    var data = mainwp_secure_data( {
        action: 'mainwp_dismissoutdatepluginsthemes',
        type: 'theme',
        slug: slug,
        name: name
    } );
    jQuery.post( ajaxurl, data, function ( response ) {
        if ( response.result ) {
            parent.find('td:last-child').html( __( 'Ignored!' ) );
            jQuery('.abandoned-themes-ignore-global[theme_slug="' + slug + '"]').find('tr td:last-child').html( __( 'Ignored!' ) );
            jQuery('.abandoned-themes-ignore-global[theme_slug="' + slug + '"]').find('tr[dismissed=0]').attr('dismissed', 1);
        } else {
            parent.find('td:last-child').html( getErrorMessage( response.error ) );
        }
    }, 'json' );
    return false;
};

// Unignore all globablly ignored themes
updatesoverview_themes_abandoned_unignore_globally_all = function () {
    var data = mainwp_secure_data( {
        action: 'mainwp_unignoreabandonedpluginsthemes',
        type: 'theme',
        slug: '_ALL_'
    } );

    jQuery.post( ajaxurl, data, function ( response ) {
        if ( response.result ) {
            var tableElement = jQuery( '#globally-ignored-themes-list' );
            tableElement.find( 'tr' ).remove();
        jQuery( '.mainwp-unignore-globally-all' ).addClass( 'disabled' );
        tableElement.append( '<tr><td colspan="999">' + __( 'No ignored abandoned themes.' ) + '</td></tr>' );
        }
    }, 'json' );

    return false;
};

// Unignore globally ignored theme
updatesoverview_themes_abandoned_unignore_globally = function ( slug ) {
    var data = mainwp_secure_data( {
        action: 'mainwp_unignoreabandonedpluginsthemes',
        type: 'theme',
        slug: slug
    } );

    jQuery.post( ajaxurl, data, function ( response ) {
        if ( response.result ) {
      var ignoreElement = jQuery( '#globally-ignored-themes-list tr[theme-slug="' + slug + '"]' );
            var parent = ignoreElement.parent();
            ignoreElement.remove();
      if ( parent.children( 'tr' ).size() == 0 ) {
        jQuery( '.mainwp-unignore-globally-all' ).addClass( 'disabled' );
        parent.append( '<tr><td colspan="999">' + __( 'No ignored abandoned themes.' ) + '</td></tr>' );
            }
        }
    }, 'json' );

    return false;
};

mainwp_siteview_onchange = function(me) {
    jQuery(me).closest("form").submit();
}

jQuery( document ).ready( function ()
{
//    jQuery( '#mainwp_select_options_siteview' ).change( function () {
//        jQuery( this ).closest( "form" ).submit();
//    } );
    if ( jQuery( '#updatesoverview_limit_updates_all' ).length > 0 && jQuery( '#updatesoverview_limit_updates_all' ).val() > 0 ) {
        limitUpdateAll = jQuery( '#updatesoverview_limit_updates_all' ).val();
        if ( jQuery( '.updatesoverview_continue_update_me' ).length > 0 ) {
            continueUpdating = true;
            jQuery( '.updatesoverview_continue_update_me' )[0].click();
        }
    }
} );

updatesoverview_recheck_http = function ( elem, id ) {
    var data = mainwp_secure_data( {
        action: 'mainwp_recheck_http',
        websiteid: id
    } );
    jQuery( elem ).attr( 'disabled', 'true' );
    jQuery( '#wp_http_response_code_' + id + ' .http-code' ).html( '<i class="ui active inline loader tiny"></i>' );
    jQuery.post( ajaxurl, data, function ( response ) {
        jQuery( elem ).removeAttr( 'disabled' );
        if ( response ) {
            var hc = ( response && response.httpcode ) ? response.httpcode : '';
            jQuery( '#wp_http_response_code_' + id + ' .http-code' ).html( 'HTTP ' + hc );
            if ( response.status ) {
                jQuery( '#wp_http_response_code_' + id ).addClass( 'http-response-ok' );
            } else {
                jQuery( '#wp_http_response_code_' + id ).removeClass( 'http-response-ok' );
            }
        } else {
            jQuery( '#wp_http_response_code_' + id + ' .http-code' ).html( __( 'Undefined error!' ) );
        }
    }, 'json' );
    return false;
};

updatesoverview_ignore_http_response = function ( elem, id ) {
    var data = mainwp_secure_data( {
        action: 'mainwp_ignore_http_response',
        websiteid: id
    } );
    jQuery( elem ).attr( 'disabled', 'true' );
    jQuery( '#wp_http_response_code_' + id + ' .http-code' ).html( '<i class="ui active inline loader tiny"></i>' );
    jQuery.post( ajaxurl, data, function ( response ) {
        jQuery( elem ).removeAttr( 'disabled' );
        if ( response && response.ok ) {
            jQuery( elem ).closest( '.mainwp-sub-row' ).remove();
        }
    }, 'json' );
    return false;
};

// for semantic ui checkboxes
jQuery( document ).ready( function () {    
   mainwp_table_check_columns_init(); // call as function to support tables with ajax, may check and call at extensions    
} );

 mainwp_table_check_columns_init = function() {
    jQuery( document ).find( 'table th.check-column .checkbox' ).checkbox( {
            // check all children
            onChecked: function() {
                var $table = jQuery( this ).closest( 'table' );
                if ($table.parent().hasClass('dataTables_scrollHeadInner')) {
                    $table = jQuery( this ).closest( '.dataTables_scroll' ); // to compatible with datatable scroll            
                }

                if ( $table.length > 0 ) {
                  var $childCheckbox  = $table.find('td.check-column .checkbox');
                  $childCheckbox.checkbox( 'check' );
                }
            },
            // uncheck all children
            onUnchecked: function() {
              var $table = jQuery( this ).closest( 'table' );

              if ($table.parent().hasClass('dataTables_scrollHeadInner'))          
                  $table = jQuery( this ).closest( '.dataTables_scroll' ); // to compatible with datatable scroll


              if ( $table.length > 0 ) {
                var $childCheckbox  = $table.find( 'td.check-column .checkbox' );
                $childCheckbox.checkbox( 'uncheck' );
              }
            }
    } );

    jQuery( document ).find('td.check-column .checkbox').checkbox( {
            // Fire on load to set parent value
            fireOnInit : true,
            // Change parent state on each child checkbox change
            onChange   : function() {

            var $table = jQuery(this).closest('table');   

            if ($table.parent().hasClass('dataTables_scrollBody'))          
              $table = jQuery( this ).closest( '.dataTables_scroll' ); // to compatible with datatable scroll

            var $parentCheckbox = $table.find('th.check-column .checkbox'),
                $checkbox       = $table.find('td.check-column .checkbox'),
                allChecked      = true,
                allUnchecked    = true
              ;

              $checkbox.each(function() {
                if( jQuery( this ).checkbox( 'is checked' ) ) {
                  allUnchecked = false;
                }
                else {
                  allChecked = false;
                }
              } );

              if( allChecked ) {
                $parentCheckbox.checkbox( 'set checked' );
              }
              else if( allUnchecked ) {
                $parentCheckbox.checkbox( 'set unchecked' );
              }
            }
    } );
}