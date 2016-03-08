<?php 

namespace CmsCanvas\Http\Controllers\Admin\System;

use View, Theme, Admin, Validator, stdClass, Config;
use CmsCanvas\Http\Controllers\Admin\AdminController;
use CmsCanvas\Models\Language;
use CmsCanvas\Models\Permission;
use CmsCanvas\Exceptions\Exception;
use Illuminate\Http\Request;

class LanguageController extends AdminController {

    /**
     * Display all languages
     *
     * @return View
     */
    public function getLanguages(Request $request)
    {
        $content = View::make('cmscanvas::admin.system.language.languages');

        $filter = Language::getSessionFilter();
        $orderBy = Language::getSessionOrderBy();

        $languages = new Language;
        $languages = $languages->applyFilter($filter)
            ->applyOrderBy($orderBy);

        $content->languages = $languages->paginate(50);
        $content->filter = new stdClass();
        $content->filter->filter = $filter;
        $content->orderBy = $orderBy;

        $this->layout->breadcrumbs = [$request->path() => 'Languages'];
        $this->layout->content = $content;

    }

    /**
     * Saves the filter request to the session
     *
     * @return View
     */
    public function postLanguages()
    {
        Language::processFilterRequest();

        return redirect()->route('admin.system.language.languages');
    }

    /**
     * Deletes languages(s) that are posted in the selected array
     *
     * @return View
     */
    public function postDelete(Request $request)
    {
        $selected = $request->input('selected');

        if (empty($selected) || ! is_array($selected)) {
            return redirect()->route('admin.system.language.languages')
                ->with('notice', 'You must select at least one language to delete.');
        }

        $selected = array_values($selected);

        try {
            Language::destroy($selected);
        } catch(Exception $e) {
            return redirect()->route('admin.system.language.languages')
                ->with('error', $e->getMessage());;
        }

        return redirect()->route('admin.system.language.languages')
            ->with('message', 'The selected languages(s) were sucessfully deleted.');;
    }

    /**
     * Display add content type form
     *
     * @return View
     */
    public function getAdd()
    {
        // Routed to getEdit
    }

    /**
     * Create a new content type
     *
     * @return View
     */
    public function postAdd()
    {
        // Routed to postEdit
    }

    /**
     * Display edit languages form
     *
     * @return View
     */
    public function getEdit(Request $request, $language = null)
    {
        $content = View::make('cmscanvas::admin.system.language.edit');

        $content->language = $language;

        $this->layout->breadcrumbs = [
            'system/language' => 'Languages', 
            $request->path() => (($language == null) ? 'Add' : 'Edit').' Language'
        ];

        $this->layout->content = $content;
    }

    /**
     * Update or create language
     *
     * @return View
     */
    public function postEdit(Request $request, $language = null)
    {
        $rules = [
            'language' => 'required|max:65',
            'locale' => "required|max:5"
                ."|unique:languages,locale".(($language == null) ? "" : ",{$language->id}"),
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($language == null) {
                return redirect()->route('admin.system.language.add')
                    ->withInput()
                    ->with('error', $validator->messages()->all());
            } else {
                return redirect()->route('admin.system.language.edit', $language->id)
                    ->withInput()
                    ->with('error', $validator->messages()->all());
            }
        }

        $language = ($language == null) ? new Language() : $language;
        $language->fill($request->all());

        try {
            $language->save();
        } catch(Exception $e) {
            return redirect()->route('admin.system.language.edit', $language->id)
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()->route('admin.system.language.languages', $language->id)
            ->with('message', "{$language->language} was successfully updated.");
    }

    /**
     * Set the provided language as the default language
     *
     * @return View
     */
    public function setDefault(Request $request, $language)
    {
        $affectedRows = Language::where('default', 1)->update(['default' => 0]);

        $language->default = 1;

        try {
            $language->save();
        } catch(Exception $e) {
            return redirect()->route('admin.system.language.languages', $language->id)
                ->with('error', "{$language->language} can not be set to the default language while \"Inactive\".");
        }

        return redirect()->route('admin.system.language.languages', $language->id)
            ->with('message', "{$language->language} was successfully set as the default language.");
    }

}