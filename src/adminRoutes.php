<?php

use \Admin;

Route::model('user', 'CmsCanvas\Models\User');
Route::model('role', 'CmsCanvas\Models\Role');
Route::model('permission', 'CmsCanvas\Models\Permission');
Route::model('contentType', 'CmsCanvas\Models\Content\Type');
Route::model('contentTypeField', 'CmsCanvas\Models\Content\Type\Field');
Route::model('entry', 'CmsCanvas\Models\Content\Entry');
Route::model('navigation', 'CmsCanvas\Models\Content\Navigation');

Route::group(['prefix' => Admin::getUrlPrefix(), 'before' => 'cmscanvas.auth|cmscanvas.permission', 'permission' => 'ADMIN'], function()
{

    Route::get('/', ['as' => 'admin.index', 'uses' => 'CmsCanvas\Controllers\Admin\DashboardController@getDashboard']);
    Route::get('dashboard', ['as' => 'admin.dashboard', 'uses' => 'CmsCanvas\Controllers\Admin\DashboardController@getDashboard']);

    Route::group(['prefix' => 'user'], function()
    {

        Route::group(['permission' => 'ADMIN_USER_VIEW'], function()
        {
            Route::get('/', ['as' => 'admin.user.users', 'uses' => 'CmsCanvas\Controllers\Admin\UserController@getUsers']);
            Route::post('/', ['as' => 'admin.user.users.post', 'uses' => 'CmsCanvas\Controllers\Admin\UserController@postUsers']);

            Route::post('delete', ['as' => 'admin.user.delete.post', 'permission' => 'ADMIN_USER_DELETE', 'uses' => 'CmsCanvas\Controllers\Admin\UserController@postDelete']);

            Route::get('add', ['as' => 'admin.user.add', 'permission' => 'ADMIN_USER_CREATE', 'uses' => 'CmsCanvas\Controllers\Admin\UserController@getEdit']);
            Route::post('add', ['as' => 'admin.user.add.post', 'permission' => 'ADMIN_USER_CREATE', 'uses' => 'CmsCanvas\Controllers\Admin\UserController@postEdit']);

            Route::get('{user}/edit', ['', 'as' => 'admin.user.edit', 'permission' => 'ADMIN_USER_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\UserController@getEdit']);
            Route::post('{user}/edit', ['as' => 'admin.user.edit.post', 'permission' => 'ADMIN_USER_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\UserController@postEdit']);
        });

        Route::group(['prefix' => 'permission', 'permission' => 'ADMIN_PERMISSION_VIEW'], function()
        {

            Route::get('/', ['as' => 'admin.user.permission.permissions', 'uses' => 'CmsCanvas\Controllers\Admin\User\PermissionController@getPermissions']);
            Route::post('/', ['as' => 'admin.user.permission.permissions.post', 'uses' => 'CmsCanvas\Controllers\Admin\User\PermissionController@postPermissions']);

            Route::post('delete', ['as' => 'admin.user.permission.delete.post', 'permission' => 'ADMIN_PERMISSION_DELETE', 'uses' => 'CmsCanvas\Controllers\Admin\User\PermissionController@postDelete']);

            Route::get('add', ['as' => 'admin.user.permission.add', 'permission' => 'ADMIN_PERMISSION_CREATE', 'uses' => 'CmsCanvas\Controllers\Admin\User\PermissionController@getEdit']);
            Route::post('add', ['as' => 'admin.user.permission.add.post', 'permission' => 'ADMIN_PERMISSION_CREATE', 'uses' => 'CmsCanvas\Controllers\Admin\User\PermissionController@postEdit']);

            Route::get('{permission}/edit', ['as' => 'admin.user.permission.edit', 'permission' => 'ADMIN_PERMISSION_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\User\PermissionController@getEdit']);
            Route::post('{permission}/edit', ['as' => 'admin.user.permission.edit.post', 'permission' => 'ADMIN_PERMISSION_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\User\PermissionController@postEdit']);

        });

        Route::group(['prefix' => 'role', 'permission' => 'ADMIN_ROLE_VIEW'], function()
        {

            Route::get('/', ['as' => 'admin.user.role.roles', 'uses' => 'CmsCanvas\Controllers\Admin\User\RoleController@getRoles']);
            Route::post('/', ['as' => 'admin.user.role.roles.post', 'uses' => 'CmsCanvas\Controllers\Admin\User\RoleController@postRoles']);

            Route::post('delete', ['as' => 'admin.user.role.delete.post', 'permission' => 'ADMIN_ROLE_DELETE', 'uses' => 'CmsCanvas\Controllers\Admin\User\RoleController@postDelete']);

            Route::get('add', ['as' => 'admin.user.role.add', 'permission' => 'ADMIN_ROLE_CREATE', 'uses' => 'CmsCanvas\Controllers\Admin\User\RoleController@getEdit']);
            Route::post('add', ['as' => 'admin.user.role.add.post', 'permission' => 'ADMIN_ROLE_CREATE', 'uses' => 'CmsCanvas\Controllers\Admin\User\RoleController@postEdit']);

            Route::get('{role}/edit', ['as' => 'admin.user.role.edit', 'permission' => 'ADMIN_ROLE_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\User\RoleController@getEdit']);
            Route::post('{role}/edit', ['as' => 'admin.user.role.edit.post', 'permission' => 'ADMIN_ROLE_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\User\RoleController@postEdit']);

        });

    });

    Route::group(['prefix' => 'content'], function()
    {
        Route::group(['prefix' => 'navigation', 'permission' => 'ADMIN_NAVIGATION_VIEW'], function()
        {

            Route::get('/', ['as' => 'admin.content.navigation.navigations', 'uses' => 'CmsCanvas\Controllers\Admin\Content\NavigationController@getNavigations']);
            Route::post('/', ['as' => 'admin.content.navigation.navigations.post', 'uses' => 'CmsCanvas\Controllers\Admin\Content\NavigationController@postNavigations']);

            Route::get('add', ['as' => 'admin.content.navigation.add', 'permission' => 'ADMIN_NAVIGATION_CREATE', 'uses' => 'CmsCanvas\Controllers\Admin\Content\NavigationController@getEdit']);
            Route::post('add', ['as' => 'admin.content.navigation.add.post', 'permission' => 'ADMIN_NAVIGATION_CREATE', 'uses' => 'CmsCanvas\Controllers\Admin\Content\NavigationController@postEdit']);

            Route::get('{navigation}/edit', ['as' => 'admin.content.navigation.edit', 'permission' => 'ADMIN_NAVIGATION_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\Content\NavigationController@getEdit']);
            Route::post('{navigation}/edit', ['as' => 'admin.content.navigation.edit.post', 'permission' => 'ADMIN_NAVIGATION_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\Content\NavigationController@postEdit']);

            Route::post('delete', ['as' => 'admin.content.navigation.delete.post', 'permission' => 'ADMIN_NAVIGATION_DELETE', 'uses' => 'CmsCanvas\Controllers\Admin\Content\NavigationController@postDelete']);

        });

        Route::group(['prefix' => 'entry', 'permission' => 'ADMIN_ENTRY_VIEW'], function()
        {

            Route::get('/', ['as' => 'admin.content.entry.entries', 'uses' => 'CmsCanvas\Controllers\Admin\Content\EntryController@getEntries']);
            Route::post('/', ['as' => 'admin.content.entry.entries.post', 'uses' => 'CmsCanvas\Controllers\Admin\Content\EntryController@postEntries']);

            Route::post('delete', ['as' => 'admin.content.entry.delete.post', 'permission' => 'ADMIN_ENTRY_DELETE', 'uses' => 'CmsCanvas\Controllers\Admin\Content\EntryController@postDelete']);
            Route::post('create-thumbnail', ['before' => 'cmscanvas.ajax', 'as' => 'admin.content.entry.create.thumbnail.post', 'uses' => 'CmsCanvas\Controllers\Admin\Content\EntryController@postCreateThumbnail']);

        });

        Route::group(['prefix' => 'type'], function()
        {
            Route::group(['permission' => 'ADMIN_ENTRY_VIEW'], function()
            {

                Route::get('{contentType}/entry/add', ['as' => 'admin.content.entry.add', 'permission' => 'ADMIN_ENTRY_CREATE', 'uses' => 'CmsCanvas\Controllers\Admin\Content\EntryController@getEdit']);
                Route::post('{contentType}/entry/add', ['after' =>'cmscanvas.cache.flush', 'as' => 'admin.content.entry.add.post', 'permission' => 'ADMIN_ENTRY_CREATE', 'uses' => 'CmsCanvas\Controllers\Admin\Content\EntryController@postEdit']);

                Route::get('{contentType}/entry/{entry}/edit', ['as' => 'admin.content.entry.edit', 'permission' => 'ADMIN_ENTRY_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\Content\EntryController@getEdit']);
                Route::post('{contentType}/entry/{entry}/edit', ['after' =>'cmscanvas.cache.flush', 'as' => 'admin.content.entry.edit.post', 'permission' => 'ADMIN_ENTRY_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\Content\EntryController@postEdit']);

            });

            Route::group(['permission' => 'ADMIN_CONTENT_TYPE_VIEW'], function()
            {
                Route::get('/', ['as' => 'admin.content.type.types', 'uses' => 'CmsCanvas\Controllers\Admin\Content\TypeController@getTypes']);
                Route::post('/', ['as' => 'admin.content.type.types.post', 'uses' => 'CmsCanvas\Controllers\Admin\Content\TypeController@postTypes']);

                Route::post('delete', ['as' => 'admin.content.type.delete.post', 'permission' => 'ADMIN_CONTENT_TYPE_DELETE', 'uses' => 'CmsCanvas\Controllers\Admin\Content\TypeController@postDelete']);

                Route::get('add', ['as' => 'admin.content.type.add', 'permission' => 'ADMIN_CONTENT_TYPE_CREATE', 'uses' => 'CmsCanvas\Controllers\Admin\Content\TypeController@getEdit']);
                Route::post('add', ['after' =>'cmscanvas.cache.flush', 'as' => 'admin.content.type.add.post', 'permission' => 'ADMIN_CONTENT_TYPE_CREATE', 'uses' => 'CmsCanvas\Controllers\Admin\Content\TypeController@postEdit']);

                Route::get('{contentType}/edit', ['as' => 'admin.content.type.edit', 'permission' => 'ADMIN_CONTENT_TYPE_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\Content\TypeController@getEdit']);
                Route::post('{contentType}/edit', ['after' =>'cmscanvas.cache.flush', 'as' => 'admin.content.type.edit.post', 'permission' => 'ADMIN_CONTENT_TYPE_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\Content\TypeController@postEdit']);
            });

            Route::group(['prefix' => '{contentType}/field', 'permission' => 'ADMIN_CONTENT_TYPE_FIELD_VIEW'], function()
            {

                Route::get('/', ['as' => 'admin.content.type.field.fields', 'uses' => 'CmsCanvas\Controllers\Admin\Content\Type\FieldController@getFields']);
                Route::post('/', ['as' => 'admin.content.type.field.fields.post', 'uses' => 'CmsCanvas\Controllers\Admin\Content\Type\FieldController@postFields']);

                Route::post('delete', ['as' => 'admin.content.type.field.delete.post', 'permission' => 'ADMIN_CONTENT_TYPE_FIELD_DELETE', 'uses' => 'CmsCanvas\Controllers\Admin\Content\Type\FieldController@postDelete']);

                Route::get('add', ['as' => 'admin.content.type.field.add', 'permission' => 'ADMIN_CONTENT_TYPE_FIELD_CREATE', 'uses' => 'CmsCanvas\Controllers\Admin\Content\Type\FieldController@getEdit']);
                Route::post('add', ['as' => 'admin.content.type.field.add.post', 'permission' => 'ADMIN_CONTENT_TYPE_FIELD_CREATE', 'uses' => 'CmsCanvas\Controllers\Admin\Content\Type\FieldController@postEdit']);

                Route::get('{contentTypeField}/edit', ['as' => 'admin.content.type.field.edit', 'permission' => 'ADMIN_CONTENT_TYPE_FIELD_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\Content\Type\FieldController@getEdit']);
                Route::post('{contentTypeField}/edit', ['as' => 'admin.content.type.field.edit.post', 'permission' => 'ADMIN_CONTENT_TYPE_FIELD_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\Content\Type\FieldController@postEdit']);

                Route::post('order', ['as' => 'admin.content.type.field.order.post', 'permission' => 'ADMIN_CONTENT_TYPE_FIELD_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\Content\Type\FieldController@postOrder']);
                Route::post('settings', ['as' => 'admin.content.type.field.settings.post', 'permission' => 'ADMIN_CONTENT_TYPE_FIELD_EDIT', 'uses' => 'CmsCanvas\Controllers\Admin\Content\Type\FieldController@postSettings']);

            });

        });

    });

    Route::group(['prefix' => 'system'], function()
    {
        Route::get('general-settings', ['as' => 'admin.system.general-settings', 'uses' => 'CmsCanvas\Controllers\Admin\SystemController@getGeneralSettings']);
        Route::post('theme-layouts', ['before' => 'cmscanvas.ajax', 'as' => 'admin.system.theme-layouts', 'uses' => 'CmsCanvas\Controllers\Admin\SystemController@postThemeLayouts']);
    });

});

Route::group(['prefix' => Admin::getUrlPrefix()], function()
{

    Route::get('user/login', ['as' => 'admin.user.login', 'uses' => 'CmsCanvas\Controllers\Admin\UserController@getLogin']);
    Route::post('user/login', ['as' => 'admin.user.login.post', 'uses' => 'CmsCanvas\Controllers\Admin\UserController@postLogin']);

    Route::get('user/logout', ['as' => 'admin.user.logout', 'uses' => 'CmscCnvas\Controllers\Admin\UserController@getLogout']);

});