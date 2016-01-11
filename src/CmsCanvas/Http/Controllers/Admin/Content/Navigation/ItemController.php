<?php 

namespace CmsCanvas\Http\Controllers\Admin\Content\Navigation;

use View, Admin, Redirect, Validator, Input, Config;
use CmsCanvas\Http\Controllers\Admin\AdminController;
use CmsCanvas\Models\Content\Navigation;
use CmsCanvas\Models\Content\Navigation\Item;
use CmsCanvas\Models\Content\Entry;

class ItemController extends AdminController {

    /**
     * Display add navigation item form
     *
     * @return \Illuminate\View\View
     */
    public function getEdit($navigation, $item = null)
    {
        $content = View::make('cmscanvas::admin.content.navigation.item.edit');

        $entries = Entry::with('contentType')
            ->whereNotNull('route')
            ->orWhereHas('contentType', function($query) {
                $query->whereNotNull('entry_uri_template');
            })
            ->orWhere('id', Config::get('cmscanvas::config.site_homepage'))
            ->get();

        $content->navigation = $navigation;
        $content->item = $item;
        $content->entries = $entries;

        $this->layout->content = $content;
    }

    /**
     * Update or add a navigation item
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit($navigation, $item = null)
    {
        $rules['type'] = 'required';
        if (Input::get('type') == 'url') {
            $rules['title'] = 'required';
        } else {
            $rules['entry_id'] = 'required';
        }

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            if ($item == null) {
                return Redirect::route('admin.content.navigation.item.add', [$navigation->id])
                    ->withInput()
                    ->with('error', $validator->messages()->all());
            } else {
                return Redirect::route('admin.content.navigation.item.edit', [$navigation->id, $item->id])
                    ->withInput()
                    ->with('error', $validator->messages()->all());
            }
        }

        $navigationItem = ($item == null) ? new Item : $item;
        $navigationItem->fill(Input::all());
        if ($navigationItem->type == 'url') {
            $navigationItem->entry_id = null;
        }
        $navigationItem->navigation_id = $navigation->id;

        if ($item == null) {
            $navigationItem->sort = Item::max('id');
        }

        $navigationItem->save();

        return Redirect::route('admin.content.navigation.tree', $navigation->id)
            ->with('message', "Item was successfully updated.");
    }

    /**
     * Deletes navigation item
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getDelete($navigation, $item)
    {
        $item->delete();

        return Redirect::route('admin.content.navigation.tree', $navigation->id)
            ->with('message', 'Navigation item deleted successfully.');;
    }

}