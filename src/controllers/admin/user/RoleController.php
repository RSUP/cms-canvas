<?php namespace CmsCanvas\Controllers\Admin\User;

use View, Theme, Admin, Session, Redirect, Validator, Request, Input, stdClass;
use CmsCanvas\Routing\AdminController;
use CmsCanvas\Models\Role;
use CmsCanvas\Container\Database\OrderBy;

class RoleController extends AdminController {

    /**
     * Display all roles
     *
     * @return View
     */
    public function getRoles()
    {
        $content = View::make('cmscanvas::admin.user.role.roles');

        $filter = Role::getSessionFilter();
        $orderBy = Role::getSessionOrderBy();

        $roles = new Role;
        $roles = $roles->applyFilter($filter)
            ->applyOrderBy($orderBy);

        $content->roles = $roles->paginate(50);
        $content->filter = new stdClass();
        $content->filter->filter = $filter;
        $content->orderBy = $orderBy;

        $this->layout->breadcrumbs = array('user' => 'Users', Request::path() => 'Role');
        $this->layout->content = $content;
    }

    /**
     * Saves the filter request to the session
     *
     * @return View
     */
    public function postRoles()
    {
        Role::processFilterRequest();

        return Redirect::route('admin.user.role.roles');
    }

    /**
     * Deletes user(s) that are posted in the selected array
     *
     * @return View
     */
    public function postDelete()
    {
        $selected = Input::get('selected');

        if (empty($selected) || ! is_array($selected)) {
            return Redirect::route('admin.user.role.roles')
                ->with('notice', 'You must select at least one role to delete.');
        }

        $selected = array_values($selected);

        $roles = Role::whereIn('id', $selected)
            ->get();

        foreach ($roles as $role) 
        {
            if ($role->users()->count() > 0)
            {
                return Redirect::route('admin.user.role.roles')
                    ->with('error', 'Failed to delete role(s) because one or more of the selected has users still assigned.');
            }
        }

        foreach ($roles as $role)
        {
            $role->delete();
        }

        return Redirect::route('admin.user.role.roles')
            ->with('message', 'The selected role(s) were sucessfully deleted.');;
    }

    /**
     * Display add role form
     *
     * @return View
     */
    public function getAdd()
    {
        // Routed to getEdit
    }

    /**
     * Create a new role
     *
     * @return View
     */
    public function postAdd()
    {
        // Routed to postEdit
    }

    /**
     * Display add role form
     *
     * @return View
     */
    public function getEdit($role = null)
    {
        $content = View::make('cmscanvas::admin.user.role.edit');
        $content->role = $role;

        $this->layout->content = $content;
    }

    /**
     * Update an existing user
     *
     * @return View
     */
    public function postEdit($role = null)
    {
        $rules = array(
            'name' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails())
        {
            if ($role == null)
            {
                return Redirect::route('admin.user.role.add')
                    ->withInput()
                    ->with('error', $validator->messages()->all());
            }
            else
            {
                return Redirect::route('admin.user.role.edit', $role->id)
                    ->withInput()
                    ->with('error', $validator->messages()->all());
            }
        }

        $role = ($role == null) ? new Role : $role;
        $role->fill(Input::all());
        $role->save();

        return Redirect::route('admin.user.role.roles')
            ->with('message', "{$role->name} was successfully updated.");
    }

}